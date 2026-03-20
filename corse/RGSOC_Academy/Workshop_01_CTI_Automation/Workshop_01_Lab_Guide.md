---
status: published
---

# Workshop 1: CTI Automation with MCP

## Workshop Goal

Build a working MCP-powered CTI enrichment workflow that ingests IOCs from a raw alert, enriches each indicator using multiple sources, and produces a structured intelligence brief.

## Prerequisites

- Modules 1–6 complete.
- Python 3.10+ installed with `mcp`, `fastmcp`, `requests`, and `groq`, `langchain-groq` packages.
- API keys: VirusTotal (free tier), AbuseIPDB (free tier).
- Google Colab configured for running Langchain / Groq Autonomous Agents.

---

## Lab Overview

| Step | Task | Tools Used |
|---|---|---|
| 1 | Build the CTI enrichment server | FastMCP, VirusTotal API, AbuseIPDB API |
| 2 | Test the server locally | MCP Inspector |
| 3 | Register the server in your AI workspace | Trae / Claude Desktop |
| 4 | Run a live triage via the AI agent | AI chat + MCP tools |
| 5 | Produce an intelligence brief | AI client output |

---

## Step 1: Build the CTI Enrichment Server

Create `d:/mcp_course/servers/cti_server.py`:

```python
import os, re, requests
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("CTI Enrichment Server")

VT_KEY = os.environ.get("VT_API_KEY", "")
ABUSE_KEY = os.environ.get("ABUSEIPDB_KEY", "")

NORMALIZATION = {
    "malicious": "flagged by vendor",
    "suspicious": "notable",
    "dangerous": "commonly scrutinized",
    "infected": "flagged",
}

def normalize(text: str) -> str:
    for term, replacement in NORMALIZATION.items():
        text = text.replace(term, replacement)
    return text

def valid_ipv4(ip: str) -> bool:
    parts = ip.split(".")
    return len(parts) == 4 and all(p.isdigit() and 0 <= int(p) <= 255 for p in parts)

def valid_hash(h: str) -> bool:
    return bool(re.match(r"^[a-fA-F0-9]{32}$|^[a-fA-F0-9]{40}$|^[a-fA-F0-9]{64}$", h))


@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """Check an IPv4 address for threat intelligence using AbuseIPDB.
    Use when an analyst provides an IP from an alert, log, or email header.
    Returns abuse confidence score, country, ISP, and total report count.
    Read-only. Safe to automate."""
    if not valid_ipv4(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4: {ip_address}"}
    try:
        resp = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": ABUSE_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": 90},
            timeout=10
        ).json().get("data", {})
        return {
            "ip": ip_address,
            "abuse_confidence": resp.get("abuseConfidenceScore", 0),
            "country": resp.get("countryCode", "unknown"),
            "isp": resp.get("isp", "unknown"),
            "total_reports": resp.get("totalReports", 0),
            "usage_type": resp.get("usageType", "unknown"),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def enrich_domain(domain: str) -> dict:
    """Check a domain against VirusTotal and return vendor detection count and registration info.
    Use when a suspicious domain appears in email headers, DNS logs, or alerts.
    Read-only. Safe to automate."""
    try:
        attrs = requests.get(
            f"https://www.virustotal.com/api/v3/domains/{domain}",
            headers={"x-apikey": VT_KEY},
            timeout=10
        ).json().get("data", {}).get("attributes", {})
        stats = attrs.get("last_analysis_stats", {})
        return {
            "domain": domain,
            "malicious_votes": stats.get("malicious", 0),
            "harmless_votes": stats.get("harmless", 0),
            "undetected": stats.get("undetected", 0),
            "creation_date": attrs.get("creation_date", "unknown"),
            "registrar": attrs.get("registrar", "unknown"),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def enrich_hash(file_hash: str) -> dict:
    """Look up a file hash (MD5, SHA1, or SHA256) on VirusTotal.
    Use when a file hash appears in an alert or malware report.
    Returns detection count and top known malware family names.
    Read-only. Safe to automate."""
    if not valid_hash(file_hash):
        return {"status": "error", "reason": f"Invalid hash format: {file_hash}"}
    try:
        attrs = requests.get(
            f"https://www.virustotal.com/api/v3/files/{file_hash}",
            headers={"x-apikey": VT_KEY},
            timeout=10
        ).json().get("data", {}).get("attributes", {})
        stats = attrs.get("last_analysis_stats", {})
        names = list(set(
            r.get("result") for r in attrs.get("last_analysis_results", {}).values()
            if r.get("result")
        ))[:5]
        return {
            "hash": file_hash,
            "malicious_detections": stats.get("malicious", 0),
            "total_scans": sum(stats.values()),
            "known_families": names,
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## Step 2: Test with MCP Inspector

```bash
$env:VT_API_KEY="your-vt-key"
$env:ABUSEIPDB_KEY="your-abuse-key"
npx @modelcontextprotocol/inspector python d:/mcp_course/servers/cti_server.py
```

Open `http://localhost:5173`. Test each tool manually:
- `enrich_ip` with `185.220.101.45`
- `enrich_domain` with `safe-update-portal.net`
- `enrich_hash` with a known hash (search VirusTotal for a public sample)

