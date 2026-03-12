---
status: draft
---

# Module 5: Build MCP Servers with Python

## Module Goal

Build real, working MCP servers in Python using FastMCP, write tools that accept inputs and return structured outputs, and apply safe design principles throughout.

## Learning Objectives

1. Explain what FastMCP is and why it replaces manual JSON-RPC code.
2. Build a working MCP server with multiple tools using `@mcp.tool()`.
3. Use type hints and docstrings to generate correct tool schemas automatically.
4. Design tools that follow safe-output and least-privilege principles.
5. Run and test an MCP server locally against an AI client.

---

## Theoretical Section

### 5.1 Why FastMCP?

Building an MCP server from scratch requires hundreds of lines just to handle the communication layer:
- Parse and validate JSON-RPC 2.0 messages.
- Negotiate protocol versions between client and server.
- Dynamically generate JSON schema for every tool input.
- Handle request/response errors and retries.

**FastMCP** (included in the official MCP Python SDK) handles all of this automatically. You write normal Python functions; FastMCP translates them into the universal MCP language that any AI client understands.

---

### 5.2 Three Things FastMCP Reads Automatically

When you decorate a function with `@mcp.tool()`, FastMCP inspects three things:

#### 1. Type Hints → Input Schema

```python
def check_ip(ip_address: str, verbose: bool = False) -> str:
```

FastMCP generates a JSON schema requiring `ip_address` as a string and making `verbose` an optional boolean. The AI cannot call this tool without providing a valid string — the schema enforces the contract.

#### 2. Docstrings → Tool Description

```python
def check_ip(ip_address: str) -> str:
    """Check an IP address against threat intelligence feeds.
    Returns a risk summary with confidence level."""
```

The docstring is extracted verbatim and sent to the LLM as the tool's official description. When the analyst asks "is this IP malicious?", the LLM reads the description, recognizes this is the right tool, and calls it.

#### 3. Return Type → Output Contract

A `-> str` return type tells FastMCP the tool returns text. Use `-> dict` for structured JSON, which is preferred for cyber tools because it lets downstream tools parse the output without string splitting.

---

### 5.3 Tool Design Principles for Cyber Defense

#### One Tool, One Action
Each tool should do exactly one thing. Instead of a single `analyze_alert` tool, build:
- `enrich_ip(ip_address)` — reputation and geolocation
- `lookup_hash(file_hash)` — malware family match
- `get_whois(domain)` — registrar and age

This makes each tool testable, auditable, and reusable across different workflows.

#### Read-Only vs. Destructive Tools

| Type | Examples | Approval Gate |
|---|---|---|
| Read-only | enrich, lookup, search, parse | None — safe to automate |
| Destructive | block, isolate, delete, patch | Required — human must confirm |

Mark destructive tools clearly in their docstrings:

```python
def block_ip(ip_address: str) -> str:
    """[DESTRUCTIVE] Block an IP address on the perimeter firewall.
    REQUIRES HUMAN APPROVAL before execution."""
```

#### Output Normalization

Never return raw tool output to the LLM:
- Replace `"suspicious"` with `"notable"`
- Replace `"malicious"` with `"flagged by vendor"`
- Replace `"dangerous API"` with `"API commonly observed in malware"`

This prevents the LLM from hallucinating severity based on emotionally loaded language.

#### Safe Defaults

- If the input fails validation, return a structured error, not an exception.
- If an external API is unreachable, return `{"status": "unavailable", "reason": "..."}` — never `None`.
- Log every tool call: timestamp, input, output, duration.

---

## Practical Section

### 5.4 Hands-On: Your First MCP Server

#### Step 1 — Install the SDK

```bash
pip install mcp fastmcp
```

#### Step 2 — Initialize the Server

```python
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("Cyber Defense Server")
```

#### Step 3 — Add a Read-Only Tool

```python
import hashlib

@mcp.tool()
def hash_file(file_path: str) -> dict:
    """Compute MD5, SHA1, and SHA256 hashes for a file.
    Use this before any malware analysis to establish file identity."""
    try:
        with open(file_path, "rb") as f:
            data = f.read()
        return {
            "md5":    hashlib.md5(data).hexdigest(),
            "sha1":   hashlib.sha1(data).hexdigest(),
            "sha256": hashlib.sha256(data).hexdigest(),
            "size_bytes": len(data),
            "status": "ok"
        }
    except FileNotFoundError:
        return {"status": "error", "reason": f"File not found: {file_path}"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

#### Step 4 — Add an Enrichment Tool

```python
import requests

