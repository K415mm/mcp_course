---
status: published
---

# 02 — Data Structures and JSON

> **Theoretical Block 2 of 5 | Module 04: Python Essentials for MCP**

---

## 2.1 Why This Matters for MCP

MCP tools communicate using **JSON** — JavaScript Object Notation. Every tool call sends JSON arguments to your function. Every tool result should return something that converts cleanly to JSON.

In Python, JSON data maps directly to two structures:
- **Dictionaries** (`dict`) → JSON objects `{ "key": "value" }`
- **Lists** (`list`) → JSON arrays `[ "item1", "item2" ]`

Master these two structures and you can handle any data that flows through an MCP server.

---

## 2.2 Dictionaries — Python's Most Important Data Structure for MCP

A **dictionary** stores key-value pairs. Think of it as a structured form with labeled fields.

```python
# An IP enrichment result as a dictionary
ip_report = {
    "ip": "185.220.101.45",
    "abuse_score": 98,
    "country": "NL",
    "isp": "Frantech Solutions",
    "is_tor": True,
    "total_reports": 412,
    "status": "ok"
}
```

### Accessing Dictionary Values

```python
# Get the value for a key using []
score = ip_report["abuse_score"]        # 98

# Safer: use .get() with a default — won't crash if key is missing
score = ip_report.get("abuse_score", 0) # 98 if exists, 0 if not
isp   = ip_report.get("isp", "unknown") # "Frantech Solutions"
```

> **Rule for MCP tools:** Always use `.get()` when reading from API responses. API data can be missing fields — `.get()` prevents crashes.

### Modifying Dictionaries

```python
# Add or update a key
ip_report["risk_level"] = "HIGH"

# Remove a key
del ip_report["is_tor"]  # removes the is_tor field
```

### Checking if a Key Exists

```python
if "abuse_score" in ip_report:
    print(ip_report["abuse_score"])
```

---

## 2.3 Dictionaries as MCP Tool Return Values

Every MCP tool should return a `dict`. Here's why:

**❌ Bad — returns a plain string:**
```python
def enrich_ip(ip_address: str) -> str:
    return "IP 185.220.101.45 has abuse score 98 in Netherlands"
```

The AI has to parse this. It might fail. Downstream tools can't use the data reliably.

**✅ Good — returns a structured dict:**
```python
def enrich_ip(ip_address: str) -> dict:
    return {
        "ip": ip_address,
        "abuse_score": 98,
        "country": "NL",
        "isp": "Frantech Solutions",
        "status": "ok"
    }
```

The AI can access each field by name. Downstream tools can read `result["abuse_score"]` reliably.

**Standard MCP tool result pattern:**
```python
# On success
return {
    "status": "ok",
    "field1": value1,
    "field2": value2
}

# On failure
return {
    "status": "error",
    "reason": "What went wrong and why"
}
```

Always include `"status"` — it's how the agent knows whether the tool succeeded.

---

## 2.4 Lists — Ordered Collections

A **list** stores multiple items in order.

```python
ioc_list = ["185.220.101.45", "evil-domain.net", "a3f9d1b2..."]

malware_families = ["FormBook", "AgentTesla", "RedLine"]

event_ids = [1001, 1002, 1003, 1004]
```

### Accessing Items

```python
first_ioc = ioc_list[0]   # "185.220.101.45" (index starts at 0)
last_ioc  = ioc_list[-1]  # "a3f9d1b2..." (negative index = from end)
```

### Adding and Removing Items

```python
ioc_list.append("new-domain.ru")     # Add to end
ioc_list.remove("evil-domain.net")   # Remove by value
```

### Looping Through a List

```python
for ioc in ioc_list:
    print(f"Processing: {ioc}")
```

This is how you process a batch of IOCs — loop through the list, call an enrichment tool on each one.

---

## 2.5 Lists Inside Dictionaries (Nested Structures)

Real threat intelligence data combines both. A domain enrichment result might look like:

```python
domain_report = {
    "domain": "evil-phish.net",
    "age_days": 3,
    "malicious_votes": 18,
    "harmless_votes": 4,
    "known_tags": ["phishing", "newly-registered"],    # list inside dict
    "vendor_names": ["Fortinet", "Kaspersky", "G-Data"],
    "status": "ok"
}
```

Accessing nested data:

```python
first_tag   = domain_report["known_tags"][0]       # "phishing"
vote_count  = domain_report["malicious_votes"]      # 18
vendor_list = domain_report.get("vendor_names", []) # safe access
```

