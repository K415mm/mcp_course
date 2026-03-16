---
status: published
---

# 01 — What is an MCP Client? Architecture and Responsibilities

> **Theoretical Block 1 of 3 | Module 06: Building MCP Clients**

---

## 1.1 What an MCP Client Does

An **MCP client** is the component that:
1. Connects to one or more MCP servers
2. Discovers what tools those servers offer
3. Passes tool definitions to an LLM
4. Executes the tool calls the LLM requests
5. Returns results to the LLM

In the agentic Sense-Think-Act loop, the client is the **bridge** between Think (the LLM) and Act (the MCP tools):

```
LLM (Think)
    ↕  tool definitions + results
MCP Client
    ↕  JSON-RPC 2.0 over STDIO or SSE
MCP Server (tool execution)
```

Without a client, an LLM has no way to call MCP tools. Claude Desktop, VS Code GitHub Copilot, and Cursor are all MCP clients. In this module, you build your own.

---

## 1.2 Why Build a Custom Client?

You need a custom client when:
- You want to embed MCP tool-calling inside your own Python application or agent
- You need to connect to multiple servers simultaneously and route calls between them
- You want to add custom logic: approval gates, audit logging, rate limiting
- You are building an automated pipeline (no human-in-the-loop UI)

Custom clients are the foundation of autonomous SOC workflows where your agent code, not a GUI, decides which tool to call.

---

## 1.3 The Two Transports: STDIO vs SSE

MCP supports two transport mechanisms. Your client must choose the right one based on where the server runs:

| | STDIO | SSE (Server-Sent Events) |
|---|---|---|
| **Server location** | Same machine | Any machine (network) |
| **Protocol** | stdin/stdout pipe | HTTP/HTTPS |
| **Setup** | Launch server process | Connect to running HTTP endpoint |
| **Best for** | Local development, lab servers | Production, shared servers, cloud |
| **Authentication** | Process-level (env vars) | HTTP headers (API keys, JWT) |
| **Latency** | Lowest | Network-dependent |

For this course: **STDIO for all lab servers** (they run on your machine). SSE you will encounter in production deployments in Module 8.

---

## 1.4 The Client Lifecycle

Every MCP client interaction follows this sequence:

```
1. CONNECT     → Launch/reach the server, negotiate protocol version
2. DISCOVER    → Call list_tools() → get tool name, description, schema
3. PASS TO LLM → Include tool definitions in the LLM system prompt
4. LLM DECIDES → LLM chooses a tool + generates arguments
5. INVOKE      → Call session.call_tool(name, args)
6. RETURN      → Pass result back to LLM for next reasoning step
7. CLEANUP     → Close session, terminate server process
```

Steps 4–6 repeat in a loop until the LLM signals it is done.

---

## 1.5 Why asyncio?

MCP clients in Python are **asynchronous** — they use Python's `asyncio` library. This is because:
- A client may be connected to multiple servers at once
- Waiting for a tool to return (network I/O) should not block the entire program
- The MCP Python SDK's transport layer is built on `asyncio`

The consequence: all MCP client code uses `async def` functions and must be run inside an event loop.

```python
import asyncio

async def main():
    # All MCP client work happens inside an async function
    client = MCPClient()
    tools = await client.connect_to_server("cti", "server.py")
    result = await client.invoke_tool("cti", "enrich_ip", {"ip_address": "185.220.101.45"})
    print(result)

asyncio.run(main())   # Start the event loop
```

You do not need to fully understand asyncio internals — the pattern is always the same: `async def`, `await`, `asyncio.run()`.

---

## 1.6 Client Setup with uv

```powershell
# Create a client project (can be in same or different directory from server)
uv init mcp-client
cd mcp-client

uv python pin 3.12
uv add "mcp[cli]" python-dotenv

New-Item client.py
```

---

## Key Takeaways

1. An MCP client connects to servers, discovers tools, and bridges the LLM to tool execution.
2. STDIO = local server (same machine). SSE = remote server (HTTP/HTTPS).
3. The client lifecycle: Connect → Discover → Pass to LLM → Invoke → Return → Cleanup.
4. MCP clients use `asyncio` because they handle concurrent I/O — all code is `async def` + `await`.
5. Custom clients are how you build autonomous agents — not just interactive chatbots.
