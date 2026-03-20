---
status: published
---

# Practical 01 — Build and Use the MCPClient

> **Practical Block 1 of 3 | Module 06: Building MCP Clients**

---

## Objective

Build the `MCPClient` class from scratch, connect it to the CTI server from Module 05, discover its tools, invoke a tool, and print a structured result.

---

## Part A: Setup

```powershell
cd d:/mcp_course
uv init mcp-client
cd mcp-client
uv python pin 3.12
uv add "mcp[cli]" python-dotenv
New-Item client.py
New-Item mcp_client.py
```

---

## Part B: Write the MCPClient Class

Fill in the blanks:

```python
# mcp_client.py
import asyncio
from contextlib import AsyncExitStack
from urllib.parse import urlparse
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from mcp.client.sse import sse_client


class MCPClient:
    """Standalone MCP client for connecting to multiple MCP servers."""

    def __init__(self):
        # Q1: Three instance variables — sessions dict, server_tools dict, exit_stack
        self.sessions: dict[___, ___] = {}
        self._server_tools: dict[___, ___] = {}
        self.exit_stack = ___()   # Q2: What class?

    async def connect_to_server(self, server_id: str, server_path_or_url: str) -> list[dict]:
        """Connect to an MCP server and return its tool list."""
        # Q3: Skip if already connected — return cached tools
        if server_id in ___:
            return self._server_tools.get(server_id, [])

        parsed = urlparse(server_path_or_url)
        is_url = parsed.scheme in ("http", "https")

        if is_url:
            # Remote: SSE transport
            transport = await self.exit_stack.enter_async_context(
                sse_client(server_path_or_url)
            )
        else:
            # Local: determine command from file extension
            if server_path_or_url.endswith(".py"):
                command = ___   # Q4
            elif server_path_or_url.endswith(".sh"):
                command = ___   # Q5
            else:
                raise ValueError(f"Unsupported: {server_path_or_url}")

            server_params = StdioServerParameters(
                command=command,
                args=[server_path_or_url],
                env=None
            )
            transport = await self.exit_stack.enter_async_context(
                stdio_client(server_params)
            )

        read, write = transport
        session = await self.exit_stack.enter_async_context(
            ClientSession(read, write)
        )

        # Q6: Initialize session (with timeout=10.0)
        await asyncio.wait_for(___, timeout=10.0)

        # Q7: Discover tools (with timeout=10.0)
        response = await asyncio.wait_for(___, timeout=10.0)

        tools = [
            {"name": t.name, "description": t.description, "parameters": t.inputSchema}
            for t in response.tools
        ]

        # Q8: Cache session and tools
        self.sessions[server_id]      = ___
        self._server_tools[server_id] = ___

        return tools

    async def invoke_tool(self, server_id: str, tool_name: str, tool_args: dict) -> str:
        """Invoke a tool on a connected server."""
        session = self.sessions.get(server_id)
        if not session:
            raise RuntimeError(f"No session for '{server_id}'. Connect first.")

        result = await asyncio.wait_for(
            session.call_tool(tool_name, tool_args),
            timeout=___   # Q9: What timeout?
        )
        return result.content[0].text if result.content else ""

    def list_all_tools(self) -> list[dict]:
        """Return all tools from all connected servers with server_id."""
        all_tools = []
        for server_id, tools in self._server_tools.items():
            for tool in tools:
                all_tools.append({**tool, "server_id": server_id})
        return all_tools

    async def cleanup(self):
        """Close all connections."""
        await self.exit_stack.aclose()
        self.sessions.clear()
        self._server_tools.clear()
```

---

## Part C: Write client.py

```python
# client.py — Test the MCPClient against the Module 05 CTI server
import asyncio
from mcp_client import MCPClient


async def main():
    client = MCPClient()

    try:
        # Step 1: Connect to the CTI server from Module 05
        print("Connecting to CTI server...")
        tools = await client.connect_to_server(
            "cti",
            "d:/mcp_course/cti-mcp-server/server.py"
        )

        # Step 2: Print discovered tools
        print(f"\nDiscovered {len(tools)} tools:")
        for t in tools:
            print(f"  [{t['name']}] {t['description'][:80]}...")

        # Step 3: Invoke enrich_ip
        print("\nInvoking enrich_ip('185.220.101.45')...")
        result = await client.invoke_tool(
            "cti",
            "enrich_ip",
            {"ip_address": "185.220.101.45"}
        )
        print(f"Result: {result}")

        # Step 4: Invoke enrich_hash
        print("\nInvoking enrich_hash('3395856ce81f2b7382dee72602f798b642f14d8')...")
        result = await client.invoke_tool(
            "cti",
            "enrich_hash",
            {"hash_value": "3395856ce81f2b7382dee72602f798b642f14d8"}
        )
        print(f"Result: {result}")

    finally:
        print("\nCleaning up...")
        await client.cleanup()
        print("Done.")


asyncio.run(main())
```

Run:
```powershell
uv run client.py
```

---

## Expected Output

```
Connecting to CTI server...

Discovered 3 tools:
  [enrich_ip] Retrieve AbuseIPDB threat intelligence for an IPv4 address...
  [enrich_hash] Look up a file hash in MalwareBazaar threat intelligence...
  [enrich_domain] Retrieve VirusTotal intelligence for a domain...

Invoking enrich_ip('185.220.101.45')...
Result: {"ip": "185.220.101.45", "abuse_score": 98, ...}

Invoking enrich_hash('3395856...')...
Result: {"hash": "3395856...", "found": ..., ...}

Cleaning up...
Done.
```

---

## Checklist

- [ ] `mcp_client.py` — all 9 blanks filled correctly
- [ ] `client.py` runs without errors
- [ ] Tool discovery prints all 3 CTI server tools
- [ ] Both tool invocations return valid result dicts
- [ ] "Cleaning up... Done." appears — connections properly closed
