---
status: draft
---

# 02 — Tools, Resources, and Prompts

> **Theoretical Block 2 of 4 | Module 05: Building MCP Servers**

---

## 2.1 The Three Capabilities of an MCP Server

FastMCP servers can expose three types of things to an LLM client. Each serves a different purpose in the agentic workflow:

| Type | Decorator | Who triggers it | Typical use |
|---|---|---|---|
| **Tool** | `@mcp.tool()` | The AI agent (autonomously) | Call APIs, compute hashes, query databases |
| **Resource** | `@mcp.resource("uri")` | The agent or user | Read static/dynamic data from a URI-addressable source |
| **Prompt** | `@mcp.prompt()` | The user explicitly | Pre-written prompts that populate the chat with structured instructions |

For cyber defense MCP servers, **tools dominate** — they are what agents call to execute enrichment, analysis, and response workflows. Resources and prompts complete the picture for production servers.

---

## 2.2 Tools — The Agent-Callable Functions

You have built tools throughout Module 04. In Module 05, you add `@mcp.tool()` and `mcp.run()` — that's the only change.

```python
from mcp.server.fastmcp import FastMCP
import os, requests
from dotenv import load_dotenv

load_dotenv()
mcp = FastMCP("CTI Server")

API_KEY = os.environ.get("ABUSEIPDB_KEY", "")


@mcp.tool()
def enrich_ip(ip_address: str, days_back: int = 90) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to get abuse score, country, and ISP.
    Returns: abuse_score (0-100), country, isp, total_reports, status.
    Read-only. Safe to automate."""

    if not API_KEY:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not set"}

    try:
        r = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": API_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": days_back},
            timeout=10
        )
        r.raise_for_status()
        d = r.json().get("data", {})
        return {
            "ip":           d.get("ipAddress", ip_address),
            "abuse_score":  d.get("abuseConfidenceScore", 0),
            "country":      d.get("countryCode", "unknown"),
            "isp":          d.get("isp", "unknown"),
            "total_reports":d.get("totalReports", 0),
            "status":       "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

### Tool Naming Rules

The tool function name becomes the tool name the LLM sees. Good naming is critical:

| ❌ Confusing | ✅ Clear |
|---|---|
| `check(data)` | `enrich_ip(ip_address)` |
| `process(f)` | `compute_file_hashes(file_path)` |
| `lookup(x, y)` | `lookup_hash(hash_value)` |
| `run(cmd)` | `execute_yara_scan(file_path, ruleset)` |

Name for the agent's understanding, not for Python brevity.

---

## 2.3 Resources — URI-Addressable Data

Resources expose read-only data at a stable URI. The agent (or user) requests data by URI — they don't execute logic. Think of them as GET endpoints the agent can read from, as opposed to functions it can call.

```python
@mcp.resource("threat://feeds/current-ip-blocklist")
def get_ip_blocklist() -> str:
    """Return the current IP blocklist as a newline-separated string."""
    blocklist = ["185.220.101.45", "91.108.4.46", "103.21.244.0"]
    return "\n".join(blocklist)


@mcp.resource("threat://reports/{report_id}")
def get_report(report_id: str) -> str:
    """Return a stored incident report by ID."""
    # In production, look up from a database
    reports = {
        "INC-0099": "Phishing campaign targeting finance team. IOC: malware-drop.ru",
        "INC-0100": "C2 beaconing from 192.168.1.55 to 185.220.101.45",
    }
    return reports.get(report_id, f"Report {report_id} not found")
```

**When to use resources instead of tools:**
- Data that changes infrequently (blocklists, policy documents, known-good lists)
- Data the agent should **read** without needing to pass arguments
- Structured documents the LLM should include as context (not compute on)

---

## 2.4 Prompts — User-Invoked Templates

Prompts are pre-written message sequences that the **user** selects to start or guide a task. They don't run logic — they generate structured text that appears in the conversation.

```python
from mcp.server.fastmcp import FastMCP
from mcp.types import PromptMessage, TextContent

mcp = FastMCP("CTI Server")


@mcp.prompt()
def triage_alert(alert_id: str, alert_type: str) -> list[PromptMessage]:
    """Generate a structured triage prompt for a given alert."""
    return [
        PromptMessage(
            role="user",
            content=TextContent(
                type="text",
                text=f"""You are a SOC analyst. Triage alert {alert_id}.
Alert type: {alert_type}

Use the available CTI tools to:
1. Enrich all IPs, domains, and hashes in the alert
2. Classify the Cynefin domain based on evidence
3. Draft a triage brief with recommended actions

Do NOT take any containment actions without my explicit approval."""
            )
        )
    ]
```

In Claude Desktop, the user clicks the prompt template, fills in `alert_id` and `alert_type`, and the structured message is sent to the LLM — which then begins the tool-calling workflow.

---

## 2.5 A Full CTI Server with All Three Capabilities

```python
# cti_server.py — Tools + Resources + Prompts
from mcp.server.fastmcp import FastMCP
from mcp.types import PromptMessage, TextContent
import os, requests
from dotenv import load_dotenv

load_dotenv()
mcp = FastMCP("CTI Enrichment Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")

# ── TOOL ────────────────────────────────────────────────────────
@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to get abuse score and country.
    Returns: abuse_score, country, isp, total_reports, status.
    Read-only. Safe to automate."""
    # ... (full implementation from Block 1) ...
    return {"status": "ok", "ip": ip_address, "abuse_score": 0}


# ── RESOURCE ─────────────────────────────────────────────────────
@mcp.resource("threat://triage-policy")
def get_triage_policy() -> str:
    """Return the current triage policy document."""
    return """TRIAGE POLICY v2.1
    - abuse_score >= 80: Escalate to analyst immediately
    - abuse_score 40-79: Flag for manual review within 2 hours
    - abuse_score < 40: Log and close if no other indicators
    - Tor exit node: Always escalate regardless of score
    - Act tools: ALWAYS require analyst approval"""


# ── PROMPT ───────────────────────────────────────────────────────
@mcp.prompt()
def ip_triage(ip_address: str, alert_context: str) -> list[PromptMessage]:
    """Generate a triage prompt for a suspicious IP address."""
    return [
        PromptMessage(role="user", content=TextContent(type="text", text=f"""
Triage the IP address {ip_address} as follows:
1. Call enrich_ip("{ip_address}")
2. Read the triage policy from threat://triage-policy
3. Apply the policy to determine the action tier
4. Write a structured triage brief

Context from alert: {alert_context}

Do not block or quarantine without explicit analyst approval."""))
    ]


if __name__ == "__main__":
    mcp.run()
```

---

## Key Takeaways

1. MCP servers expose three things: **tools** (agent-called functions), **resources** (URI-addressed data), **prompts** (user-invoked templates).
2. Tools are the primary building block for cyber defense servers.
3. Resource URIs use a custom scheme like `threat://` — define your own meaningful namespace.
4. Prompt templates are how you give users structured, repeatable starting points for investigations.
5. Name tools descriptively — the LLM reads the name to decide whether to call it.
