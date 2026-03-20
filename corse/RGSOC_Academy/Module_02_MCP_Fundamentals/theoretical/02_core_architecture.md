---
status: published
---

# MCP Core Architecture: Hosts, Clients, and Servers

## The Client-Server Model

MCP operates on a straightforward **client-server architecture**. To understand how an agentic AI system interacts with MCP, you need to know three main actors and how they relate to each other.

## The Three Core Components

### 1. MCP Hosts

The **Host** is the agentic AI application or framework itself — the environment where the AI agent "lives."

- Examples: Claude Desktop, VS Code with Copilot, a custom Python agent framework
- The Host is responsible for **coordinating** one or more MCP Clients
- It manages the overall user experience and orchestrates the agent's behavior

> **Analogy:** Think of the Host as the **operating system** — it provides the environment where applications (clients) run and manages system-level coordination.

### 2. MCP Clients

The **Client** is a component embedded directly inside the MCP Host. Each Client maintains a **dedicated 1:1 connection** to a single MCP Server.

**Responsibilities:**
- Reach out to servers and discover what tools are available
- Invoke tools when the AI agent requests them
- Manage the communication session lifecycle
- Handle protocol negotiation and capability exchange

> ⚠️ **Important:** Because the MCP protocol is complex and constantly evolving, it is **highly recommended to use existing client libraries** (Python SDK, TypeScript SDK) rather than building an MCP client from scratch.

### 3. MCP Servers

**Servers** are independent programs running as **separate processes** that expose specific capabilities to the AI. An MCP server provides:

| Primitive | Description | Example |
|-----------|-------------|---------|
| **Tools** | Functions the AI can execute (actions) | `scan_ip`, `block_domain`, `query_siem` |
| **Resources** | Contextual data the AI can read | Configuration files, log entries, threat feeds |
| **Prompts** | Pre-built prompt templates | Alert triage workflow, incident report template |

## How They Connect Together

A single AI Host can embed **multiple Clients**, each connecting to a **different Server**. This gives the AI agent a massive, composable toolset:

```
┌─────────────────────────────────────────────────┐
│                  MCP HOST                       │
│            (AI Application)                     │
│                                                 │
│  ┌───────────┐  ┌───────────┐  ┌───────────┐   │
│  │  Client 1 │  │  Client 2 │  │  Client 3 │   │
│  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘   │
│        │              │              │          │
└────────┼──────────────┼──────────────┼──────────┘
         │              │              │
    ┌────▼────┐    ┌────▼────┐    ┌────▼────┐
    │ Server  │    │ Server  │    │ Server  │
    │  SIEM   │    │  CTI    │    │  EDR    │
    │ (local) │    │(remote) │    │(remote) │
    └─────────┘    └─────────┘    └─────────┘
```

### The 1:1 Rule

Each Client maintains a **dedicated connection to exactly one Server**. This design ensures:

- **Isolation** — a failure in one server doesn't cascade to others
- **Security** — each connection can have its own authentication and permissions
- **Simplicity** — the protocol negotiation is always between exactly two parties
- **Scalability** — add more servers without modifying existing connections

## The Connection Lifecycle

When a Client connects to a Server, they follow a defined lifecycle:

1. **Initialize** — Client sends an `initialize` request with its capabilities
2. **Negotiate** — Server responds with its own capabilities and supported features
3. **Discover** — Client requests the list of available tools, resources, and prompts
4. **Operate** — Client calls tools and reads resources as needed by the AI agent
5. **Shutdown** — Either party can terminate the connection gracefully

```json
// Example: Initialize request
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2025-03-26",
    "capabilities": {
      "tools": { "listChanged": true }
    },
    "clientInfo": {
      "name": "SOC-Agent",
      "version": "1.0.0"
    }
  }
}
```

## In a Cyber Defense Context

Consider a SOC automation agent:

| Component | Role | Instance |
|-----------|------|----------|
| **Host** | The SOC automation platform | Custom Python framework |
| **Client 1** | Connects to threat intel | → MISP MCP Server |
| **Client 2** | Connects to SIEM | → Splunk MCP Server |
| **Client 3** | Connects to EDR | → CrowdStrike MCP Server |
| **Client 4** | Connects to ticketing | → ServiceNow MCP Server |

The agent can seamlessly query MISP for IOC data, correlate with Splunk events, check CrowdStrike for endpoint status, and create a ServiceNow ticket — all through the same MCP protocol.

## Key Takeaways

- MCP uses a **Host → Client → Server** architecture
- The **Host** is the AI application; **Clients** are embedded connectors; **Servers** expose tools
- Each Client maintains a **1:1 dedicated connection** to one Server
- Servers expose **Tools** (actions), **Resources** (data), and **Prompts** (templates)
- A single Host can connect to **many Servers simultaneously** for composable capabilities
- Use **official SDKs** rather than building clients from scratch
