---
status: published
---

# 03 — Invoking Tools, Cleanup, and Building a Full Agent Loop

> **Theoretical Block 3 of 3 | Module 06: Building MCP Clients**

---

## 3.1 Invoking a Tool

Once connected and you have the tool list, invoking a tool is a single `await` call:

```python
async def invoke_tool(self, server_id: str, tool_name: str, tool_args: dict) -> str:
    """Invoke a tool on a connected MCP server.

    Args:
        server_id: The server label used in connect_to_server()
        tool_name: The exact function name (e.g., 'enrich_ip')
        tool_args: Dict matching the tool's parameters (e.g., {'ip_address': '1.2.3.4'})

    Returns: The tool result as a string.
    Raises: RuntimeError if server not connected or tool times out.
    """

    session = self.sessions.get(server_id)
    if not session:
        raise RuntimeError(
            f"No active session for '{server_id}'. Call connect_to_server() first."
        )

    try:
        # 30-second timeout — prevents the agent from hanging on a slow tool
        result = await asyncio.wait_for(
            session.call_tool(tool_name, tool_args),
            timeout=30.0
        )

        # Extract the text content from the result object
        if result.content and len(result.content) > 0:
            return result.content[0].text
        return ""

    except asyncio.TimeoutError:
        raise RuntimeError(
            f"Tool '{server_id}:{tool_name}' timed out after 30 seconds"
        )
    except Exception as e:
        raise RuntimeError(f"Tool invocation failed: {str(e)}")
```

### Why 30 seconds?

- Too short: fast tools work, slow tools (sandbox analysis) fail unnecessarily
- Too long: a hung server blocks the agent indefinitely
- 30 seconds is a good default for cyber defense tools — most complete in < 5 seconds, some (malware analysis, large log queries) need up to 30

---

## 3.2 Cleanup — Always Close Your Connections

```python
async def cleanup(self):
    """Close all server connections and clean up resources."""
    await self.exit_stack.aclose()
    self.sessions.clear()
    self._server_tools.clear()


async def disconnect_server(self, server_id: str):
    """Disconnect a specific server without closing others."""
    if server_id in self.sessions:
        del self.sessions[server_id]
        del self._server_tools[server_id]
        # Note: full transport cleanup requires the exit_stack context
```

The standard pattern — always use `try/finally`:

```python
async def main():
    client = MCPClient()
    try:
        tools = await client.connect_to_server("cti", "server.py")
        result = await client.invoke_tool("cti", "enrich_ip", {"ip_address": "185.220.101.45"})
        print(result)
    finally:
        await client.cleanup()  # Always runs — even if an error occurs above

asyncio.run(main())
```

---

## 3.3 The Complete MCPClient Class

```python
# mcp_client.py — Complete implementation
import asyncio
from contextlib import AsyncExitStack
from urllib.parse import urlparse
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from mcp.client.sse import sse_client


class MCPClient:
    """Standalone MCP client for connecting to multiple MCP servers."""

    def __init__(self):
        self.sessions: dict[str, ClientSession] = {}
        self._server_tools: dict[str, list[dict]] = {}
        self.exit_stack = AsyncExitStack()

    async def connect_to_server(self, server_id: str, server_path_or_url: str) -> list[dict]:
        if server_id in self.sessions:
            return self._server_tools.get(server_id, [])

        parsed = urlparse(server_path_or_url)
        is_url = parsed.scheme in ("http", "https")

        if is_url:
            transport = await self.exit_stack.enter_async_context(
                sse_client(server_path_or_url)
            )
        else:
            if server_path_or_url.endswith(".py"):
                command = "python"
            elif server_path_or_url.endswith(".sh"):
                command = "bash"
            elif server_path_or_url.endswith(".js"):
                command = "node"
            else:
                raise ValueError(f"Unsupported: {server_path_or_url}")

            server_params = StdioServerParameters(
                command=command, args=[server_path_or_url], env=None
            )
            transport = await self.exit_stack.enter_async_context(
                stdio_client(server_params)
            )

        read, write = transport
        session = await self.exit_stack.enter_async_context(ClientSession(read, write))

        await asyncio.wait_for(session.initialize(), timeout=10.0)
        response = await asyncio.wait_for(session.list_tools(), timeout=10.0)

        tools = [
            {"name": t.name, "description": t.description, "parameters": t.inputSchema}
            for t in response.tools
        ]

        self.sessions[server_id] = session
        self._server_tools[server_id] = tools
        return tools

    async def invoke_tool(self, server_id: str, tool_name: str, tool_args: dict) -> str:
        session = self.sessions.get(server_id)
        if not session:
            raise RuntimeError(f"No session for '{server_id}'")

        result = await asyncio.wait_for(
            session.call_tool(tool_name, tool_args),
            timeout=30.0
        )
        return result.content[0].text if result.content else ""

    def list_all_tools(self) -> list[dict]:
        """Return all tools from all connected servers."""
        all_tools = []
        for server_id, tools in self._server_tools.items():
            for tool in tools:
                all_tools.append({**tool, "server_id": server_id})
        return all_tools

    async def cleanup(self):
        await self.exit_stack.aclose()
        self.sessions.clear()
        self._server_tools.clear()
```

