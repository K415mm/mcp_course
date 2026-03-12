---
status: draft
---

# Practical 01 — Write Your First MCP Tool

> **Practical Block 1 of 3 | Module 04: Python Essentials for MCP**

---

## Exercise Goal

Write a complete, working Python function that embodies all the MCP tool design principles from Blocks 1–5: type hints, docstring, error handling, input validation, and a structured return dict.

---

## Setup

You need Python 3.10+ and two packages:

```bash
pip install requests python-dotenv
```

Create a file called `my_first_tool.py` in `d:/mcp_course/practice/`.

---

## Exercise 1 — The Starter Function (Guided)

Complete the blanks in this function:

```python
import os, re, requests
from dotenv import load_dotenv

load_dotenv()

API_KEY = os.environ.get("ABUSEIPDB_KEY", "")

def validate_ipv4(ip: str) -> bool:
    """Return True if ip is a valid IPv4 string."""
    pattern = re.compile(r"^(\d{1,3}\.){3}\d{1,3}$")
    if not pattern.match(ip):
        return False
    return all(0 <= int(part) <= 255 for part in ip.split("."))


def enrich_ip(ip_address: ___) -> ___:   # Q1: fill in type hints
    """
    ___                           # Q2: what does this tool do?
    ___                           # Q3: when should the agent call this?
    Returns: abuse_score, country, isp, total_reports, status.
    ___                           # Q4: safety classification
    """

    # Q5: add the API key guard
    if ___:
        return {"status": "error", "reason": "ABUSEIPDB_KEY not configured"}

    # Q6: add IP format validation
    if ___:
        return {"status": "error", "reason": f"Invalid IPv4: '{ip_address}'"}

    try:
        response = requests.get(
            "https://api.abuseipdb.com/api/v2/check",
            headers={"Key": API_KEY, "Accept": "application/json"},
            params={"ipAddress": ip_address, "maxAgeInDays": 90},
            timeout=___    # Q7: what timeout value? Why?
        )
        response.raise_for_status()
        data = response.json().get("data", {})

        return {
            "ip":           data.get(___),       # Q8: what key is the IP in?
            "abuse_score":  data.get("abuseConfidenceScore", ___),  # Q9: default if missing?
            "country":      data.get("countryCode", "unknown"),
            "isp":          data.get("isp", "unknown"),
            "total_reports":data.get("totalReports", 0),
            "status":       "ok"
        }

    except requests.exceptions.Timeout:
        return {"status": "error", "reason": ___}    # Q10: what message?

    except Exception as e:
        return {"status": "error", "reason": str(e)}


# Test it
if __name__ == "__main__":
    result = enrich_ip("185.220.101.45")
    print(result)

    # Test validation
    bad = enrich_ip("not-an-ip")
    print(bad)
```

**Answers to check your work:**
- Q1: `str` → `dict`
- Q5: `not API_KEY`
- Q6: `not validate_ipv4(ip_address)`
- Q7: `10` (seconds — never hang indefinitely)
- Q8: `"ipAddress"`, default: `ip_address`
- Q9: default: `0`
- Q10: `"AbuseIPDB request timed out after 10 seconds"`

---

## Exercise 2 — Write a Domain Enrichment Tool From Scratch

Without a template this time. Use everything from Exercise 1 as your guide.

**API:** VirusTotal Domain Lookup
- URL: `https://www.virustotal.com/api/v3/domains/{domain}`
- Auth header: `{"x-apikey": YOUR_KEY}`
- Response path: `data.attributes.last_analysis_stats.malicious` (number of malicious detections)
- Other fields: `data.attributes.last_analysis_stats.harmless`, `data.attributes.registrar`, `data.attributes.creation_date`

**Environment variable:** `VT_API_KEY`

**Requirements:**
- Function name: `enrich_domain(domain: str) -> dict`
- Validate: domain contains `.` and has no spaces (return error dict if invalid)
- Return: `domain`, `malicious_detections`, `harmless_detections`, `registrar`, `creation_date`, `status`
- Apply all safety patterns from Block 4

**Validation function hint:**
```python
def validate_domain(domain: str) -> bool:
    return "." in domain and " " not in domain and len(domain) > 3
```

---

## Exercise 3 — Write a Hash Enrichment Tool

**API:** MalwareBazaar Hash Lookup (no API key required for basic queries)

```python
# MalwareBazaar API:
# URL: https://mb-api.abuse.ch/api/v1/
# Method: POST
# Body (JSON): {"query": "get_info", "hash": "YOUR_HASH"}

import requests

response = requests.post(
    "https://mb-api.abuse.ch/api/v1/",
    json={"query": "get_info", "hash": hash_value},
    timeout=10
)
data = response.json()

# Response structure:
# data["query_status"] == "hash_not_found" means clean (or unknown)
# data["query_status"] == "ok" means hash was found in the database
# data["data"][0]["file_name"] — original file name
# data["data"][0]["tags"] — list of threat tags
# data["data"][0]["signature"] — malware family name
```

**Requirements:**
- Function name: `lookup_hash(hash_value: str) -> dict`
- Validate hash: length must be 32, 40, or 64 characters; all hex characters
- Return: `hash`, `found` (True/False), `file_name`, `tags`, `signature`, `status`
- If `query_status == "hash_not_found"`: return `found: False, signature: "not in database"`

---

## Exercise 4 — Combine Into a Mini Triage Script

Using your three functions (`enrich_ip`, `enrich_domain`, `lookup_hash`), write a triage script that:

1. Takes this alert text:
```python
ALERT = """
Alert ID: ALT-0099
Phishing email detected.
Sender: attacker@malware-drop.ru
Attachment hash: 3395856ce81f2b7382dee72602f798b642f14d8
IP in header: 185.220.101.45
"""
```

2. Manually extracts the domain, hash, and IP (no regex needed — just hardcode the values from the alert for now).
3. Calls all three enrichment functions.
4. Prints a triage summary in this format:

```
TRIAGE SUMMARY — ALT-0099
IP 185.220.101.45: score=98, country=NL → HIGH RISK
Domain malware-drop.ru: X detections → HIGH RISK
Hash 3395856c...: found=True, family=Mirai → MALWARE CONFIRMED

VERDICT: HIGH — recommend quarantine and domain block
CYNEFIN: Clear (multiple corroborating indicators)
ACTION REQUIRED: Analyst approval to quarantine and block
```

---

## Checklist

- [ ] Exercise 1: all blanks filled, tool runs without errors
- [ ] Exercise 1: `enrich_ip("not-an-ip")` returns `{"status": "error", ...}` not a crash
- [ ] Exercise 2: `enrich_domain` complete with all safety patterns
- [ ] Exercise 3: `lookup_hash` handles both "found" and "not found" cases
- [ ] Exercise 4: mini triage script prints all three enrichments and a verdict
- [ ] All three tools return `dict` (not string or None)
- [ ] All three tools have type hints and docstrings
