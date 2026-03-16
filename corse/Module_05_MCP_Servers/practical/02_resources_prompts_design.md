---
status: published
---

# Practical 02 — Add Resources, Prompts, and Multi-Tool Design

> **Practical Block 2 of 3 | Module 05: Building MCP Servers**

---

## Part A: Add a Resource to Your CTI Server

Open `d:/mcp_course/cti-mcp-server/server.py` and add this resource:

```python
@mcp.resource("threat://triage-policy")
def triage_policy() -> str:
    """Return the current CTI triage policy document."""
    return """CTI TRIAGE POLICY v2.1
━━━━━━━━━━━━━━━━━━━━━━━━
AUTOMATIC ACTIONS (Sense tools, no approval needed):
  - Enrich all IPs, domains, hashes in any alert
  - Query SIEM for corroborating events

ESCALATION THRESHOLDS:
  - abuse_score >= 80: Escalate immediately
  - abuse_score 40-79: Review within 2 hours
  - abuse_score < 40: Log and close (no other indicators)
  - Tor exit node: Always escalate (regardless of score)

ACT TOOL POLICY (human approval mandatory for all):
  - block_ip: Requires analyst name + ticket ID
  - quarantine_file: Requires analyst name + hash confirmation
  - disable_user: Requires SOC Lead approval

OUTPUT NORMALIZATION:
  - Return "elevated indicator count" not "high risk"
  - Return "flagged by vendor" not "malicious"
  - Return "commonly scrutinized API" not "dangerous import"
"""


@mcp.resource("threat://known-safe-ips")
def known_safe_ips() -> str:
    """Return list of known-safe IPs that should not be escalated."""
    return "\n".join([
        "8.8.8.8",       # Google DNS
        "1.1.1.1",       # Cloudflare DNS
        "208.67.222.222" # OpenDNS
    ])
```

Test: after adding, restart the Inspector and check the Resources panel shows both URIs.

---

## Part B: Add a Prompt Template

```python
from mcp.types import PromptMessage, TextContent

@mcp.prompt()
def ip_alert_triage(ip_address: str, alert_id: str) -> list[PromptMessage]:
    """Generate a structured triage prompt for a suspicious IP alert."""
    return [
        PromptMessage(
            role="user",
            content=TextContent(
                type="text",
                text=f"""You are a SOC analyst. Triage alert {alert_id} for IP: {ip_address}

REQUIRED STEPS:
1. Call enrich_ip("{ip_address}") to get the threat intelligence score
2. Read threat://triage-policy to determine the action tier
3. Check threat://known-safe-ips — if the IP is listed, close as false positive
4. Write a triage brief with: evidence summary, complexity domain, and recommended actions

CONSTRAINTS:
- Do NOT block or quarantine without my explicit approval
- If abuse_score is between 40-79: flag for review and stop
- If abuse_score >= 80: draft a containment request for my approval

Start now."""
            )
        )
    ]
```

---

## Part C: Multi-Tool Design Exercise

Design a Malware Analysis MCP server. For each tool, fill in the template:

```
Tool name:           [function_name]
Input parameter(s):  [name: type, ...]
Return fields:       [field1, field2, ...]
Docstring line 1:    [what it does in one sentence]
Docstring line 2:    [when should the agent call it]
Docstring line 3:    [what it returns]
Docstring line 4:    [safety classification]
Tool type:           [Sense / Act]
decision-complexity threshold:   [Clear / Complicated / Forbidden-without-approval]
```

Design at least 4 tools for the Malware Analysis server:
1. A tool that computes file hashes
2. A tool that extracts printable strings from a binary
3. A tool that runs YARA rules against a file
4. A tool that looks up a hash on VirusTotal

Then answer: **In what order should an agent call these tools, and why?**

---

## Part D: Knowledge Check

1. What is the difference between a `@mcp.tool()` and a `@mcp.resource()`? Give a cyber defense example of each.
2. Where does `ctx.info("message")` write its output, and why does it matter?
3. What three lines change between a Module 04 Python function and a Module 05 MCP tool?
4. You add a `print("debug")` inside a `@mcp.tool()` function. What happens?
5. Why is `logging.basicConfig(stream=sys.stderr)` at the top of your server file?

---

## Checklist

- [ ] Resource `threat://triage-policy` appears in Inspector Resources panel
- [ ] Resource `threat://known-safe-ips` appears in Inspector Resources panel
- [ ] Prompt `ip_alert_triage` appears in Inspector Prompts panel
- [ ] Prompt generates the correct structured message when filled in
- [ ] Multi-tool design completed (4+ tools documented)
