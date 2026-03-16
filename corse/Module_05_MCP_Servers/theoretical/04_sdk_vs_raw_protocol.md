---
status: published
---

# 04 — SDK vs Raw Protocol: Why You Should Always Use the SDK

> **Theoretical Block 4 of 4 | Module 05: Building MCP Servers**

---

## 4.1 The Raw Protocol — What FastMCP Hides

MCP is built on **JSON-RPC 2.0** over a transport (STDIO or HTTP). Without an SDK, you handle every message manually. Here is what a minimal Bash MCP server looks like to understand how the protocol works:

```bash
#!/bin/bash
# github_server.sh — Minimal Bash MCP server (educational only)

# Helper: output compact JSON (no extra newlines)
emit_json() {
    tr -d '\n' | tr -s ' '
    echo
}

while read -r line; do
    method=$(echo "$line"  | jq -r '.method' 2>/dev/null)
    id=$(echo "$line"      | jq -r '.id'     2>/dev/null)

    case "$method" in
        "initialize")
            cat <<EOF | emit_json
{
  "jsonrpc": "2.0", "id": $id,
  "result": {
    "protocolVersion": "2024-11-05",
    "capabilities": {"tools": {}},
    "serverInfo": {"name": "GitHub Bash Server", "version": "1.0.0"}
  }
}
EOF
            ;;

        "notifications/initialized")
            # No response required — client just confirms handshake
            ;;

        "tools/list")
            cat <<EOF | emit_json
{
  "jsonrpc": "2.0", "id": $id,
  "result": {
    "tools": [{
      "name": "gh",
      "description": "Run a GitHub CLI command.",
      "inputSchema": {
        "type": "object",
        "properties": {"args": {"type": "string"}},
        "required": ["args"]
      }
    }]
  }
}
EOF
            ;;

        "tools/call")
            args=$(echo "$line" | jq -r '.params.arguments.args' 2>/dev/null)
            result=$(gh $args 2>&1)
            escaped=$(echo "$result" | jq -Rs .)
            cat <<EOF | emit_json
{
  "jsonrpc": "2.0", "id": $id,
  "result": {"content": [{"type": "text", "text": $escaped}]}
}
EOF
            ;;

        *)
            # Unrecognized method — return JSON-RPC error
            cat <<EOF | emit_json
{
  "jsonrpc": "2.0", "id": $id,
  "error": {"code": -32601, "message": "Method not found: $method"}
}
EOF
            ;;
    esac
done
```

This 60-line script handles **one tool** and only STDIO transport. It took more code than the entire FastMCP server with three tools.

---

## 4.2 The SDK Equivalent

The Python FastMCP version of the same server is dramatically simpler:

```python
# github_server.py — Same functionality, FastMCP version
import subprocess
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("GitHub Server")


@mcp.tool()
def gh(args: str) -> str:
    """Run a GitHub CLI command.
    Use to list repos, PRs, issues, or check workflow status.
    Args: a GitHub CLI command string (e.g., 'repo list --limit 5')
    Returns: CLI output as a string.
    Read-only. Safe to automate."""
    try:
        result = subprocess.run(
            ["gh"] + args.split(),
            capture_output=True, text=True, timeout=30
        )
        return result.stdout or result.stderr
    except subprocess.TimeoutExpired:
        return "GitHub CLI command timed out after 30 seconds"
    except Exception as e:
        return f"Error: {str(e)}"


if __name__ == "__main__":
    mcp.run()
```

**The SDK version is safer and more capable** — it automatically handles:
- Protocol version negotiation
- Initialisation handshake
- SSE transport (not just STDIO)
- Schema generation from type hints
- Connection lifecycle management

---

## 4.3 Side-by-Side Comparison

