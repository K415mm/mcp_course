---
status: draft
---

# Guardrails for Safe MCP Automation

## Why Guardrails Matter

MCP gives AI agents the power to execute real actions — query databases, scan networks, block IPs, isolate hosts. With great power comes the need for **explicit safety boundaries**.

Without guardrails, an AI agent could:
- Block legitimate traffic based on a false positive
- Isolate a production server during a misidentified threat
- Exfiltrate sensitive data through an overly permissive tool
- Create an infinite loop of automated responses

## Principle 1: Least Privilege Tool Design

Every MCP tool should be scoped to the **minimum permissions** needed for its task.

### Bad Design ❌

```python
@mcp.tool()
def execute_command(command: str) -> str:
    """Execute any shell command."""
    import subprocess
    return subprocess.check_output(command, shell=True).decode()
```

This gives the AI agent **unrestricted shell access** — it could do anything.

### Good Design ✅

```python
@mcp.tool()
def get_process_list(hostname: str) -> list:
    """Get the list of running processes on a monitored host."""
    # Scoped to a specific, safe operation
    return edr_client.get_processes(hostname)

@mcp.tool()
def check_file_hash(filepath: str) -> dict:
    """Check a file's hash against known malware databases."""
    # Read-only, no modification capability
    file_hash = compute_sha256(filepath)
    return threat_intel.lookup_hash(file_hash)
```

### Scoping Guidelines

| Principle | Example |
|-----------|---------|
| **Single responsibility** | One tool does one thing |
| **No shell access** | Wrap specific commands, not generic execution |
| **Read-only by default** | Add write capabilities only when necessary |
| **Input validation** | Validate and sanitize all arguments |
| **Output filtering** | Don't expose raw internal data |

## Principle 2: Explicit Action Boundaries

Define clear categories for what can be automated vs. what requires human approval.

### Automation Tiers

```
┌─────────────────────────────────────────────┐
│         TIER 1: FULLY AUTOMATED             │
│  • IOC enrichment and reputation checks     │
│  • Log queries and event correlation        │
│  • Alert classification and tagging         │
│  • Report generation                        │
├─────────────────────────────────────────────┤
│    TIER 2: AUTOMATED WITH NOTIFICATION      │
│  • Ticket creation and assignment           │
│  • Alert escalation                         │
│  • Adding indicators to watchlists          │
│  • Sending analyst notifications            │
├─────────────────────────────────────────────┤
│    TIER 3: REQUIRES HUMAN APPROVAL          │
│  • IP/domain blocking                       │
│  • Endpoint isolation                       │
│  • Account disabling                        │
│  • Firewall rule changes                    │
├─────────────────────────────────────────────┤
│     TIER 4: MANUAL ONLY (NO AUTOMATION)     │
│  • Production system changes                │
│  • Evidence destruction/modification        │
│  • Communication with external parties      │
│  • Legal or compliance actions              │
└─────────────────────────────────────────────┘
```

## Principle 3: Human-in-the-Loop for High-Risk Actions

For Complicated or Complex domain actions (from Module 01's Cynefin framework):

```python
@mcp.tool()
def isolate_host(hostname: str, reason: str, approved_by: str = None) -> dict:
    """Isolate a host from the network. Requires analyst approval."""
    if not approved_by:
        return {
            "status": "pending_approval",
            "message": f"Isolation of {hostname} requires analyst approval.",
            "reason": reason,
            "action_required": "Call this tool again with approved_by parameter"
        }

    # Log the approval
    audit_log.record(
        action="isolate_host",
        target=hostname,
        approved_by=approved_by,
        reason=reason,
        timestamp=datetime.utcnow()
    )

    return edr_client.isolate(hostname)
```

## Principle 4: Audit Everything

Every MCP tool invocation should be logged:

```json
{
  "timestamp": "2026-03-12T01:30:00Z",
  "agent_id": "soc-agent-01",
  "tool": "block_ip",
  "server": "firewall-mcp",
  "arguments": { "ip": "185.220.101.1", "duration": "24h" },
  "approved_by": "analyst-kais",
  "result": "success",
  "alert_id": "ALERT-2026-0312-001"
}
```

### What to Log

- **Who** — Which agent and which human approved
- **What** — The exact tool called and its arguments
- **When** — Timestamp of invocation
- **Why** — The alert or context that triggered the action
- **Result** — Success, failure, or error details

## Principle 5: Fail Safe

When an MCP tool encounters an error or unexpected input, it should **fail safely**:

- Return an error message, don't crash
- Don't execute partial actions
- Don't escalate permissions
- Log the failure for review
- Default to "no action" rather than "action"

## Implementation Checklist

- [ ] Each tool does **one thing only**
- [ ] Read tools are **separated** from write tools
- [ ] Write tools require **explicit approval parameters**
- [ ] All tool arguments are **validated and sanitized**
- [ ] Every invocation is **audit logged**
- [ ] High-risk tools are **gated behind human approval**
- [ ] Tools **fail safely** on errors
- [ ] Tool descriptions are **clear and accurate** (the AI reads them)
- [ ] Input schemas use **strict types** with constraints

## Key Takeaways

- Apply **least privilege** — each tool should do the minimum necessary
- Define **explicit tiers** for automation, notification, approval, and manual-only actions
- Require **human-in-the-loop** for containment and destructive actions
- **Audit log** every tool invocation with full context
- Design tools to **fail safely** — no action is better than wrong action
