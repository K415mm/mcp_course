---
status: published
---

# 04 — Error Handling and Input Validation

> **Theoretical Block 4 of 5 | Module 04: Python Essentials for MCP**

---

## 4.1 Why Your MCP Tool Must Never Crash

When a regular Python script crashes, the user sees an error and fixes it. When an **MCP tool crashes**, the AI agent receives an unhandled exception, may retry endlessly, or worse — silently produces an incorrect verdict ("could not enrich, assuming safe"). This can cascade into a wrong containment decision.

**The rule:** Every MCP tool must always return a dict — never raise an unhandled exception.

---

## 4.2 The `try/except` Block — Your Safety Net

```python
def enrich_ip(ip_address: str) -> dict:
    """..."""
    try:
        # All your tool logic goes inside here
        response = requests.get("https://api.abuseipdb.com/...")
        data = response.json()
        return {"status": "ok", "score": data["abuseConfidenceScore"]}

    except Exception as e:
        # If ANYTHING fails, return a clean error dict — not a crash
        return {"status": "error", "reason": str(e)}
```

The `except Exception as e` catches every possible failure: network timeouts, JSON parse errors, missing API keys, missing dict keys — and converts them into a structured result.

---

## 4.3 Specific Exception Types for Better Error Messages

More specific exceptions produce more useful error messages for the analyst:

```python
import requests

def enrich_ip(ip_address: str) -> dict:
    """..."""
    try:
        response = requests.get("https://api.abuseipdb.com/api/v2/check",
                                 headers={"Key": "YOUR_KEY"},
                                 params={"ipAddress": ip_address},
                                 timeout=10)

        data = response.json()
        return {"ip": ip_address, "score": data["data"]["abuseConfidenceScore"], "status": "ok"}

    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "API timed out after 10 seconds"}

    except requests.exceptions.ConnectionError:
        return {"status": "error", "reason": "Network connection failed — check API endpoint"}

    except KeyError as e:
        return {"status": "error", "reason": f"Unexpected API response — missing field: {e}"}

    except Exception as e:
        return {"status": "error", "reason": f"Unexpected error: {str(e)}"}
```

---

## 4.4 Input Validation — Reject Bad Data Before the API Call

LLMs can pass unexpected arguments (e.g., a domain where an IP is expected). Validate inputs *before* making any API call:

```python
import re

def is_valid_ipv4(ip: str) -> bool:
    """Return True if ip looks like a valid IPv4 address."""
    pattern = r"^(\d{1,3}\.){3}\d{1,3}$"
    if not re.match(pattern, ip):
        return False
    return all(0 <= int(part) <= 255 for part in ip.split("."))


def enrich_ip(ip_address: str) -> dict:
    """..."""
    # Validate BEFORE any API call
    if not ip_address:
        return {"status": "error", "reason": "ip_address cannot be empty"}
    if not is_valid_ipv4(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4 format: '{ip_address}'"}

    try:
        # ... API call here
        pass
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

### What to Validate by Input Type

| Input | Validation |
|---|---|
| IP address | Matches `d.d.d.d`, each octet 0–255 |
| Hash | Length 32/40/64, hex characters only |
| Domain | Contains `.`, no spaces |
| File path | Exists, inside allowed directory, size under limit |
| Port | Integer 1–65535 |

---

## 4.5 File Path Validation — A Security-Critical Check

File-handling tools need a specific check: **path traversal prevention**. Without it, a prompt injection attack could make your tool read `/etc/passwd` or `C:\Windows\System32\`.

```python
import os

ALLOWED_DIR = os.environ.get("ANALYSIS_DIR", "d:/mcp_course/labs")

def validate_file_path(file_path: str) -> tuple:
    """Returns (is_valid: bool, error_message: str)"""
    resolved = os.path.realpath(file_path)
    allowed  = os.path.realpath(ALLOWED_DIR)

    if not resolved.startswith(allowed):
        return False, "File path is outside the allowed analysis directory"
    if not os.path.isfile(resolved):
        return False, f"File not found: {file_path}"
    if os.path.getsize(resolved) > 10 * 1024 * 1024:  # 10 MB limit
        return False, "File too large for analysis (max 10 MB)"
    return True, ""
```

Using it in a tool:

```python
def compute_file_hashes(file_path: str) -> dict:
    """Compute MD5/SHA1/SHA256 for a file. Read-only. Safe to automate."""
    is_valid, error = validate_file_path(file_path)
    if not is_valid:
        return {"status": "error", "reason": error}

    try:
        import hashlib
        data = open(file_path, "rb").read()
        return {
            "md5":    hashlib.md5(data).hexdigest(),
            "sha256": hashlib.sha256(data).hexdigest(),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

---

## 4.6 Full Tool with All Safety Patterns Applied

```python
import re, requests, os, hashlib
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("CTI Server")
API_KEY = os.environ.get("ABUSEIPDB_KEY", "")
IPV4 = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")

def valid_ip(ip: str) -> bool:
    return bool(IPV4.match(ip)) and all(0 <= int(o) <= 255 for o in ip.split("."))


@mcp.tool()
def enrich_ip(ip_address: str, days_back: int = 90) -> dict:
    """Retrieve AbuseIPDB threat intelligence for an IPv4 address.
    Use when an IP appears in an alert to get abuse score, country, and ISP.
    Read-only. Safe to automate."""

    if not API_KEY:
        return {"status": "error", "reason": "ABUSEIPDB_KEY environment variable not set"}
    if not valid_ip(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4: '{ip_address}'"}
    if not 1 <= days_back <= 365:
        return {"status": "error", "reason": "days_back must be 1–365"}

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
    except requests.exceptions.Timeout:
        return {"status": "error", "reason": "AbuseIPDB timed out"}
    except requests.exceptions.HTTPError as e:
        return {"status": "error", "reason": f"HTTP {e.response.status_code}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

---

## Key Takeaways

1. MCP tools must never crash — always wrap in `try/except`, always return a dict.
2. Use specific exception types (`Timeout`, `HTTPError`, `KeyError`) for actionable errors.
3. Validate all inputs *before* making API calls to catch bad LLM arguments early.
4. File path validation must use `os.path.realpath()` to prevent path traversal.
5. The `"status": "ok"/"error"` field tells the agent whether the tool succeeded.

---

## Try It Yourself

Take your `enrich_ip` function from Block 2 and add:
1. A `valid_ip()` check — return an error dict if it fails.
2. A `try/except` wrapping the API call.
3. A specific catch for `requests.exceptions.Timeout`.

Then test by passing `"not-an-ip"` — verify you get a clean error dict, not a Python exception.
