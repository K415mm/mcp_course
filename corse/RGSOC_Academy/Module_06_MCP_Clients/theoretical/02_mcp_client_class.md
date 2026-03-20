---
status: published
---

# 02 — The MCPClient Class: Connecting and Discovering Tools

> **Theoretical Block 2 of 3 | Module 06: Building MCP Clients**

---

## 2.1 The MCPClient Design Pattern

The `MCPClient` class manages connections to multiple MCP servers simultaneously. Its design follows three principles:

1. **One client, many servers** — a single `MCPClient` instance can connect to a CTI server, a SIEM server, and a malware analysis server at the same time
2. **Caching** — tool lists and sessions are cached after first connection (don't re-query on every call)
3. **Safe cleanup** — `AsyncExitStack` ensures all connections are properly closed even if an error occurs

---

## 2.2 The Class Structure

```python
# mcp_client.py
import asyncio
from contextlib import AsyncExitStack
from urllib.parse import urlparse
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from mcp.client.sse import sse_client


class MCPClient:
    """Standalone MCP client for connecting to and managing multiple MCP servers."""

    def __init__(self):
        # Active sessions, keyed by server_id you assign
        self.sessions: dict[str, ClientSession] = {}

        # Cached tool lists per server
        self._server_tools: dict[str, list[dict]] = {}

        # AsyncExitStack: safely closes ALL connections when done
        self.exit_stack = AsyncExitStack()
```

The `server_id` is a label you choose (e.g., `"cti"`, `"siem"`, `"malware"`) — not the server's name. This lets you address multiple servers without confusion.

---

## 2.3 Connecting to a Server

The `connect_to_server` method does three things:
1. Detects whether to use STDIO (local file) or SSE (URL)
2. Opens the transport and creates a `ClientSession`
3. Calls `list_tools()` to discover what the server offers

```python
async def connect_to_server(self, server_id: str, server_path_or_url: str) -> list[dict]:
    """Connect to an MCP server and return its tool list.

    Args:
        server_id: A unique label you assign (e.g., 'cti', 'siem')
        server_path_or_url: File path for local servers, URL for remote

    Returns: list of tool dicts (name, description, parameters)
    """
    # Return cached tools if already connected
    if server_id in self.sessions:
        return self._server_tools.get(server_id, [])

    parsed = urlparse(server_path_or_url)
    is_url = parsed.scheme in ("http", "https")

    if is_url:
        # Remote server — SSE/HTTP transport
        transport = await self.exit_stack.enter_async_context(
            sse_client(server_path_or_url)
        )
    else:
        # Local server — STDIO transport
        # Detect the command based on file extension
        if server_path_or_url.endswith(".py"):
            command = "python"
        elif server_path_or_url.endswith(".sh"):
            command = "bash"
        elif server_path_or_url.endswith(".js"):
            command = "node"
        else:
            raise ValueError(f"Unsupported server type: {server_path_or_url}")

        server_params = StdioServerParameters(
            command=command,
            args=[server_path_or_url],
            env=None   # Inherits the current process environment (includes our .env vars)
        )
        transport = await self.exit_stack.enter_async_context(
            stdio_client(server_params)
        )

    read, write = transport
    session = await self.exit_stack.enter_async_context(
        ClientSession(read, write)
    )

    # Initialize the session (protocol handshake) with a timeout
    await asyncio.wait_for(session.initialize(), timeout=10.0)

    # Discover available tools
    response = await asyncio.wait_for(session.list_tools(), timeout=10.0)

    tools = [
        {
            "name":        tool.name,
            "description": tool.description,
            "parameters":  tool.inputSchema
        }
        for tool in response.tools
    ]

    # Cache for future calls
    self.sessions[server_id]      = session
    self._server_tools[server_id] = tools

    return tools
```

### What `AsyncExitStack` Does

`AsyncExitStack` is like a stack of cleanup actions. When you call `exit_stack.aclose()` at the end:
- It runs every cleanup action in reverse order
- This terminates the server subprocess, closes the transport, and closes the session
- Even if an exception occurs mid-workflow, cleanup still runs

Without this, you would have orphaned Python processes running after your client exits.

---

## 2.4 Understanding the Tool Discovery Response

`session.list_tools()` returns a response with a `.tools` list. Each tool object has:
- `tool.name` — the function name (e.g., `"enrich_ip"`)
- `tool.description` — the docstring (this is what the LLM reads!)
- `tool.inputSchema` — a JSON Schema dict describing the parameters

This is exactly the data you pass to an LLM to tell it what tools exist.

```python
# Example discovery output for the CTI server:
tools = [
    {
        "name": "enrich_ip",
        "description": "Retrieve AbuseIPDB threat intelligence for an IPv4 address. Use when an IP appears in an alert...",
        "parameters": {
            "type": "object",
            "properties": {
                "ip_address": {"type": "string"},
                "days_back":  {"type": "integer", "default": 90}
            },
            "required": ["ip_address"]
        }
    }
]
```

Notice: **the docstring you wrote in Module 04 is exactly what the LLM sees here**. Good docstrings are not optional — they are what makes the agent use (or ignore) your tool.

---

## 2.5 Connecting to Multiple Servers

```python
async def main():
    client = MCPClient()

    # Connect to all three servers
    cti_tools    = await client.connect_to_server("cti",    "d:/mcp_course/cti-server/server.py")
    siem_tools   = await client.connect_to_server("siem",   "d:/mcp_course/siem-server/server.py")
    malware_tools = await client.connect_to_server("malware", "d:/mcp_course/malware-server/server.py")

    # Combine all tools into one list for the LLM
    all_tools = cti_tools + siem_tools + malware_tools

    print(f"Total tools available: {len(all_tools)}")
    for t in all_tools:
        print(f"  {t['name']}: {t['description'][:60]}...")
```

The LLM receives `all_tools` as context and can call any tool across any connected server — transparently.

---

## Key Takeaways

1. `MCPClient` holds `sessions` and `_server_tools` dicts — one entry per connected server.
2. `connect_to_server` auto-detects STDIO vs SSE based on the path/URL.
3. `AsyncExitStack` ensures all server processes and connections are cleaned up after use.
4. `session.list_tools()` returns the tool list the LLM will use to decide which tools to call.
5. The docstring you write in Module 04 is literally what the LLM reads to understand your tool.
