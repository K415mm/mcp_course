---
status: draft
---

# Module 6: Build MCP Clients with Python

## Module Goal

Understand how MCP clients work, build a client that discovers and invokes tools from an MCP server, and assemble a multi-tool triage workflow that produces analyst-ready output.

## Learning Objectives

1. Explain the role of an MCP client versus an MCP server.
2. Write a Python client that connects to a server and calls tools via JSON-RPC.
3. Handle tool responses and chain multiple tool calls in sequence.
4. Build an incident triage client that ingests an alert and produces a structured brief.
5. Handle errors, timeouts, and multi-server orchestration.

---

## Theoretical Section

### 6.1 What a Client Does

In MCP, the **client** sits between the AI model (LLM) and the MCP server(s). Its responsibilities are:

| Responsibility | Description |
|---|---|
| Connection management | Establish and maintain JSON-RPC sessions to one or more servers |
| Tool discovery | Call `tools/list` at session start to learn what tools are available |
| Tool invocation | Call `tools/call` with structured arguments and receive results |
| Error handling | Retry on transient failures, surface errors to the LLM clearly |
| Session teardown | Cleanly close connections when the workflow completes |

In a typical setup:
- **Host** = the AI application (Trae AI, Claude Desktop, your Python script)
- **Client** = the component inside the host that manages server connections
- **Server** = the MCP server you built in Module 5

A single host can embed **multiple clients**, each connected to a different server. The LLM selects which tool (and therefore which server) to call based on tool descriptions.

---

### 6.2 The Tool Discovery Flow

When a client connects to a server, the first message it sends is `tools/list`:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/list"
}
```

The server responds with all registered tools, their input schemas, and their descriptions:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "tools": [
      {
        "name": "enrich_domain",
        "description": "Enrich a domain with WHOIS...",
        "inputSchema": {
          "type": "object",
          "properties": {
            "domain": { "type": "string" }
          },
          "required": ["domain"]
        }
      }
    ]
  }
}
```

The client forwards this tool list to the LLM. The LLM then knows exactly what it can call.

---

### 6.3 Tool Invocation Flow

When the LLM decides to call a tool, it sends a `tools/call` request:

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "enrich_domain",
    "arguments": { "domain": "safe-update-portal.net" }
  }
}
```

The server executes the function and returns the result. The client forwards it back to the LLM as additional context.

---

### 6.4 Connecting to Servers: Local vs Remote

#### Local (stdio)

The client spawns the server as a child process and communicates over stdin/stdout:

```python
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client

server_params = StdioServerParameters(
    command="python",
    args=["cti_server.py"],
    env={"VT_API_KEY": os.environ["VT_API_KEY"]}
)
```

- Best for development and local-only tools.
- No network exposure.
- Server lifetime tied to client session.

#### Remote (Streamable HTTP)

The client connects over HTTP to a running remote server:

```python
from mcp.client.streamable_http import streamablehttp_client

# Server URL + optional auth header
server_url = "https://mcp.myorg.internal/cti"
headers = {"Authorization": f"Bearer {os.environ['MCP_TOKEN']}"}
```

- Best for shared team services (org-wide CTI enrichment, SIEM connector).
- Requires auth and origin validation on the server side.
- Server runs independently; multiple clients can connect simultaneously.

---

## Practical Section

### 6.5 Hands-On: Basic Client Session

```python
import asyncio
import os
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client

async def run_triage(alert_ip: str):
    server_params = StdioServerParameters(
        command="python",
        args=["cti_server.py"],
        env={"VT_API_KEY": os.environ.get("VT_API_KEY", "")}
    )

    async with stdio_client(server_params) as (read, write):
        async with ClientSession(read, write) as session:
            # Step 1: discover tools
            await session.initialize()
            tools = await session.list_tools()
            print(f"Available tools: {[t.name for t in tools.tools]}")

            # Step 2: call enrichment tool
            result = await session.call_tool(
                "check_ip_reputation",
                arguments={"ip_address": alert_ip}
            )
            print(f"Enrichment result: {result.content}")

asyncio.run(run_triage("185.220.101.45"))
```

---

### 6.6 Hands-On: Incident Triage Assistant

A complete client that ingests a raw alert, runs enrichment, and produces a structured brief:

```python
import asyncio, os, json
from mcp import ClientSession, StdioServerParameters
from mcp.client.stdio import stdio_client
from anthropic import Anthropic

# — Config —
CTI_SERVER = StdioServerParameters(
    command="python", args=["cti_server.py"],
    env={"VT_API_KEY": os.environ["VT_API_KEY"]}
)
anthropic = Anthropic(api_key=os.environ["ANTHROPIC_API_KEY"])