---

## 3.4 A Simple Agent Loop

This is the core of an autonomous SOC agent — the pattern that drives every workshop server:

```python
# agent_loop.py
import asyncio
import os
import json
import requests
from mcp_client import MCPClient

ANTHROPIC_KEY = os.environ.get("ANTHROPIC_API_KEY", "")


async def run_agent(alert_text: str):
    """Run a single-round agent loop to triage an alert."""
    client = MCPClient()

    try:
        # Step 1: Connect to servers
        cti_tools = await client.connect_to_server(
            "cti", "d:/mcp_course/cti-mcp-server/server.py"
        )
        print(f"Connected: {len(cti_tools)} CTI tools available")

        # Step 2: Ask the LLM what to do
        tool_descriptions = [
            f"{t['name']}: {t['description']}" for t in cti_tools
        ]
        system_prompt = f"""You are a SOC triage assistant. 
Available tools: {json.dumps(tool_descriptions, indent=2)}

For each tool call, respond with EXACTLY:
CALL: tool_name {{"param": "value"}}

After all enrichments, respond with:
VERDICT: [your assessment]"""

        # (In production, this would call a real LLM API)
        print("--- Agent reasoning step ---")
        print(f"Alert: {alert_text}")
        print(f"Tools: {[t['name'] for t in cti_tools]}")

        # Step 3: Execute a tool call (simulated here, real in Workshop labs)
        result = await client.invoke_tool(
            "cti",
            "enrich_ip",
            {"ip_address": "185.220.101.45"}
        )
        print(f"Tool result: {result}")

    finally:
        await client.cleanup()


asyncio.run(run_agent("Outbound connection to 185.220.101.45:9001, 48KB transferred"))
```

---

## 3.5 Audit Logging — Every Tool Call Must Be Recorded

In production SOC deployments, every tool call must be logged:

```python
import json
import datetime

async def invoke_tool_with_audit(
    self,
    server_id: str,
    tool_name: str,
    tool_args: dict,
    analyst_id: str = "agent"
) -> str:
    """Invoke a tool and write an audit log entry."""
    start = datetime.datetime.utcnow().isoformat()

    try:
        result = await self.invoke_tool(server_id, tool_name, tool_args)
        status = "ok"
    except Exception as e:
        result = str(e)
        status = "error"

    audit_entry = {
        "timestamp": start,
        "analyst":   analyst_id,
        "server":    server_id,
        "tool":      tool_name,
        "args":      tool_args,
        "status":    status,
        "result_preview": result[:200]  # First 200 chars only
    }

    with open("audit_log.jsonl", "a") as f:
        f.write(json.dumps(audit_entry) + "\n")

    if status == "error":
        raise RuntimeError(result)
    return result
```

Every tool call produces one line in `audit_log.jsonl` — a standard format for SIEM ingestion.

---

## Key Takeaways

1. `session.call_tool(name, args)` returns a result object — extract text with `result.content[0].text`.
2. Always wrap `call_tool` in `asyncio.wait_for(..., timeout=30.0)` — never let a tool hang forever.
3. Use `try/finally` with `client.cleanup()` — connections must be closed even if an error occurs.
4. The agent loop: Connect → Discover → Ask LLM → Call tool → Return result → Repeat → Cleanup.
5. Every tool call in a production system must produce an audit log entry — immutable and SIEM-ready.
