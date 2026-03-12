---
status: draft
---

# Example 01 — From Zero to Running CTI Server in 10 Minutes

> **Example Block 1 of 2 | Module 05: Building MCP Servers**

---

## What This Example Shows

A complete, timed walkthrough: starting from an empty directory, ending with a working MCP server visible in Claude Desktop's tool list. Every command is exact. Every decision is explained.

---

## Step 1 — Create the Project (1 min)

```powershell
cd d:/mcp_course

uv init cti-mcp-server
cd cti-mcp-server

uv python pin 3.12
uv add "mcp[cli]" requests python-dotenv
```

Why `3.12`? MCP SDK has been most stable on 3.12. Python 3.13 may introduce breaking changes with current async implementations — avoid in production until explicitly tested.

---

## Step 2 — Protect Secrets (30 sec)

```powershell
# Create .env
"ABUSEIPDB_KEY=your-key-here" | Out-File .env

# Create .gitignore
@"
.env
.venv/
__pycache__/
*.pyc
"@ | Out-File .gitignore
```

**Rule:** `.env` is created before any code. Not after. The habit matters more than the order.

---

## Step 3 — Write the Server (4 min)

Save as `server.py`:

```python
# server.py — CTI MCP Server
# Test: uv run mcp dev server.py
# Prod: register in claude_desktop_config.json

import os, re, requests, sys, logging
from mcp.server.fastmcp import FastMCP
from dotenv import load_dotenv

# stderr logging — NEVER stdout in an MCP server
logging.basicConfig(level=logging.INFO, stream=sys.stderr)

load_dotenv()
mcp = FastMCP("CTI Enrichment Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")
IPV4 = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")


def valid_ip(ip: str) -> bool:
    return bool(IPV4.match(ip)) and all(0 <= int(o) <= 255 for o in ip.split("."))


@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to assess its abuse history.
    Returns: abuse_score (0-100), country, isp, total_reports, status.
    Read-only. Safe to automate."""

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
            "total_reports":d.get("totalReports", 0),
            "status":       "ok"
        }
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "AbuseIPDB timed out after 10s"}
    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 429:
            return {"status": "error", "reason": "Rate limit hit — wait 60 seconds"}
        return {"status": "error", "reason": f"HTTP {e.response.status_code}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.resource("threat://triage-policy")
def triage_policy() -> str:
    return """TRIAGE POLICY: abuse_score>=80 → escalate. 40-79 → review. <40 → log/close."""


if __name__ == "__main__":
    mcp.run()
```

---

## Step 4 — Test in Inspector (2 min)

```powershell
uv run mcp dev server.py
```

**What to check:**
1. No error output on startup
2. `enrich_ip` appears in Tools panel with the correct 4-line description
3. `threat://triage-policy` appears in Resources panel
4. Call `enrich_ip("185.220.101.45")` → score ≥ 90, status "ok"
5. Call `enrich_ip("not-an-ip")` → `{"status": "error", "reason": "Invalid IPv4: ..."}`

If tests 4 and 5 both pass: the server is production-ready for Claude Desktop.

---

## Step 5 — Register with Claude Desktop (2 min)

Open `%APPDATA%\Claude\claude_desktop_config.json`:

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

Restart Claude Desktop.

---

## Step 6 — Verify in Claude (30 sec)

Type:
> "What MCP tools do you have?"

Expected: Claude lists `enrich_ip` and describes it accurately from the docstring.

Then:
> "Enrich IP 185.220.101.45"

Expected: Claude calls `enrich_ip`, returns the abuse score, country, ISP, and formats a brief response.

**Total time: ~10 minutes from empty directory to Claude calling your tool.**

---

## Key Observations

| Stage | What succeeded | Why it mattered |
|---|---|---|
| `uv python pin 3.12` | Stable runtime | Avoids 3.13 async breakage |
| `.env` before code | Clean secrets management | Habit is architectural |
| 4-line docstring | Claude described the tool correctly | LLM reads docstring verbatim |
| Custom error returns | Invalid IP returned clean dict | Agent got actionable message |
| `uv` in config | Claude starts server automatically | No manual activation needed |