async def triage_alert(alert: dict) -> dict:
    """
    Ingest an alert dict, enrich all IOCs via MCP,
    and return a structured triage brief.
    """
    async with stdio_client(CTI_SERVER) as (read, write):
        async with ClientSession(read, write) as session:
            await session.initialize()

            # Discover tools and convert for Anthropic
            mcp_tools = await session.list_tools()
            anthropic_tools = [
                {
                    "name": t.name,
                    "description": t.description,
                    "input_schema": t.inputSchema
                }
                for t in mcp_tools.tools
            ]

            # System context + alert
            messages = [
                {
                    "role": "user",
                    "content": (
                        f"Triage this security alert. Enrich all IOCs you find. "
                        f"Produce a structured brief with: risk_level, summary, "
                        f"enrichment_results, and recommended_action.\n\n"
                        f"Alert: {json.dumps(alert, indent=2)}"
                    )
                }
            ]

            # Agentic loop
            while True:
                response = anthropic.messages.create(
                    model="claude-opus-4-5",
                    max_tokens=2048,
                    tools=anthropic_tools,
                    messages=messages
                )

                # If no tool calls — we have the final answer
                if response.stop_reason == "end_turn":
                    final_text = next(
                        (b.text for b in response.content if hasattr(b, "text")), ""
                    )
                    return {"brief": final_text, "status": "ok"}

                # Process tool calls
                tool_results = []
                for block in response.content:
                    if block.type == "tool_use":
                        result = await session.call_tool(
                            block.name,
                            arguments=block.input
                        )
                        tool_results.append({
                            "type": "tool_result",
                            "tool_use_id": block.id,
                            "content": str(result.content)
                        })

                # Add assistant response + tool results to message history
                messages.append({"role": "assistant", "content": response.content})
                messages.append({"role": "user", "content": tool_results})


# Example usage
if __name__ == "__main__":
    sample_alert = {
        "alert_id": "ALT-2024-0042",
        "source": "SIEM",
        "type": "Suspicious outbound connection",
        "src_ip": "192.168.1.105",
        "dst_ip": "185.220.101.45",
        "domain": "safe-update-portal.net",
        "file_hash": "a3f9d1b2c4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1"
    }
    result = asyncio.run(triage_alert(sample_alert))
    print(result["brief"])
```

---

### 6.7 Multi-Server Client Pattern

For production triage workflows, connect to multiple specialized servers simultaneously:

```python
# Conceptual pattern — initialize multiple sessions
cti_session    = ClientSession(...)   # CTI enrichment server
network_session = ClientSession(...)  # Network analysis server
malware_session = ClientSession(...)  # REMnux / malware analysis server

# Aggregate all tools into one list for the LLM
all_tools = (
    await cti_session.list_tools()
    + await network_session.list_tools()
    + await malware_session.list_tools()
)

# Route each tool call to the correct session based on tool name
```

---

## Example Section

### Triage Walk-Through: End-to-End

**Alert received:**
```json
{
  "type": "Phishing email",
  "sender_domain": "safe-update-portal.net",
  "attachment_hash": "a3f9d1..."
}
```

**Client agent loop:**

| Step | Tool Called | Result |
|---|---|---|
| 1 | `enrich_domain("safe-update-portal.net")` | Age: 3 days, 23/68 malicious votes |
| 2 | `enrich_hash("a3f9d1...")` | 47/68 detections, family: FormBook |
| 3 | LLM synthesizes | Clear domain: high confidence phishing |

**Generated brief:**
> **Risk Level: HIGH**  
> The sender domain `safe-update-portal.net` was registered 3 days ago and has 23 vendor flaggings. The attachment hash matches FormBook (47/68 vendors). Recommended action: quarantine email and block domain. Human approval required to isolate any endpoints that opened the attachment.

---

## Knowledge Check

1. What is the first message a client sends to a server and why?
2. What is the difference between local (stdio) and remote (Streamable HTTP) client connections?
3. In the agentic client loop, what does `stop_reason == "end_turn"` indicate?
4. Why should each tool call be routed to the session that owns that tool?
5. What should a client do if a tool call returns `{"status": "error"}`?

---

## Reading List (Module 6 Source Files)

- [MCP_Universal_AI_Connectivity (1).pdf](file:///d:/mcp_course/MCP_Universal_AI_Connectivity%20(1).pdf)
- [The_MCP_Universal_Plug.pdf](file:///d:/mcp_course/The_MCP_Universal_Plug.pdf)
- [The Universal Framework for Model Context Protocol Integration.md](file:///d:/mcp_course/corse/Module_02_MCP_Fundamentals/The%20Universal%20Framework%20for%20Model%20Context%20Protocol%20Integration.md)
- [9781806662272.pdf](file:///d:/mcp_course/9781806662272.pdf)
