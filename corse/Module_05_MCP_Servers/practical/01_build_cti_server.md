---
status: draft
---

# Practical 01 — Build a Working CTI MCP Server

> **Practical Block 1 of 3 | Module 05: Building MCP Servers**

---

## Objective

By the end of this practical you will have a working MCP server with three CTI tools, tested in the Inspector and registered in Claude Desktop (or your preferred client).

---

## Part A: Project Setup with uv

```powershell
# From your course directory
cd d:/mcp_course

# Create the project
uv init cti-mcp-server
cd cti-mcp-server

# Pin Python 3.12
uv python pin 3.12

# Add all dependencies
uv add "mcp[cli]" requests python-dotenv

# Create server file
New-Item server.py

# Create .env
New-Item .env

# Create .gitignore
@"
.env
.venv/
__pycache__/
*.pyc
"@ | Out-File .gitignore
```

Add your keys to `.env`:
```
ABUSEIPDB_KEY=your-abuseipdb-key
VT_API_KEY=your-virustotal-key
```

---

## Part B: Build the Server — Three Tools

Fill in the missing pieces in this starter template:

```python
# server.py
import os, re, requests, sys, logging
from mcp.server.fastmcp import FastMCP
from dotenv import load_dotenv

logging.basicConfig(level=logging.INFO, stream=sys.stderr)

load_dotenv()
mcp = FastMCP("CTI Enrichment Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")
VT_KEY        = os.environ.get("VT_API_KEY", "")

IPV4 = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")


def valid_ip(ip: str) -> bool:
    return bool(IPV4.match(ip)) and all(0 <= int(o) <= 255 for o in ip.split("."))


def valid_hash(h: str) -> bool:
    return len(h) in {32, 40, 64} and all(c in "0123456789abcdefABCDEF" for c in h)


# ── TOOL 1: Enrich IP ────────────────────────────────────────────
@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """[Q1: Write the 4-line docstring here]"""

    # Q2: Add the API key guard
    if ___:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not configured"}

    # Q3: Add IP format validation
    if ___:
        return {"status": "error", "reason": f"Invalid IPv4: '{ip_address}'"}

    try:
        r = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": ABUSEIPDB_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": 90},
            timeout=___  # Q4: What value?
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
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": ___}  # Q5: What message?
    except Exception as e:
        return {"status": "error", "reason": str(e)}


# ── TOOL 2: Enrich Hash (using MalwareBazaar — no key needed) ────
@mcp.tool()
def enrich_hash(hash_value: str) -> dict:
    """Look up a file hash in MalwareBazaar threat intelligence.
    Use when you have a file hash from an alert or malware scan.
    Returns: found (bool), signature (malware family), tags, status.
    Read-only. Safe to automate."""

    hash_value = hash_value.strip().lower()
    if not valid_hash(hash_value):
        return {"status": "error", "reason": f"Invalid hash format: '{hash_value}'"}

    try:
        r = requests.post(
            "https://mb-api.abuse.ch/api/v1/",
            json={"query": "get_info", "hash": hash_value},
            timeout=15
        )
        r.raise_for_status()
        raw = r.json()

        if raw.get("query_status") == "hash_not_found":
            return {"hash": hash_value, "found": False, "signature": "not in database", "tags": [], "status": "ok"}

        item = raw.get("data", [{}])[0]
        return {
            "hash":      hash_value,
            "found":     True,
            "signature": item.get("signature", "unknown"),
            "tags":      item.get("tags", []),
            "file_name": item.get("file_name", "unknown"),
            "status":    "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


# ── TOOL 3: Check Domain (VirusTotal) ────────────────────────────
@mcp.tool()
def enrich_domain(domain: str) -> dict:
    """[Q6: Write the 4-line docstring here]"""

    # Q7: Write the domain validation check (must contain ".", no spaces, length > 3)
    if ___:
        return {"status": "error", "reason": f"Invalid domain: '{domain}'"}

    # Q8: Write the API key guard
    if ___:
        return {"status": "error", "reason": "VT_API_KEY not configured"}

    try:
        r = requests.get(
            f"https://www.virustotal.com/api/v3/domains/{domain}",
            headers={"x-apikey": VT_KEY},
            timeout=10
        )
        r.raise_for_status()
        attrs = r.json().get("data", {}).get("attributes", {})
        stats = attrs.get("last_analysis_stats", {})
        return {
            "domain":             domain,
            "malicious":          stats.get("malicious", 0),
            "harmless":           stats.get("harmless", 0),
            "registrar":          attrs.get("registrar", "unknown"),
            "creation_date":      attrs.get("creation_date", "unknown"),
            "status":             "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## Part C: Test in MCP Inspector

```powershell
uv run mcp dev server.py
```

Run these test cases in the Inspector and verify the results:

| Test | Tool | Input | Expected |
|---|---|---|---|
| 1 | `enrich_ip` | `185.220.101.45` | `abuse_score >= 90`, `status: ok` |
| 2 | `enrich_ip` | `"not-an-ip"` | `status: error`, clean error dict |
| 3 | `enrich_ip` | `""` | `status: error`, not a crash |
| 4 | `enrich_hash` | `3395856ce81f2b7382dee72602f798b642f14d8` | `found: true` OR `found: false` (depends on database) |
| 5 | `enrich_hash` | `"abc"` | `status: error` (invalid hash format) |
| 6 | `enrich_domain` | `malware-drop.ru` | `malicious_count`, `status: ok` |

---

## Part D: Register with Claude Desktop

Edit `%APPDATA%\Claude\claude_desktop_config.json`:
```json
{
  "mcpServers": {
    "cti-server": {
      "command": "uv",
      "args": [
        "--directory",
        "d:\\mcp_course\\cti-mcp-server",
        "run",
        "server.py"
      ]
    }
  }
}
```

Restart Claude Desktop. In a new conversation, type:
> "What tools do you have available?"

Claude should list `enrich_ip`, `enrich_hash`, and `enrich_domain`. Then try:
> "Enrich IP 185.220.101.45 and tell me if it's a known threat."

---

## Checklist

- [ ] `uv run mcp dev server.py` opens Inspector without errors
- [ ] All 3 tools visible in Inspector tools panel
- [ ] All 6 test cases pass
- [ ] Server registered in Claude Desktop config
- [ ] Claude can list tools and call `enrich_ip` successfully
