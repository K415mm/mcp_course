---
status: published
---

# 03 — Type Hints and Docstrings

> **Theoretical Block 3 of 5 | Module 04: Python Essentials for MCP**

---

## 3.1 Why Type Hints and Docstrings Are Non-Negotiable in MCP

When you write a regular Python script, type hints and docstrings are best practices — nice to have.

When you write an **MCP tool**, they are **mandatory**. Here's why:

**FastMCP reads your type hints and docstring automatically** to build the JSON schema that tells the AI agent:
- What your tool does (docstring → description in the schema)
- What inputs it accepts (type hints → parameter types in the schema)
- What format each input should be in (type annotations → JSON schema types)

Without proper type hints and docstrings:
- The AI agent gets an incomplete schema → it may pass wrong arguments
- The AI agent can't understand when to use the tool → it may skip it entirely
- FastMCP may fail to register the tool correctly

> **Rule:** A complete MCP tool = function + type hints on all parameters + return type + docstring. No exceptions.

---

## 3.2 Type Hints: The Complete Guide for MCP

### Basic Types

```python
def my_tool(
    ip_address: str,        # Text: IP, domain, hash, path, message
    score: int,             # Whole number: port, count, threshold
    confidence: float,      # Decimal: percentages, ratios
    is_blocked: bool,       # True or False
) -> dict:                  # Always return a dict for MCP tools
    ...
```

### Optional Parameters

```python
from typing import Optional

def enrich_ip(
    ip_address: str,
    max_reports: int = 100,         # Default value — optional to caller
    include_details: bool = False,  # Default False — caller can set True
    notes: Optional[str] = None,    # Can be a string OR None (not provided)
) -> dict:
    ...
```

`Optional[str]` means: "this can be a `str` OR `None`." Use it when a parameter truly doesn't need to be provided.

### List and Dict Parameters

```python
from typing import List, Dict

def process_ioc_batch(
    ioc_list: List[str],            # A list of strings
    config: Dict[str, str] = {},    # A dict with string keys and string values
) -> dict:
    ...
```

### What FastMCP Generates From Your Type Hints

```python
# Your function
def enrich_ip(ip_address: str, include_reports: bool = False) -> dict:
    ...
```

FastMCP auto-generates this JSON schema (shown for reference):
```json
{
  "name": "enrich_ip",
  "description": "[your docstring here]",
  "inputSchema": {
    "type": "object",
    "properties": {
      "ip_address": {"type": "string"},
      "include_reports": {"type": "boolean", "default": false}
    },
    "required": ["ip_address"]
  }
}
```

Note: `include_reports` is NOT in `required` because it has a default value. FastMCP figures this out from your type hints automatically.

---

## 3.3 Docstrings: The Three-Part Structure

A docstring is the text between `"""` triple quotes immediately after your function definition. For MCP tools, every docstring should answer three questions:

**What does this tool do?** (First sentence — the most important)
**When should the agent call this tool?** (Second sentence — tells the LLM when to use it)
**Safety classification** (Last line — read-only or destructive)

```python
def enrich_ip(ip_address: str) -> dict:
    """Retrieve threat intelligence data for an IPv4 address from AbuseIPDB.
    Use when you find an IP address in an alert or log event and need to assess its risk level.
    Returns abuse confidence score (0-100), country, ISP, and Tor exit node status.
    Read-only. Safe to automate."""
    ...
```

Breaking it down:

| Part | Content | Why It Matters |
|---|---|---|
| Line 1 ("What") | Retrieve threat intelligence data for an IPv4 | LLM knows what the tool produces |
| Line 2 ("When") | Use when you find an IP in an alert | LLM knows when to call this tool |
| Line 3 ("What it returns") | Returns abuse score, country, ISP, Tor status | LLM can reference expected output fields |
| Line 4 ("Safety") | Read-only. Safe to automate | Tells LLM no approval needed |

---

## 3.4 Docstring Examples: Good vs. Bad

### ❌ Too Vague — Agent Will Under-Use This Tool

```python
def check_ip(ip: str) -> dict:
    """Check an IP."""
    ...
```

Problems:
- "Check" means nothing — check for what?
- No guidance on when to use it
- No description of what's returned
- No safety classification

### ❌ Technically Correct But Too Long

```python
def enrich_ip(ip_address: str) -> dict:
    """This function connects to the AbuseIPDB REST API using the v2/check endpoint
    with the ipAddress parameter set to the provided IPv4 address string. It 
    retrieves JSON response data including the abuseConfidenceScore field which 
    ranges from 0 to 100 where higher values indicate more abuse reports have been 
    filed against this IP. It also retrieves countryCode, isp, and isPublic fields.
    The function handles HTTP errors using try/except blocks and returns a normalized
    dictionary ready for use by the calling agent."""
    ...
```