---

## 2.6 Reading Real JSON from an API

When you call an external API (like VirusTotal or AbuseIPDB), it returns JSON. Python's `requests` library (covered in Block 5) makes this simple:

```python
import requests

response = requests.get("https://api.abuseipdb.com/api/v2/check", 
                         headers={"Key": "YOUR_KEY"},
                         params={"ipAddress": "185.220.101.45"})

# Convert the response to a Python dict
data = response.json()

# Navigate the nested structure using .get() for safety
ip_data = data.get("data", {})
score   = ip_data.get("abuseConfidenceScore", 0)
country = ip_data.get("countryCode", "unknown")
isp     = ip_data.get("isp", "unknown")
```

**Always use `.get()` on API data.** APIs can change their response format, return partial data, or omit fields in error conditions. `.get()` with a default value ensures your tool never crashes on unexpected API responses.

---

## 2.7 Building a Result Dict Step by Step

This is the most practical pattern you'll use in every MCP tool:

```python
def enrich_domain(domain: str) -> dict:
    """Enrich a domain with VirusTotal detection data."""
    
    # Step 1: Make the API call
    response = requests.get(
        f"https://www.virustotal.com/api/v3/domains/{domain}",
        headers={"x-apikey": "YOUR_KEY"},
        timeout=10
    )
    
    # Step 2: Parse the response into a dict
    raw = response.json()
    
    # Step 3: Navigate to the data you need using .get()
    attrs = raw.get("data", {}).get("attributes", {})
    stats = attrs.get("last_analysis_stats", {})
    
    # Step 4: Build your clean result dict
    return {
        "domain": domain,
        "malicious_detections": stats.get("malicious", 0),
        "harmless_detections":  stats.get("harmless", 0),
        "total_scans":          sum(stats.values()) if stats else 0,
        "registrar":            attrs.get("registrar", "unknown"),
        "creation_date":        attrs.get("creation_date", "unknown"),
        "status": "ok"
    }
```

---

## 2.8 Common Dict Patterns in MCP Tools

### Filtering a List Based on a Condition

```python
events = [
    {"host": "WIN-01", "port": 443, "bytes": 4200},
    {"host": "WIN-02", "port": 9001, "bytes": 1200},
    {"host": "WIN-03", "port": 443, "bytes": 8900},
]

SUSPICIOUS_PORTS = {9001, 4444, 1337}

# List comprehension — creates a new list with only matching items
suspicious = [e for e in events if e["port"] in SUSPICIOUS_PORTS]
# Result: [{"host": "WIN-02", "port": 9001, "bytes": 1200}]
```

### Extracting Unique Values

```python
hosts = ["WIN-01", "WIN-02", "WIN-01", "WIN-03", "WIN-02"]

unique_hosts = list(set(hosts))  # ["WIN-01", "WIN-02", "WIN-03"]
```

### Counting Occurrences

```python
tags = ["phishing", "malware", "phishing", "c2", "phishing"]

count = {}
for tag in tags:
    count[tag] = count.get(tag, 0) + 1

# count = {"phishing": 3, "malware": 1, "c2": 1}
```

---

## Key Takeaways

1. Dictionaries are the core data structure for MCP — every tool output should be a `dict`.
2. Always use `.get()` with a default when reading API responses — never assume a key exists.
3. Every MCP tool result dict must include `"status": "ok"` or `"status": "error"`.
4. Lists store ordered collections — loop through them to process multiple IOCs.
5. Nested structures (dict inside dict, list inside dict) are common in API responses — navigate with chained `.get()` calls.
6. List comprehensions `[x for x in items if condition]` are the cleanest way to filter data.

---

## Try It Yourself

```python
# Exercise: Build an IOC report dict from raw data

# Given:
ip = "185.220.101.45"
raw_api_response = {
    "data": {
        "abuseConfidenceScore": 98,
        "countryCode": "NL",
        "isp": "Frantech Solutions",
        "totalReports": 412,
        "usageType": "Data Center/Web Hosting"
    }
}

# Task: Write a function that extracts the data from raw_api_response
# and returns a clean dict in MCP tool format (with "status": "ok")

def parse_ip_report(ip_address: str, raw_response: dict) -> dict:
    # Your code here
    pass


# Test it:
result = parse_ip_report(ip, raw_api_response)
print(result["abuse_score"])   # Should print: 98
print(result["status"])        # Should print: ok
```
