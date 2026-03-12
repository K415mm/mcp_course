---
status: draft
---

# Module 4: Python Essentials for MCP and Agentic AI

## Module Goal

Build just enough Python fluency to write clean, agent-friendly MCP tools — focusing on the patterns that matter most for cyber defense automation.

## Learning Objectives

1. Write Python functions with type hints that generate correct MCP tool schemas.
2. Use docstrings as the AI's primary guide to tool selection.
3. Handle errors gracefully so broken tools never crash an AI workflow.
4. Read and manipulate JSON data — the lingua franca of MCP tool outputs.
5. Call external APIs and validate inputs safely.

---

## Theoretical Section

### 4.1 Why Python for MCP?

MCP is language-agnostic — you can build a server in TypeScript, Go, or Bash. But Python is the dominant language of both cybersecurity and AI for good reasons:

- Most open-source security tools (Scapy, Impacket, Volatility, YARA) are Python.
- Most AI tooling (Hugging Face, LangChain, LlamaIndex) is Python.
- Python is readable enough for security analysts who are not full-time developers.

Learning the subset of Python needed for MCP tools is achievable in hours, not weeks.

---

### 4.2 Functions, Type Hints, and Return Types

The most important Python construct for MCP is the **typed function**:

```python
def lookup_ip(ip_address: str, max_age_days: int = 90) -> dict:
    """Look up IP threat intelligence."""
    ...
```

- `ip_address: str` — type hint declaring input type (FastMCP auto-generates the schema).
- `max_age_days: int = 90` — optional parameter with default value.
- `-> dict` — return type hint (prefer `dict` for structured MCP output).

**Python types you'll use most in MCP tools:**

| Type | Use case |
|---|---|
| `str` | IP addresses, domains, hashes, file paths |
| `int` | Thresholds, counts, port numbers |
| `float` | Confidence scores, ratios |
| `bool` | Flags (verbose, recursive, force) |
| `dict` | Structured return values (always preferred) |
| `list` | Multiple results (IOC lists, event lists) |

---

### 4.3 Docstrings — The AI's Decision Engine

The docstring is not documentation for humans in MCP — it is **the instruction set the LLM reads to decide when and how to call your tool**. Write it for the AI, not for a colleague:

**Bad docstring (too vague):**
```python
def check(ip: str) -> dict:
    """Check an IP."""
```

**Good docstring (AI-oriented):**
```python
def check_ip_reputation(ip_address: str) -> dict:
    """Check an IPv4 address for known malicious activity using threat intelligence.
    Use this tool when an analyst provides an IP address from an alert or log.
    Returns abuse confidence score (0–100), country, ISP, and report count.
    Read-only. Safe to call automatically."""
```

The LLM will read both the function name and the docstring when choosing tools. Be explicit about:
- When to use the tool.
- What the inputs and outputs mean.
- Whether it is safe to automate (read-only) or requires approval (destructive).

---

### 4.4 Working with JSON and Dictionaries

MCP tool outputs should always be dictionaries (Python `dict`), which serialize cleanly to JSON:

```python
# Good — structured, parseable by next tool in chain
return {
    "ip": "185.220.101.45",
    "abuse_score": 98,
    "country": "NL",
    "status": "ok"
}

# Bad — raw string, hard for the LLM to parse reliably
return "IP 185.220.101.45 has abuse score 98 in Netherlands"
```

**Reading nested JSON from an API response:**

```python
import requests

response = requests.get("https://api.example.com/ip/185.220.101.45").json()

# Safe nested access with .get() — never use ['key'] on API data
score = response.get("data", {}).get("abuseConfidenceScore", 0)
country = response.get("data", {}).get("countryCode", "unknown")
```

Using `.get()` with a default prevents `KeyError` when an API returns unexpected structure.

---

### 4.5 Error Handling

In an AI workflow, a crashing tool breaks the entire agent loop. Always return a structured error dict instead of letting exceptions propagate:

```python
@mcp.tool()
def read_pcap_summary(pcap_path: str) -> dict:
    """Parse a pcap file and return a summary of hosts, protocols, and packet count."""
    try:
        # ... analysis logic here
        return {"hosts": [...], "protocols": [...], "status": "ok"}
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {pcap_path}"}
    except PermissionError:
        return {"status": "error", "reason": "Insufficient permissions to read file"}
    except Exception as e:
        return {"status": "error", "reason": f"Unexpected error: {str(e)}"}
```

**Rule:** every MCP tool should return `{"status": "ok", ...}` on success and `{"status": "error", "reason": "..."}` on any failure.

---

### 4.6 Input Validation

Never trust the LLM's inputs to a security tool. Validate before acting:

```python
import re

def is_valid_ip(ip: str) -> bool:
    """Basic IPv4 validation."""
    pattern = r"^(\d{1,3}\.){3}\d{1,3}$"
    if not re.match(pattern, ip):
        return False
    return all(0 <= int(part) <= 255 for part in ip.split("."))

@mcp.tool()
def enrich_ip(ip_address: str) -> dict:
    """Enrich an IPv4 address with threat intelligence data. Read-only."""
    if not is_valid_ip(ip_address):
        return {"status": "error", "reason": f"Invalid IPv4 address: {ip_address}"}
    # proceed with enrichment...
```

**Common validations for cyber tools:**
- IP address format.
- Hash length (MD5=32, SHA1=40, SHA256=64 hex chars).
- File path exists and is accessible before reading.
- Domain format (no spaces, valid TLD structure).

---

### 4.7 Environment Variables for Secrets

Never hard-code API keys in tool code. Load from environment:

```python
import os

VT_KEY = os.environ.get("VT_API_KEY")
if not VT_KEY:
    raise EnvironmentError("VT_API_KEY environment variable is not set")
```

In production MCP server config (e.g., Trae `mcp.json`):

```json
{
  "command": "python",
  "args": ["cti_server.py"],
  "env": {
    "VT_API_KEY": "${VT_API_KEY}"
  }
}
```

---

## Practical Section

### Exercise 4.1 — Write a Typed Tool Function

Write a Python function called `extract_hashes_from_text` that:
- Takes a `text: str` input and an optional `hash_types: list = ["md5", "sha256"]`.
- Returns a `dict` with keys per hash type, each mapping to a list of found hashes.
- Handles empty text gracefully.
- Includes a docstring that tells an LLM exactly when to call this tool.

### Exercise 4.2 — Fix Unsafe Code

The following tool has three safety problems. Identify and fix them:

```python
def analyze_file(path):
    with open(path) as f:
        data = f.read()
    result = requests.get(f"https://api.vt.com/hash/{hash(data)}").json()
    return result['data']['verdict']
```

Problems to find:
1. No type hints.
2. Missing error handling.
3. Using Python's built-in `hash()` instead of a cryptographic hash.

### Checklist: Agent-Friendly Python Tool

Before registering a tool with `@mcp.tool()`, verify:
- [ ] All parameters have type hints.
- [ ] Return type is `dict` or `list`, not `str`.
- [ ] Docstring says when to use it and whether it is safe to automate.
- [ ] All external calls are wrapped in `try/except`.
- [ ] Inputs are validated before being passed to security tools.
- [ ] Secrets are loaded from environment variables, not hard-coded.
- [ ] Output keys are consistent and predictable (not varying by code path).

---

## Example Section

### Complete Safe Tool: Domain Age Checker

```python
import requests, os, re
from mcp.server.fastmcp import FastMCP
from datetime import datetime

mcp = FastMCP("Domain Intel Server")

def is_valid_domain(domain: str) -> bool:
    pattern = r"^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$"
    return bool(re.match(pattern, domain))

@mcp.tool()
def check_domain_age(domain: str) -> dict:
    """Check how recently a domain was registered using WHOIS data.
    Use this when evaluating a suspicious domain from an alert or email header.
    Newly registered domains (under 30 days) are a strong phishing indicator.
    Read-only. Safe to automate."""
    if not is_valid_domain(domain):
        return {"status": "error", "reason": f"Invalid domain format: {domain}"}
    try:
        import whois
        w = whois.whois(domain)
        creation = w.creation_date
        if isinstance(creation, list):
            creation = creation[0]
        if creation:
            age_days = (datetime.now() - creation).days
            return {"domain": domain, "age_days": age_days, "creation_date": str(creation), "status": "ok"}
        return {"domain": domain, "age_days": None, "status": "ok", "note": "Creation date not available"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}

if __name__ == "__main__":
    mcp.run()
```

---

## Knowledge Check

1. Why should `dict` be preferred over `str` as a return type for MCP tools?
2. What happens to an AI agent workflow if a tool raises an uncaught exception?
3. Where should API keys be stored in an MCP server project?
4. Rewrite this docstring to be more AI-friendly: `"""Checks a hash."""`
5. Write a one-line input validation check for a SHA256 hash string.

---

## Reading List (Module 4 Source Files)

- [FastMCP_ Building AI-Enabled Python Tools.md](file:///d:/mcp_course/FastMCP_%20Building%20AI-Enabled%20Python%20Tools.md)
- [Mastering_Python_MCP.pdf](file:///d:/mcp_course/Mastering_Python_MCP.pdf)
- [9781806106134.pdf](file:///d:/mcp_course/9781806106134.pdf)