| Aspect | Bash (raw protocol) | Python FastMCP |
|---|---|---|
| Lines of code (single tool) | ~60 | ~20 |
| Transport support | STDIO only | STDIO + SSE |
| Schema generation | Manual JSON | Auto from type hints |
| Error handling | Manual JSON-RPC | Built in |
| Protocol version updates | Break your server | SDK handles |
| Testing | Raw JSON inspection | MCP Inspector |
| Deployment to remote servers | Not possible | `--transport sse` |
| Python version management | N/A | `uv python pin 3.12` |

> **Recommendation from MCP docs:** "Use official MCP SDKs for production. The raw protocol approach reveals how the protocol works but is labor-intensive, difficult to maintain, and limited to STDIO transport."

---

## 4.4 When You Might Need Raw Protocol Knowledge

Understanding JSON-RPC helps in three scenarios:
1. **Debugging**: Reading MCP Inspector's raw JSON to understand why a server isn't responding
2. **Non-Python languages**: If your team needs an MCP server in Go, Rust, or a language without an SDK
3. **Protocol auditing**: Security review of what an MCP server is actually sending

For this course and for production use: **always use FastMCP**.

---

## 4.5 The Complete Production-Ready CTI Server

Putting everything from Module 05 together — one file, four tools, resources, prompts, logging, and uv:

```python
# server.py — Production CTI MCP Server
# Run: uv run mcp dev server.py   (testing)
# Run: uv run server.py            (production / Claude Desktop)

import os, re, requests, sys, logging
from mcp.server.fastmcp import FastMCP, Context
from dotenv import load_dotenv

# System logging (to stderr — not stdout)
logging.basicConfig(level=logging.INFO, stream=sys.stderr)
log = logging.getLogger(__name__)

load_dotenv()
mcp = FastMCP("CTI Enrichment Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")
VT_KEY        = os.environ.get("VT_API_KEY", "")
IPV4 = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")


def valid_ip(ip: str) -> bool:
    return bool(IPV4.match(ip)) and all(0 <= int(o) <= 255 for o in ip.split("."))


@mcp.tool()
def enrich_ip(ip_address: str, ctx: Context = None) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to assess its abuse history.
    Returns: abuse_score (0-100), country, isp, is_tor, total_reports, status.
    Read-only. Safe to automate."""
    if ctx: ctx.info(f"Enriching IP: {ip_address}")
    if not ABUSEIPDB_KEY:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not configured"}
    if not valid_ip(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4: '{ip_address}'"}
    try:
        r = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": ABUSEIPDB_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": 90},
            timeout=10
        )
        r.raise_for_status()
        d = r.json().get("data", {})
        return {
            "ip":           d.get("ipAddress", ip_address),
            "abuse_score":  d.get("abuseConfidenceScore", 0),
            "country":      d.get("countryCode", "unknown"),
            "isp":          d.get("isp", "unknown"),
            "is_tor":       d.get("usageType", "") == "Tor/Anonymizer",
            "total_reports":d.get("totalReports", 0),
            "status":       "ok"
        }
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "AbuseIPDB timed out"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.resource("threat://triage-policy")
def triage_policy() -> str:
    return """CTI TRIAGE POLICY v2.1
abuse_score >= 80: Escalate immediately. Recommended: quarantine + block (approval required)
abuse_score 40-79: Analyst review within 2 hours
abuse_score < 40: Log and close unless other indicators present
Tor exit node: Always escalate regardless of score
All Act tools: MANDATORY analyst approval before execution"""


if __name__ == "__main__":
    mcp.run()
```

---

## Key Takeaways

1. Raw protocol requires 3× more code, is STDIO-only, and breaks on protocol updates.
2. FastMCP abstracts JSON-RPC, schema generation, transport, and lifecycle — use it always.
3. Understanding raw protocol helps with debugging and security auditing — but not for building.
4. The Bash server example from the source material is an excellent teaching tool to see JSON-RPC, but should never be used in production.
5. The complete production server combines Block 1 (setup), Block 2 (tools + resources), Block 3 (transport), and Module 04 patterns (validation, error handling, env vars).
