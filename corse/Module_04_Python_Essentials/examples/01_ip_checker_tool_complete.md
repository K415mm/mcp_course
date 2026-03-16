---
status: published
---

# Example 01 — Complete IP Checker Tool: From Scratch to Running

> **Example Block 1 of 2 | Module 04: Python Essentials for MCP**

---

## What This Example Shows

This is not an exercise — it is a complete, working, annotated tool that you run and inspect. Every line has a comment explaining why it is written that way. Read it, run it, then modify one thing at a time.

---

## The Complete File

Save this as `d:/mcp_course/practice/ip_checker.py`:

```python
# ─────────────────────────────────────────────────────────────────
# ip_checker.py — Complete IP enrichment tool (standalone, no MCP)
# This file demonstrates all Module 04 Python patterns.
# Run with: python ip_checker.py
# ─────────────────────────────────────────────────────────────────

import os          # For reading environment variables
import re          # For input validation with regular expressions
import requests    # For making HTTP API calls
from dotenv import load_dotenv  # For reading our .env file

# ── Load environment variables ────────────────────────────────────
# load_dotenv() reads the .env file and puts its values into os.environ.
# This must happen BEFORE any os.environ.get() calls.
load_dotenv()

# ── Load API key once, at module level ───────────────────────────
# We load the key here, not inside the function.
# Reason: if the key is missing, we find out when the module loads,
# not when the function is called mid-investigation.
ABUSEIPDB_KEY = os.environ.get("ABUSEIPDB_KEY", "")


# ── Input validator ───────────────────────────────────────────────
# Compiled regex patterns are faster if called many times in a loop.
IPV4_PATTERN = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")

def validate_ipv4(ip: str) -> bool:
    """Return True if ip is a valid IPv4 address string."""
    if not IPV4_PATTERN.match(ip):
        return False  # Doesn't even match the dot pattern
    parts = ip.split(".")
    return all(0 <= int(p) <= 255 for p in parts)  # Each octet in range


# ── Risk level classifier ─────────────────────────────────────────
def classify_risk(score: int) -> str:
    """Convert an abuse score (0-100) into a risk level string."""
    if score >= 80:
        return "HIGH"
    elif score >= 40:
        return "MEDIUM"
    else:
        return "LOW"


# ── The main tool function ────────────────────────────────────────
# This is what becomes an @mcp.tool() in Module 05.
# Right now it's a plain Python function — easier to test and debug.

def enrich_ip(ip_address: str, days_back: int = 90) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.

    Use when an IP appears in an alert to assess its abuse history
    and determine risk level for triage.

    Returns: ip, abuse_score, country, isp, is_tor, total_reports,
             risk_level, status.

    Read-only. Safe to automate.
    """

    # ── Guard 1: API key ─────────────────────────────────────────
    # If ABUSEIPDB_KEY is empty (not set), return an error dict.
    # Do NOT crash. Do NOT print an error and exit.
    if not ABUSEIPDB_KEY:
        return {
            "status": "error",
            "reason": "ABUSEIPDB_KEY environment variable is not set. "
                      "Add it to your .env file or set it in PowerShell."
        }

    # ── Guard 2: IP format validation ────────────────────────────
    if not ip_address:
        return {"status": "error", "reason": "ip_address cannot be empty"}

    if not validate_ipv4(ip_address):
        return {
            "status": "error",
            "reason": f"'{ip_address}' is not a valid IPv4 address. "
                      "Expected format: 123.123.123.123"
        }

    # ── Guard 3: Parameter range check ───────────────────────────
    if not (1 <= days_back <= 365):
        return {"status": "error", "reason": "days_back must be between 1 and 365"}

    # ── API Call ──────────────────────────────────────────────────
    # Everything that can go wrong is inside try/except.
    try:
        response = requests.get(
            "https://api.abuseipdb.com/api/v2/check",

            # Auth header: AbuseIPDB uses "Key" as the header name
            headers={
                "Key": ABUSEIPDB_KEY,
                "Accept": "application/json"  # Tells the API we want JSON back
            },

            # Query parameters — appended to the URL automatically:
            # ?ipAddress=185.220.101.45&maxAgeInDays=90
            params={
                "ipAddress": ip_address,
                "maxAgeInDays": days_back
            },

            timeout=10  # ALWAYS set a timeout. Never let a tool hang.
        )

        # raise_for_status() raises requests.exceptions.HTTPError
        # automatically if the status code is 4xx or 5xx.
        response.raise_for_status()

        # Convert the response body from JSON text into a Python dict.
        raw = response.json()

        # Navigate to the data section.
        # Use .get("data", {}) — if "data" is missing, we get {} not a crash.
        data = raw.get("data", {})

        # ── Build the result dict ─────────────────────────────────
        # Use .get() with sensible defaults on EVERY field.
        # API responses can change; fields can be missing; never assume.
        score = data.get("abuseConfidenceScore", 0)

        return {
            "ip":            data.get("ipAddress", ip_address),
            "abuse_score":   score,
            "country":       data.get("countryCode", "unknown"),
            "isp":           data.get("isp", "unknown"),
            "is_tor":        data.get("usageType", "") == "Tor/Anonymizer",
            "total_reports": data.get("totalReports", 0),
            "risk_level":    classify_risk(score),  # Human-readable risk
            "status":        "ok"
        }

    # ── Exception handlers ────────────────────────────────────────
    # Each case returns a specific, actionable error message.

    except requests.exceptions.Timeout:
        return {
            "status": "error",
            "reason": "AbuseIPDB did not respond within 10 seconds. "
                      "Check your internet connection or try again shortly."
        }

    except requests.exceptions.ConnectionError:
        return {
            "status": "error",
            "reason": "Could not connect to AbuseIPDB. "
                      "Check network connectivity."
        }

    except requests.exceptions.HTTPError as e:
        code = e.response.status_code
        if code == 401:
            return {"status": "error", "reason": "Invalid AbuseIPDB API key (401)"}
        if code == 429:
            return {"status": "error", "reason": "Rate limit hit (429). Wait 60 seconds."}
        return {"status": "error", "reason": f"AbuseIPDB returned HTTP {code}"}

    except Exception as e:
        # Catch-all: any unexpected error returns a structured result
        return {"status": "error", "reason": f"Unexpected error: {str(e)}"}


# ── Manual test block ─────────────────────────────────────────────
# This only runs when you run this file directly: python ip_checker.py
# It does NOT run when another file imports this module.

if __name__ == "__main__":

    print("=== Test 1: Valid high-risk IP ===")
    result = enrich_ip("185.220.101.45")
    print(result)
    print()

    print("=== Test 2: Invalid IP format ===")
    result = enrich_ip("not-an-ip")
    print(result)
    print()

    print("=== Test 3: Known clean IP (Google DNS) ===")
    result = enrich_ip("8.8.8.8")
    print(result)
    print()

    print("=== Test 4: Empty input ===")
    result = enrich_ip("")
    print(result)
    print()

    print("=== Test 5: IP with custom days_back ===")
    result = enrich_ip("185.220.101.45", days_back=30)
    print(f"Score (30 days): {result.get('abuse_score')}")
```