**Expected:** each tool returns a valid `{"status": "ok", ...}` dict.

---

## Step 3: Register in AI Workspace

**Trae AI:** Add stdio server → `python` → `d:/mcp_course/servers/cti_server.py` → set env vars.

**Claude Desktop** (`claude_desktop_config.json`):
```json
{
  "mcpServers": {
    "cti": {
      "command": "python",
      "args": ["d:/mcp_course/servers/cti_server.py"],
      "env": {
        "VT_API_KEY": "your-vt-key",
        "ABUSEIPDB_KEY": "your-abuse-key"
      }
    }
  }
}
```

---

## Step 4: Live Triage Exercise

Use the following sample alert in chat with your AI agent:

```
Alert: Suspicious outbound connection detected.
Source IP: 192.168.10.55 (internal endpoint)
Destination IP: 185.220.101.45
Domain in DNS log: update-secure-patch[.]net
Attachment hash seen in email: 3395856ce81f2b7382dee72602f798b642f14d8
Timestamp: 2026-03-09 21:43 UTC
```

**Prompt:** *"Triage this alert. Enrich all IOCs and tell me the risk level and recommended action."*

**Expected agent behavior:**
1. Calls `enrich_ip("185.220.101.45")`.
2. Calls `enrich_domain("update-secure-patch.net")`.
3. Calls `enrich_hash("3395856ce81f2b7382dee72602f798b642f14d8")`.
4. Synthesizes results into a structured brief.

---

## Step 5: Intelligence Brief — Expected Output Format

Your brief should contain:

```
RISK LEVEL: HIGH / MEDIUM / LOW

SUMMARY:
[2–3 sentence narrative of findings]

IOC ENRICHMENT:
- IP 185.220.101.45: abuse score X, country Y, Z reports
- Domain update-secure-patch.net: X/68 vendor detections, registered N days ago
- Hash 3395856c...: X/68 detections, known as [family]

COMPLEXITY LEVEL: Clear / Complicated / Complex

RECOMMENDED ACTION:
[Specific action — block, escalate, monitor, or investigate]

APPROVAL REQUIRED: Yes / No
```

---

## Lab Checklist

- [ ] Server starts without errors.
- [ ] All three tools return valid `status: published` in Inspector.
- [ ] Server registered in AI workspace.
- [ ] Agent correctly enriches all three IOC types from the sample alert.
- [ ] Brief includes risk level, complexity level classification, and recommended action.
- [ ] At least one tool returned `status: published` during testing (confirm error handling works).

---

## Extension Challenge

Add a fourth tool: `get_whois(domain: str) -> dict` using the `python-whois` library that returns:
- Creation date, expiry date, registrar, and name server count.
- Flag `newly_registered: true` if creation date is under 30 days ago.

Register it and re-run the triage to see how the brief changes.

