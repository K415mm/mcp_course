---
status: published
---

# Example 01 — Complete MCPClient: Connecting, Discovering, Invoking

> **Example Block 1 of 2 | Module 06: Building MCP Clients**

---

## What This Example Shows

A fully annotated, runnable `MCPClient` implementation. Every line has a comment explaining WHY it is written that way. Read it, run it against your Module 05 CTI server, then modify one thing at a time.

---

## The Complete Annotated File

Save as `d:/mcp_course/mcp-client/mcp_client.py`:

```python
# mcp_client.py — Complete annotated MCP client implementation
# How to use: import MCPClient and call it from an async function

import asyncio
from contextlib import AsyncExitStack   # Context manager stack for async resources
from urllib.parse import urlparse       # Detect if path_or_url is a URL or file
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from mcp.client.sse import sse_client


class MCPClient:
    """Standalone MCP client for connecting to multiple MCP servers.

    Usage:
        client = MCPClient()
        tools = await client.connect_to_server("cti", "path/to/server.py")
        result = await client.invoke_tool("cti", "enrich_ip", {"ip_address": "1.2.3.4"})
        await client.cleanup()
    """

    def __init__(self):
        # sessions: maps your label → the active ClientSession
        # e.g., {"cti": <ClientSession>, "siem": <ClientSession>}
        self.sessions: dict[str, ClientSession] = {}

        # _server_tools: cached tool list per server
        # populated by connect_to_server, read by list_all_tools
        self._server_tools: dict[str, list[dict]] = {}

        # exit_stack: manages ALL async context managers we open
        # When exit_stack.aclose() is called, every transport and session
        # is closed in reverse order — even if an error occurred
        self.exit_stack = AsyncExitStack()

    # ─────────────────────────────────────────────────────────────
    async def connect_to_server(
        self, server_id: str, server_path_or_url: str
    ) -> list[dict]:
        """Connect to an MCP server and return its available tools.

        Args:
            server_id: A unique label you choose, e.g. 'cti', 'malware'
            server_path_or_url: File path (.py/.sh) for local, URL for remote

        Returns: list of tool dicts with keys: name, description, parameters
        """

        # Don't reconnect if we're already connected to this server
        # This makes repeated calls to connect_to_server safe
        if server_id in self.sessions:
            return self._server_tools.get(server_id, [])

        # Detect whether this is a URL (SSE transport) or a file (STDIO)
        parsed = urlparse(server_path_or_url)
        is_url = parsed.scheme in ("http", "https")

        if is_url:
            # Remote server: SSE (HTTP) transport
            # sse_client() establishes an HTTP connection and keeps it open
            transport = await self.exit_stack.enter_async_context(
                sse_client(server_path_or_url)
            )

        else:
            # Local server: STDIO transport
            # We start a subprocess and communicate via its stdin/stdout

            # Determine the right command to run the server
            if server_path_or_url.endswith(".py"):
                command = "python"      # Python server
            elif server_path_or_url.endswith(".sh"):
                command = "bash"        # Bash server (like the GitHub example)
            elif server_path_or_url.endswith(".js"):
                command = "node"        # TypeScript/JavaScript server
            else:
                raise ValueError(
                    f"Unsupported server type: {server_path_or_url}. "
                    "Expected .py, .sh, or .js"
                )

            # StdioServerParameters: tells the SDK which process to start
            # env=None means inherit the current environment (includes our .env vars)
            server_params = StdioServerParameters(
                command=command,
                args=[server_path_or_url],
                env=None
            )

            # stdio_client() starts the subprocess and wraps its stdin/stdout
            transport = await self.exit_stack.enter_async_context(
                stdio_client(server_params)
            )

        # transport gives us (read, write) — two async streams
        read, write = transport

        # ClientSession wraps the transport to handle the MCP protocol
        session = await self.exit_stack.enter_async_context(
            ClientSession(read, write)
        )

        # Initialize: perform the MCP protocol handshake
        # (client sends "initialize", server responds with capabilities)
        # 10-second timeout: if server doesn't respond, raise TimeoutError
        await asyncio.wait_for(session.initialize(), timeout=10.0)

        # Discover: ask the server what tools it offers
        # This returns tool objects with .name, .description, .inputSchema
        response = await asyncio.wait_for(session.list_tools(), timeout=10.0)

        # Convert tool objects to plain dicts for easier handling
        tools = [
            {
                "name":        tool.name,
                "description": tool.description,  # This is the docstring!
                "parameters":  tool.inputSchema    # JSON Schema for inputs
            }
            for tool in response.tools
        ]

        # Cache everything for future invoke calls
        self.sessions[server_id]      = session
        self._server_tools[server_id] = tools

        return tools

    # ─────────────────────────────────────────────────────────────
    async def invoke_tool(
        self, server_id: str, tool_name: str, tool_args: dict
    ) -> str:
        """Invoke a tool on a connected server and return the result as text.

        Args:
            server_id: The label used in connect_to_server()
            tool_name: Exact function name (e.g., 'enrich_ip')
            tool_args: Dict matching the tool's parameters

        Returns: Tool result as a string (JSON-format dict from the server)
        Raises: RuntimeError if server not connected or tool times out
        """

        # Get the pre-established session
        session = self.sessions.get(server_id)
        if not session:
            raise RuntimeError(
                f"No active session for server '{server_id}'. "
                "Call connect_to_server() first."
            )

        # call_tool sends the MCP "tools/call" message over the transport
        # 30 seconds: long enough for malware analysis, short enough to detect hangs
        result = await asyncio.wait_for(
            session.call_tool(tool_name, tool_args),
            timeout=30.0
        )

        # result.content is a list of content items; we take the first text item
        if result.content and len(result.content) > 0:
            return result.content[0].text
        return ""   # Empty string if no content

    # ─────────────────────────────────────────────────────────────
    def list_all_tools(self) -> list[dict]:
        """Return all tools from all connected servers, tagged with server_id."""
        all_tools = []
        for server_id, tools in self._server_tools.items():
            for tool in tools:
                # Add server_id so the caller knows which server owns this tool
                all_tools.append({**tool, "server_id": server_id})
        return all_tools

    # ─────────────────────────────────────────────────────────────
    async def cleanup(self):
        """Close all server connections and free resources.

        ALWAYS call this when done — use try/finally to guarantee it runs.
        """
        # aclose() runs all cleanup actions registered in the exit_stack
        # This terminates subprocess servers and closes SSE connections
        await self.exit_stack.aclose()

        # Clear the cached state so the object can't be accidentally reused
        self.sessions.clear()
        self._server_tools.clear()
```