Problems:
- The agent doesn't need implementation details
- It will confuse the LLM about what parameters to pass
- The "when to use it" is completely absent

### ✅ Correct — Clear, Informative, Action-Oriented

```python
def enrich_ip(ip_address: str) -> dict:
    """Look up AbuseIPDB threat intelligence for an IPv4 address.
    Use this as the first enrichment step when an IP appears in an alert or network log.
    Returns: abuse_score (0-100), country, isp, is_tor, total_reports, status.
    Read-only. Safe to automate."""
    ...
```

---

## 3.5 Docstrings for Destructive Tools

Destructive tools require a special docstring format that makes the AI agent pause before calling them:

```python
def quarantine_file(file_path: str, reason: str, approved_by: str) -> dict:
    """[DESTRUCTIVE] Move a file to the quarantine directory for safe storage.
    REQUIRES HUMAN APPROVAL before calling — do not call this without explicit analyst instruction.
    The analyst must provide their name in 'approved_by' and a reason (min 10 characters).
    This action moves the original file and cannot be automatically reversed.
    Audit log entry is required."""
    ...
```

Key elements:
- `[DESTRUCTIVE]` in ALL CAPS at the start — unmistakable flag
- "REQUIRES HUMAN APPROVAL" — explicit instruction to the LLM to pause
- `approved_by` parameter requirement stated — LLM knows it must get this from the human
- Irreversibility noted — sets appropriate caution level

---

## 3.6 The Return Type: Always `dict`

All MCP tools should return a `dict`. Never return a plain string, int, or list as the top-level result. Reasons:
- Structured fields are reliably parseable by the AI
- You can always add `"status": "ok"` or `"status": "error"` to signal success
- Downstream tools can access specific fields by name

```python
# ❌ Bad
def get_score(ip: str) -> int:
    return 98

# ❌ Bad
def get_summary(ip: str) -> str:
    return "IP 185.220.101.45 is high risk"

# ✅ Good
def enrich_ip(ip_address: str) -> dict:
    return {
        "ip": ip_address,
        "abuse_score": 98,
        "risk_level": "HIGH",
        "status": "ok"
    }
```

---

## 3.7 Complete Example: A Fully Annotated MCP Tool Signature

```python
from typing import Optional
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("CTI Server")


@mcp.tool()
def enrich_ip(
    ip_address: str,
    days_back: int = 90,
    include_report_details: bool = False,
    notes: Optional[str] = None
) -> dict:
    """Look up AbuseIPDB threat intelligence data for an IPv4 address.
    
    Use at the start of any alert triage when an external IP address is identified.
    Also use when pivoting from a network flow to assess a destination IP.
    
    Parameters:
        ip_address: The IPv4 address to check (e.g., '185.220.101.45')
        days_back: How many days of abuse reports to include (default: 90)
        include_report_details: If True, include individual report comments
        notes: Optional analyst context to attach to the result
    
    Returns: abuse_score (0-100), country, isp, is_tor, total_reports, status.
    
    Read-only. Safe to automate. No network queries to the target IP."""
    
    # Implementation comes in Module 5
    ...
```

This is the gold standard tool signature. If every tool you write looks like this, the AI agent will always know exactly what to call and when.

---

## Key Takeaways

1. Type hints are mandatory for MCP — FastMCP reads them to generate the JSON schema.
2. Use `Optional[str]` for parameters that can be omitted.
3. Always use `-> dict` as the return type for MCP tools.
4. Docstrings must answer: What does it do? When to call it? What does it return? Is it safe?
5. Destructive tools must start with `[DESTRUCTIVE] REQUIRES HUMAN APPROVAL` in the docstring.
6. A clear "When to use" sentence in the docstring is the most important thing you can write.
7. Never document implementation details in a docstring — document behavior and purpose.

---

## Try It Yourself

Improve these docstrings by rewriting them following the 4-part structure:

**Tool 1:**
```python
def check_hash(hash_value: str) -> dict:
    """Check a hash."""
    ...
```

**Tool 2:**
```python
def block_ip_on_firewall(ip_address: str) -> dict:
    """Call the firewall API to block an IP address. This is a POST request 
    to the internal API at /api/v1/block with the ipAddress field."""
    ...
```

After rewriting, discuss with a partner:
- What specific word or phrase tells the LLM *when* to call each tool?
- What phrase signals to the LLM that human approval is needed for Tool 2?
