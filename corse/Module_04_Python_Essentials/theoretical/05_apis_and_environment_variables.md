---
status: published
---

# 05 — Calling APIs and Using Environment Variables

> **Theoretical Block 5 of 5 | Module 04: Python Essentials for MCP**

---

## 5.1 Why This Is the Bridge to Real MCP Tools

The previous four blocks gave you the Python skills to write, document, and protect a function. This block makes the function *useful* by connecting it to live threat intelligence APIs. After this block, you have every ingredient needed to build a working MCP tool.

---

## 5.2 The `requests` Library

Install once per environment:
```bash
pip install requests
```

Three uses you'll see constantly:

```python
import requests

# GET — fetching data (most threat intel APIs)
response = requests.get(url, headers={}, params={}, timeout=10)

# POST — submitting data (sandbox submission, report creation)
response = requests.post(url, headers={}, json={}, timeout=10)

# Parse the JSON response into a Python dict
data = response.json()
```

Always set `timeout=10` (seconds). Without it, a slow or down API can freeze your tool indefinitely — blocking the agent.

---

## 5.3 Reading the Response

```python
response = requests.get("https://api.abuseipdb.com/api/v2/check", ...)

print(response.status_code)   # 200=OK, 401=bad key, 429=rate limited

# Convert JSON to dict
data = response.json()

# Navigate safely with .get()
ip_data = data.get("data", {})
score   = ip_data.get("abuseConfidenceScore", 0)
country = ip_data.get("countryCode", "unknown")
```

Check for HTTP errors:
```python
response.raise_for_status()   # Raises HTTPError automatically on 4xx/5xx
```

---

## 5.4 Environment Variables — Never Hardcode API Keys

```python
# ❌ NEVER — key is in source code / git history
API_KEY = "abc123secret"

# ✅ ALWAYS — read from environment
import os
API_KEY = os.environ.get("ABUSEIPDB_KEY", "")

if not API_KEY:
    return {"status": "error", "reason": "ABUSEIPDB_KEY env variable not set"}
```

### Setting Variables (Windows PowerShell)
```powershell
# Current session only
$env:ABUSEIPDB_KEY = "your-key-here"
$env:VT_API_KEY    = "your-vt-key"

# Verify it's set
echo $env:ABUSEIPDB_KEY
```

### Using a `.env` File (Recommended for Development)

**Install:**
```bash
pip install python-dotenv
```

**Create `.env` in your project root (add to `.gitignore` — never commit this):**
```
ABUSEIPDB_KEY=your-abuseipdb-key
VT_API_KEY=your-virustotal-key
SHODAN_KEY=your-shodan-key
ANALYSIS_DIR=d:/mcp_course/labs
```

**Load at the top of your server file:**
```python
from dotenv import load_dotenv
import os

load_dotenv()   # Reads .env and injects into os.environ

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")
VT_KEY        = os.environ.get("VT_API_KEY", "")
```

---

## 5.5 The Complete Pattern: API + Env + Error Handling

This is the template for every API-calling MCP tool in the course:

```python
import os, requests
from mcp.server.fastmcp import FastMCP
from dotenv import load_dotenv

load_dotenv()
mcp = FastMCP("CTI Server")

ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")


@mcp.tool()
def enrich_ip(ip_address: str, days_back: int = 90) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to get abuse score, country, and ISP.
    Returns: abuse_score (0-100), country, isp, total_reports, status.
    Read-only. Safe to automate."""

    # Guard: API key configured?
    if not ABUSEIPDB_KEY:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not set. Add it to .env or environment."}

    try:
        response = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": ABUSEIPDB_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": days_back},
            timeout=10
        )
        response.raise_for_status()
        data = response.json().get("data", {})

        return {
            "ip":            data.get("ipAddress", ip_address),
            "abuse_score":   data.get("abuseConfidenceScore", 0),
            "country":       data.get("countryCode", "unknown"),
            "isp":           data.get("isp", "unknown"),
            "total_reports": data.get("totalReports", 0),
            "status":        "ok"
        }

    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "AbuseIPDB request timed out after 10s"}
    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 429:
            return {"status": "error", "reason": "Rate limit hit — wait 60 seconds and retry"}
        return {"status": "error", "reason": f"API returned HTTP {e.response.status_code}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## 5.6 Common Threat Intel APIs — Quick Reference

| API | Use Case | Auth Header | Free Tier |
|---|---|---|---|
| AbuseIPDB | IP abuse history | `Key: YOUR_KEY` | 1,000 req/day |
| VirusTotal | Hash / IP / domain | `x-apikey: YOUR_KEY` | 500 req/day |
| Shodan | Internet scan data | `?key=key` (param) | 100 req/month |
| URLScan.io | URL / domain analysis | `API-Key: YOUR_KEY` | 5,000 req/month |
| OTX AlienVault | Multi-IOC threat feeds | `X-OTX-API-KEY: YOUR_KEY` | Unlimited (public pulses) |

---

## 5.7 Rate Limits — Be a Good API Citizen

Every free API has limits. On 429 errors:

```python
except requests.exceptions.HTTPError as e:
    if e.response.status_code == 429:
        return {"status": "error", "reason": "Rate limit — pause 60 seconds before retrying"}
```

For bulk batch processing, add a small delay between calls:

```python
import time

for ip in ip_list:
    result = enrich_ip(ip)
    print(result)
    time.sleep(0.5)   # 500ms delay = max ~120 req/min well under most limits
```

---

## Key Takeaways

1. `requests.get()` with `timeout=10` is the standard pattern for API calls in MCP tools.
2. Use `response.raise_for_status()` to auto-catch HTTP error codes.
3. Never put API keys in code — use `os.environ.get()` and `.env` files.
4. Add `.env` to `.gitignore` — it is never committed to source control.
5. Handle 429 rate limit errors explicitly so the agent gets a clear "wait and retry" message.
6. Load API keys once at module level, not inside each function.

---

## Try It Yourself

1. Sign up for a free AbuseIPDB API key at [abuseipdb.com/api](https://www.abuseipdb.com/api).
2. Create a `.env` file: `ABUSEIPDB_KEY=your-key`.
3. Copy the complete `enrich_ip` function above into `test_enrich.py`.
4. Remove the `@mcp.tool()` decorator and FastMCP setup — just test the function.
5. At the bottom add: `print(enrich_ip("185.220.101.45"))`
6. Run: `python test_enrich.py`

If you see a dict with an `abuse_score` field — you have built the core of your first MCP tool. Add `@mcp.tool()` back in Module 5 and it becomes AI-callable.