---

## Running the File

```powershell
# Set your API key first
$env:ABUSEIPDB_KEY = "your-key-here"

# Run the file
python d:/mcp_course/practice/ip_checker.py
```

**Expected output shape for Test 1:**
```python
{
  'ip': '185.220.101.45',
  'abuse_score': 98,
  'country': 'NL',
  'isp': 'Frantech Solutions',
  'is_tor': True,
  'total_reports': 412,
  'risk_level': 'HIGH',
  'status': 'ok'
}
```

**Expected output for Test 2:**
```python
{
  'status': 'error',
  'reason': "'not-an-ip' is not a valid IPv4 address. Expected format: 123.123.123.123"
}
```

---

## Modification Exercises

1. **Add `days_back` to the result dict** — so callers know what time window was used.
2. **Add a `last_reported` field** — extract `lastReportedAt` from the API response.
3. **Add a `verdict` field** using this logic:
   - `abuse_score >= 80` AND `total_reports >= 10` AND `is_tor == True` → `"strong_c2_candidate"`
   - `abuse_score >= 80` → `"high_risk"`
   - `abuse_score >= 40` → `"elevated_risk"`
   - Otherwise → `"low_risk"`
4. **Add a batch function** `enrich_ip_batch(ip_list: list) -> dict` that calls `enrich_ip()` for each IP in the list and returns a summary dict with all results.

---

## What Changes in Module 05

In Module 05, you:
1. `pip install fastmcp`
2. Add `from mcp.server.fastmcp import FastMCP` and `mcp = FastMCP("CTI Server")`
3. Add `@mcp.tool()` above the function
4. Change `if __name__ == "__main__": mcp.run()`

That's it. The function body stays identical. The Python you wrote here is already 95% of an MCP tool.