@mcp.tool()
def check_ip_reputation(ip_address: str) -> dict:
    """Check an IPv4 address against AbuseIPDB for threat intelligence.
    Returns abuse confidence score, country, and usage type.
    Read-only — safe to automate."""
    api_key = "YOUR_ABUSEIPDB_KEY"  # load from env var in production
    url = "https://api.abuseipdb.com/api/v2/check"
    headers = {"Key": api_key, "Accept": "application/json"}
    params = {"ipAddress": ip_address, "maxAgeInDays": 90}
    try:
        resp = requests.get(url, headers=headers, params=params, timeout=10)
        data = resp.json().get("data", {})
        return {
            "ip": ip_address,
            "abuse_confidence": data.get("abuseConfidenceScore", 0),
            "country": data.get("countryCode", "unknown"),
            "usage_type": data.get("usageType", "unknown"),
            "total_reports": data.get("totalReports", 0),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

#### Step 5 — Add a Destructive Tool (with approval marker)

```python
@mcp.tool()
def quarantine_file(file_path: str, reason: str) -> dict:
    """[DESTRUCTIVE] Move a suspicious file to an isolated quarantine directory.
    REQUIRES HUMAN APPROVAL. Provide the reason for quarantine.
    This action cannot be undone automatically."""
    import shutil, os
    quarantine_dir = "/var/quarantine"
    os.makedirs(quarantine_dir, exist_ok=True)
    try:
        dest = os.path.join(quarantine_dir, os.path.basename(file_path))
        shutil.move(file_path, dest)
        return {
            "status": "quarantined",
            "original_path": file_path,
            "quarantine_path": dest,
            "reason": reason
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}
```

#### Step 6 — Start the Server

```python
if __name__ == "__main__":
    mcp.run()
```

Run it:

```bash
python cyber_defense_server.py
```

The server now listens on stdio, ready for any MCP client to connect, discover tools, and call them.

---

### 5.5 Hands-On: CTI Enrichment Server (Full Example)

Build a complete server that enriches an IOC (IP, domain, or hash) by chaining multiple tools:

```python
from mcp.server.fastmcp import FastMCP
import requests, os

mcp = FastMCP("CTI Enrichment Server")

VIRUSTOTAL_KEY = os.environ.get("VT_API_KEY", "")

@mcp.tool()
def enrich_domain(domain: str) -> dict:
    """Enrich a domain with WHOIS registration data and VirusTotal verdict.
    Read-only. Returns age, registrar, and malicious vote count."""
    vt_url = f"https://www.virustotal.com/api/v3/domains/{domain}"
    headers = {"x-apikey": VIRUSTOTAL_KEY}
    try:
        resp = requests.get(vt_url, headers=headers, timeout=10)
        attrs = resp.json().get("data", {}).get("attributes", {})
        stats = attrs.get("last_analysis_stats", {})
        return {
            "domain": domain,
            "creation_date": attrs.get("creation_date", "unknown"),
            "registrar": attrs.get("registrar", "unknown"),
            "malicious_votes": stats.get("malicious", 0),
            "harmless_votes": stats.get("harmless", 0),
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def enrich_hash(file_hash: str) -> dict:
    """Look up a file hash (MD5, SHA1, or SHA256) on VirusTotal.
    Read-only. Returns detection count and known malware family names."""
    vt_url = f"https://www.virustotal.com/api/v3/files/{file_hash}"
    headers = {"x-apikey": VIRUSTOTAL_KEY}
    try:
        resp = requests.get(vt_url, headers=headers, timeout=10)
        attrs = resp.json().get("data", {}).get("attributes", {})
        stats = attrs.get("last_analysis_stats", {})
        names = list(set(
            r.get("result") for r in attrs.get("last_analysis_results", {}).values()
            if r.get("result")
        ))[:5]  # top 5 family names
        return {
            "hash": file_hash,
            "malicious_detections": stats.get("malicious", 0),
            "total_scans": sum(stats.values()),
            "known_family_names": names,
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

**Test it with the MCP CLI inspector:**

```bash
npx @modelcontextprotocol/inspector python cti_server.py
```

This opens a browser UI where you can trigger each tool manually and inspect the JSON-RPC messages — essential for debugging before connecting a real AI client.

---

## Example Section

### Walk-Through: Hash Lookup in a Triage Workflow

**Analyst prompt to AI:** "I have a suspicious file at `/tmp/update_patch.exe`. Is it known malware?"

**AI agent (MCP client) calls:**

1. `hash_file("/tmp/update_patch.exe")` →
   ```json
   { "sha256": "a3f9d1...", "sha1": "b2c...", "md5": "9e1...", "status": "ok" }
   ```

2. `enrich_hash("a3f9d1...")` →
   ```json
   { "malicious_detections": 47, "total_scans": 68, "known_family_names": ["AgentTesla", "FormBook"], "status": "ok" }
   ```

**AI response to analyst:**
> "The file has a SHA256 of `a3f9d1...`. It was flagged by 47 of 68 vendors. Known family names include AgentTesla and FormBook. I recommend isolating the host. Do you approve quarantine of the file?"

The analyst approves → `quarantine_file("/tmp/update_patch.exe", "Known AgentTesla sample, 47/68 detections")` called.

---

## Knowledge Check

1. What two things does FastMCP read from a Python function to generate a tool schema?
2. Why should destructive MCP tools always include an approval gate?
3. What is output normalization and why is it critical for LLM-facing security tools?
4. What command can you use to test an MCP server without connecting a real AI client?
5. If `check_ip_reputation` cannot reach the API, what should it return instead of raising an exception?

---

## Reading List (Module 5 Source Files)

- [FastMCP_ Building AI-Enabled Python Tools.md](file:///d:/mcp_course/FastMCP_%20Building%20AI-Enabled%20Python%20Tools.md)
- [MCP_Cyber_Defense.pdf](file:///d:/mcp_course/MCP_Cyber_Defense.pdf)
- [Mastering_Python_MCP.pdf](file:///d:/mcp_course/Mastering_Python_MCP.pdf)
- [9781806106134.pdf](file:///d:/mcp_course/9781806106134.pdf)