---

## The Test Script

```python
# test_client.py — Run against your Module 05 CTI server
import asyncio
from mcp_client import MCPClient


async def main():
    client = MCPClient()

    try:
        # Test 1: Connect and discover
        print("Test 1: Connecting to CTI server...")
        tools = await client.connect_to_server(
            "cti", "d:/mcp_course/cti-mcp-server/server.py"
        )
        print(f"✅ Connected. {len(tools)} tools discovered:")
        for t in tools:
            print(f"   [{t['name']}]: {t['description'][:60]}...")

        # Test 2: Call a real tool
        print("\nTest 2: Invoking enrich_ip...")
        result = await client.invoke_tool(
            "cti", "enrich_ip", {"ip_address": "185.220.101.45"}
        )
        print(f"✅ Result: {result[:200]}")

        # Test 3: Tool with bad input (verify error handling)
        print("\nTest 3: Invoking enrich_ip with bad input...")
        result = await client.invoke_tool(
            "cti", "enrich_ip", {"ip_address": "not-an-ip"}
        )
        print(f"✅ Error handled: {result[:100]}")      # Should be error dict, not crash

        # Test 4: Cached re-connect (should not re-initialize)
        print("\nTest 4: Re-connecting (should use cache)...")
        tools2 = await client.connect_to_server(
            "cti", "d:/mcp_course/cti-mcp-server/server.py"
        )
        assert tools == tools2, "Tool list should be identical"
        print("✅ Cache working correctly")

    finally:
        print("\nCleaning up...")
        await client.cleanup()
        print("✅ Done. All connections closed.")


asyncio.run(main())
```

Run:
```powershell
cd d:/mcp_course/mcp-client
uv run test_client.py
```

---

## Modification Exercises

1. **Add timeout parameter**: Modify `invoke_tool` to accept an optional `timeout: float = 30.0` parameter.
2. **Add server status check**: Add `is_connected(server_id: str) -> bool` method.
3. **Add audit logging**: Log every `invoke_tool` call to `audit.jsonl` (from Module 06 Block 3).
