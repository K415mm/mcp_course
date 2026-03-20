---
status: published
---

# Practical 02 — JSON and Data Exercises

> **Practical Block 2 of 3 | Module 04: Python Essentials for MCP**

---

## Exercise Goal

Practice reading, navigating, and transforming the JSON data structures you will encounter in real MCP threat intelligence tools. All exercises use realistic API response shapes.

---

## Exercise 2.1 — Navigate a Nested API Response

Given this realistic AbuseIPDB API response:

```python
raw_response = {
    "data": {
        "ipAddress": "185.220.101.45",
        "isPublic": True,
        "ipVersion": 4,
        "isWhitelisted": False,
        "abuseConfidenceScore": 98,
        "countryCode": "NL",
        "usageType": "Data Center/Web Hosting",
        "isp": "Frantech Solutions",
        "domain": "frantech.ca",
        "hostnames": [],
        "isTor": True,
        "totalReports": 412,
        "numDistinctUsers": 181,
        "lastReportedAt": "2026-03-09T23:00:00+00:00",
        "reports": []
    }
}
```

Write Python expressions to extract:

```python
# 1. The abuse confidence score
score = ___

# 2. The country code
country = ___

# 3. Whether this is a Tor exit node (True/False)
is_tor = ___

# 4. The number of distinct users who reported it
distinct_users = ___

# 5. The last reported date
last_seen = ___

# 6. Safe: use .get() with default for a field that doesn't exist
reputation_tag = raw_response.get("data", {}).get("reputationTag", "unknown")
```

---

## Exercise 2.2 — Build a Clean Output Dict

Using the raw response from 2.1, write the `parse_ip_report()` function:

```python
def parse_ip_report(ip_address: str, raw_response: dict) -> dict:
    """
    Parse an AbuseIPDB raw API response into a clean MCP tool result.
    Use .get() with defaults on all fields.
    Must return: ip, abuse_score, country, isp, is_tor, total_reports, risk_level, status
    risk_level: 'HIGH' if score >= 80, 'MEDIUM' if >= 40, else 'LOW'
    """
    data = raw_response.get("data", {})
    
    score = data.get("abuseConfidenceScore", 0)
    
    # Determine risk level
    if score >= 80:
        risk_level = ___
    elif score >= 40:
        risk_level = ___
    else:
        risk_level = ___
    
    return {
        "ip":           ___,
        "abuse_score":  ___,
        "country":      ___,
        "isp":          ___,
        "is_tor":       ___,
        "total_reports":___,
        "risk_level":   risk_level,
        "status":       "ok"
    }

# Test it
result = parse_ip_report("185.220.101.45", raw_response)
print(result["risk_level"])      # Should print: HIGH
print(result["is_tor"])          # Should print: True
print(result["total_reports"])   # Should print: 412
```

---

## Exercise 2.3 — Process a Batch of Enrichment Results

Given this list of enrichment results from multiple IP lookups:

```python
ip_results = [
    {"ip": "185.220.101.45", "abuse_score": 98, "country": "NL", "is_tor": True},
    {"ip": "8.8.8.8",        "abuse_score": 0,  "country": "US", "is_tor": False},
    {"ip": "91.108.4.46",    "abuse_score": 12, "country": "NL", "is_tor": False},
    {"ip": "103.21.244.0",   "abuse_score": 85, "country": "HK", "is_tor": False},
    {"ip": "204.79.197.200", "abuse_score": 3,  "country": "US", "is_tor": False},
]
```

Write the code to:

```python
# Task A: Filter — get only IPs with abuse_score >= 80
high_risk = ___   # Expected: 2 IPs (185.220.101.45 and 103.21.244.0)

# Task B: Sort — order all results from highest to lowest abuse_score
sorted_results = sorted(ip_results, key=___, reverse=___)

# Task C: Count — how many are Tor exit nodes?
tor_count = ___   # Expected: 1

# Task D: Extract — get just the list of high-risk IP addresses (not full dicts)
high_risk_ips = [r["ip"] for r in ip_results if r["abuse_score"] >= ___]
# Expected: ["185.220.101.45", "103.21.244.0"]

# Task E: Summarize — build a summary dict
summary = {
    "total_checked": ___,
    "high_risk_count": ___,
    "tor_exit_nodes": ___,
    "highest_score_ip": sorted_results[0]["ip"],
}
```

