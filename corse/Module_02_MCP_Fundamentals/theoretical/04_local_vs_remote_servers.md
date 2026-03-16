---
status: published
---

# Local vs. Remote MCP Servers

## Deployment Models

Because agentic AI systems need to perform a wide variety of tasks securely, MCP clients can connect to servers located in different places using different transport methods. Understanding **when to use local vs. remote servers** is crucial for building secure and efficient cyber defense workflows.

## Local MCP Servers

Local servers run on the **exact same machine** as the AI agent. They communicate via **stdio** (standard input/output streams).

### How They Work

```
┌──────────────────────────────────┐
│         Your Machine             │
│                                  │
│  ┌──────────┐    ┌────────────┐  │
│  │ AI Agent │◀──▶│ MCP Server │  │
│  │  (Host)  │    │  (process) │  │
│  └──────────┘    └────────────┘  │
│     stdin/stdout communication   │
└──────────────────────────────────┘
```

1. The Host **spawns the server** as a subprocess
2. Communication happens over the process's `stdin`/`stdout`
3. Server has access to **local filesystem, tools, and network**

### When to Use Local Servers

| Use Case | Example |
|----------|---------|
| **Development & Testing** | Prototyping a new MCP tool locally |
| **Filesystem Access** | Reading local log files, config, evidence |
| **Fast Iteration** | No network latency, instant feedback |
| **Sensitive Data** | Data that cannot leave the machine |
| **CLI Tool Wrappers** | Wrapping local tools like `nmap`, `yara`, `volatility` |

### Security Considerations for Local Servers

> ⚠️ **Critical Warning:** Local servers run with **your user's permissions**. A malicious MCP server could:
> - Read/write any file your user can access
> - Execute arbitrary commands
> - Exfiltrate data over the network

**Best practices:**
- **Always review the code** of third-party MCP servers before running them
- Run servers in **sandboxed environments** when possible (containers, VMs)
- Use **least-privilege accounts** for running automated agents
- Audit server code for suspicious network calls or file operations

### Example: Local Forensics Server

```python
# local_forensics_server.py
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("forensics-local")

@mcp.tool()
def hash_file(filepath: str) -> str:
    """Compute SHA-256 hash of a local file."""
    import hashlib
    with open(filepath, "rb") as f:
        return hashlib.sha256(f.read()).hexdigest()

@mcp.tool()
def list_recent_logs(directory: str, hours: int = 24) -> list:
    """List log files modified in the last N hours."""
    import os, time
    cutoff = time.time() - (hours * 3600)
    return [f for f in os.listdir(directory)
            if os.path.getmtime(os.path.join(directory, f)) > cutoff]
```

## Remote MCP Servers

Remote servers run on a **different machine**, often in the cloud or on a shared infrastructure. They communicate via **Streamable HTTP**.

### How They Work

```
┌───────────────┐          ┌──────────────────┐
│  Your Machine │          │  Remote Server   │
│               │          │                  │
│  ┌──────────┐ │  HTTPS   │ ┌──────────────┐ │
│  │ AI Agent │─┼──────────┼▶│  MCP Server  │ │
│  │  (Host)  │ │          │ │  (service)   │ │
│  └──────────┘ │          │ └──────────────┘ │
└───────────────┘          └──────────────────┘
```

1. Server runs as an **HTTP service** with a `/mcp` endpoint
2. Client sends **HTTP POST** requests
3. Server responds with JSON or opens **SSE streams**
4. Standard **TLS encryption** and **authentication** apply

### When to Use Remote Servers

| Use Case | Example |
|----------|---------|
| **Shared Services** | Team-wide access to a CTI enrichment server |
| **Cloud APIs** | VirusTotal, Shodan, AbuseIPDB wrappers |
| **Production Deployment** | Stable, monitored, load-balanced services |
| **Multi-User** | Multiple analysts sharing the same MCP tools |
| **Managed Infrastructure** | Centralized logging and audit trails |

### Security Considerations for Remote Servers

Remote servers carry the **same concerns as any web API**:

- **Authentication** — Require API keys, OAuth tokens, or mutual TLS
- **Authorization** — Enforce role-based access control (RBAC)
- **Encryption** — Always use HTTPS/TLS
- **Rate Limiting** — Prevent abuse from automated agents
- **Input Validation** — Sanitize all tool arguments
- **Audit Logging** — Log every tool invocation for compliance
- **Network Segmentation** — Place servers in appropriate DMZ/VLAN

### Example: Remote CTI Server

```python
# remote_cti_server.py
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("cti-remote")

@mcp.tool()
async def check_ip_reputation(ip: str) -> dict:
    """Check IP reputation across multiple threat intel sources."""
    import httpx
    async with httpx.AsyncClient() as client:
        vt = await client.get(f"https://api.virustotal.com/v3/ip_addresses/{ip}",
                              headers={"x-apikey": VT_API_KEY})
        abuse = await client.get(f"https://api.abuseipdb.com/api/v2/check",
                                params={"ipAddress": ip},
                                headers={"Key": ABUSE_API_KEY})
    return {
        "virustotal": vt.json(),
        "abuseipdb": abuse.json()
    }
```

## Hybrid Architectures

In practice, most deployments use a **combination** of local and remote servers:

```
SOC AI Agent
├── Client 1 → Local: yara-scanner (stdio)
├── Client 2 → Local: pcap-analyzer (stdio)
├── Client 3 → Remote: virustotal-mcp (HTTPS)
├── Client 4 → Remote: splunk-mcp (HTTPS)
└── Client 5 → Remote: servicenow-mcp (HTTPS)
```

### Decision Matrix

| Factor | Choose Local | Choose Remote |
|--------|-------------|---------------|
| Data sensitivity | Highly sensitive data | Shareable data |
| User count | Single analyst | Team/organization |
| Latency needs | Real-time / sub-second | Acceptable latency |
| Tool type | CLI wrappers, filesystem | Cloud APIs, shared services |
| Maintenance | Developer-managed | Ops/DevOps-managed |
| Compliance | Air-gapped / isolated | Standard cloud compliance |

## Key Takeaways

- **Local servers** (stdio) are fast, simple, and ideal for development and sensitive operations
- **Remote servers** (HTTP) are scalable, shareable, and production-ready
- Local servers run with **your permissions** — always review untrusted code
- Remote servers need **authentication, encryption, and access control**
- Most real-world deployments use a **hybrid** of local and remote servers
- The **protocol messages are identical** regardless of transport — code once, deploy anywhere
