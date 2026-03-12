---
status: draft
---

# MCP Communication Layers: Data and Transport

## Two-Layer Architecture

When an MCP Client and Server communicate, their interaction is divided into **two distinct layers**, each with a specific responsibility:

1. **The Data Layer** — handles *what* is being said (the content of messages)
2. **The Transport Layer** — handles *how* messages travel (the communication medium)

This separation is critical: it means the same tool calls and responses work identically whether the server is running locally on your machine or remotely in the cloud.

## The Data Layer: JSON-RPC 2.0

The Data Layer uses **JSON-RPC 2.0** as its message format. This is a lightweight remote procedure call protocol encoded in JSON.

### Message Types

| Type | Direction | Purpose |
|------|-----------|---------|
| **Request** | Client → Server | Invoke a method (e.g., call a tool) |
| **Response** | Server → Client | Return results or errors |
| **Notification** | Either direction | One-way message, no response expected |

### Core Primitives

The Data Layer manages these MCP primitives:

- **Tools** — Executable functions discovered via `tools/list` and invoked via `tools/call`
- **Resources** — Contextual data retrieved via `resources/list` and `resources/read`
- **Prompts** — Reusable templates fetched via `prompts/list` and `prompts/get`
- **Notifications** — Lifecycle events like `notifications/tools/list_changed`

### Example: Listing Available Tools

```json
// Client Request
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/list",
  "params": {}
}

// Server Response
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "tools": [
      {
        "name": "analyze_ip",
        "description": "Analyze an IP address for threats",
        "inputSchema": {
          "type": "object",
          "properties": {
            "ip": { "type": "string", "description": "IPv4 address" }
          },
          "required": ["ip"]
        }
      }
    ]
  }
}
```

### Example: Calling a Tool

```json
// Client Request
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "analyze_ip",
    "arguments": { "ip": "185.220.101.1" }
  }
}

// Server Response
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "IP 185.220.101.1 — TOR exit node, high risk, 847 AbuseIPDB reports"
      }
    ]
  }
}
```

## The Transport Layer

The Transport Layer handles the **physical mechanics** of how messages travel between Client and Server:

- Connection establishment
- Message framing and delivery
- Authentication and authorization (for remote)

### Supported Transports

MCP supports two primary transport mechanisms:

#### 1. stdio (Standard Input/Output)

Used for **local servers** running on the same machine as the Host.

```
Client ──stdin──▶ Server Process
Client ◀──stdout── Server Process
```

**How it works:**
- The Host spawns the server as a **child process**
- Messages are sent over `stdin` and received from `stdout`
- Each message is a JSON-RPC object on a single line
- `stderr` is used for logging (not protocol traffic)

**Advantages:**
- Extremely fast (no network overhead)
- Simple to set up and debug
- Great for local development and testing

**Security considerations:**
- The server runs with **your local user permissions**
- Be cautious when downloading third-party MCP servers
- Always review the code of untrusted servers before running them

#### 2. Streamable HTTP

Used for **remote servers** running on different machines or in the cloud.

```
Client ──HTTP POST──▶ Server (endpoint: /mcp)
Client ◀──SSE stream── Server (for streaming results)
```

**How it works:**
- Client sends HTTP POST requests to the server's `/mcp` endpoint
- Server can respond with direct JSON or open an SSE stream for ongoing communication
- Supports standard HTTP authentication (Bearer tokens, OAuth, API keys)

**Advantages:**
- Works across networks, firewalls, and cloud environments
- Supports existing HTTP infrastructure (load balancers, proxies, WAFs)
- Standard authentication and TLS encryption

**Security considerations:**
- Requires proper authentication configuration
- Origin validation for web-based clients
- Rate limiting and access control are essential

### Transport Comparison

| Feature | stdio | Streamable HTTP |
|---------|-------|-----------------|
| Location | Local only | Local or Remote |
| Speed | Very fast | Network-dependent |
| Auth | OS-level | HTTP auth (tokens, OAuth) |
| Setup | Spawn process | HTTP server + endpoint |
| Use case | Development, local tools | Production, shared services |
| Encryption | N/A (same machine) | TLS/HTTPS |

## The Key Insight: Transport Independence

Regardless of whether the server is local (stdio) or remote (HTTP), **the Data Layer messages remain identical**. The AI agent always experiences a smooth, predictable interaction:

```
Same JSON-RPC messages
├── Over stdio  → Local development
└── Over HTTP   → Production deployment
```

This means you can:
1. **Develop locally** with stdio for fast iteration
2. **Deploy remotely** with HTTP for production — no code changes needed
3. **Switch transports** without modifying your tools or business logic

## Key Takeaways

- MCP separates communication into **Data Layer** (JSON-RPC content) and **Transport Layer** (delivery mechanism)
- The Data Layer uses **JSON-RPC 2.0** for structured requests, responses, and notifications
- **stdio** transport is for local servers (fast, simple, development-friendly)
- **Streamable HTTP** transport is for remote servers (production-ready, authenticated)
- Messages are **transport-independent** — same content works over any transport