---

## Exercise 2.4 — Output Normalization

Apply the course's output normalization rule to these raw strings from a malware tool:

```python
RAW_OUTPUT = {
    "verdict": "suspicious file — dangerous API usage detected",
    "tags": ["malicious", "suspicious", "backdoor capability", "infected"],
    "analyst_note": "This file is dangerous and likely a trojan dropper"
}

NORMALIZE = {
    "suspicious": "notable",
    "malicious": "flagged by vendor",
    "dangerous": "commonly scrutinized",
    "infected": "flagged",
    "backdoor": "remote access capability",
    "trojan": "file classified as",
}

def normalize_text(text: str, table: dict) -> str:
    """Replace all flagged terms in text with their normalized equivalents."""
    for original, replacement in table.items():
        text = text.replace(original, replacement)
    return text


# Apply normalization to all string fields in RAW_OUTPUT
normalized = {
    "verdict":      normalize_text(RAW_OUTPUT["verdict"], NORMALIZE),
    "tags":         [normalize_text(tag, NORMALIZE) for tag in RAW_OUTPUT["tags"]],
    "analyst_note": normalize_text(RAW_OUTPUT["analyst_note"], NORMALIZE),
}

print(normalized["verdict"])
# Expected: "notable file — commonly scrutinized API usage detected"
print(normalized["tags"])
# Expected: ['flagged by vendor', 'notable', 'remote access capability', 'flagged']
```

Run this code and verify the output matches expectations. Then add two more terms to the NORMALIZE dict and test again.

---

## Exercise 2.5 — Building the Triage Summary Dict

Write a function that takes a list of enriched IP results and produces a triage summary dict ready to be returned as an MCP tool result:

```python
def build_triage_summary(enriched_ips: list) -> dict:
    """
    Given a list of IP enrichment result dicts, produce a triage summary.
    Returns: total, high_risk_ips, tor_exits, risk_distribution, verdict, status
    """
    high_risk = [r for r in enriched_ips if r.get("abuse_score", 0) >= 80]
    tor_exits = [r for r in enriched_ips if r.get("is_tor", False)]
    
    risk_distribution = {"HIGH": 0, "MEDIUM": 0, "LOW": 0}
    for r in enriched_ips:
        s = r.get("abuse_score", 0)
        if s >= 80:
            risk_distribution["HIGH"] += 1
        elif s >= 40:
            risk_distribution["MEDIUM"] += 1
        else:
            risk_distribution["LOW"] += 1
    
    if len(high_risk) >= 2 or (len(high_risk) == 1 and len(tor_exits) >= 1):
        verdict = "HIGH — multiple corroborating high-risk indicators"
    elif len(high_risk) == 1:
        verdict = "MEDIUM — one high-risk indicator, further investigation required"
    else:
        verdict = "LOW — no high-risk indicators found"
    
    return {
        "total_ips_checked": len(enriched_ips),
        "high_risk_count":   len(high_risk),
        "high_risk_ips":     [r["ip"] for r in high_risk],
        "tor_exit_count":    len(tor_exits),
        "risk_distribution": risk_distribution,
        "verdict":           verdict,
        "status":            "ok"
    }


# Test with the ip_results from Exercise 2.3
summary = build_triage_summary(ip_results)
print(summary)
```

---

## Checklist

- [ ] Exercise 2.1: all 5 extractions correct
- [ ] Exercise 2.2: `parse_ip_report` returns correct `risk_level` for all three thresholds
- [ ] Exercise 2.3: all 5 tasks produce correct output
- [ ] Exercise 2.4: normalized output has no original flagged terms remaining
- [ ] Exercise 2.5: `build_triage_summary` returns correct verdict for 2 high-risk IPs
