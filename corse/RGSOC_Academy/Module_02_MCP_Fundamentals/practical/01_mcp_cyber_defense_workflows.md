---
status: published
---

# MCP in Cyber Defense Workflows

## Standardizing SOC Tool Access

One of MCP's most powerful applications in cyber defense is **standardizing access** to the diverse toolset used in Security Operations Centers. Instead of building custom integrations for each security tool, MCP provides a unified interface.

## Mapping MCP to SOC Tool Categories

### Read-Only Tools (Enrichment & Search)

These tools **gather information** without modifying the environment. They are safe to run automatically:

| Tool Category | MCP Tool Examples | Risk Level |
|--------------|-------------------|------------|
| **CTI Enrichment** | `lookup_ip`, `check_hash`, `whois_domain` | 🟢 Low |
| **SIEM Queries** | `search_events`, `get_alert_details`, `list_recent_alerts` | 🟢 Low |
| **Asset Lookup** | `get_host_info`, `list_installed_software` | 🟢 Low |
| **Reputation** | `check_url_reputation`, `analyze_email_headers` | 🟢 Low |

### Write/Modify Tools (Response & Containment)

These tools **change the environment**. They should require human approval:

| Tool Category | MCP Tool Examples | Risk Level |
|--------------|-------------------|------------|
| **Network Blocking** | `block_ip`, `block_domain`, `add_firewall_rule` | 🔴 High |
| **Endpoint Isolation** | `isolate_host`, `kill_process`, `quarantine_file` | 🔴 High |
| **Account Actions** | `disable_user`, `force_password_reset` | 🟠 Medium |
| **Ticket Management** | `create_incident`, `update_ticket`, `escalate_case` | 🟡 Low-Med |

## Workflow Example: Automated Alert Triage

Here's how MCP tools map to the Sense-Think-Act loop from Module 01:

### Phase 1: Sense (Gather Data)

```
Agent calls MCP tools automatically:
├── siem-server.search_events(query="src_ip=185.220.101.1 AND last_24h")
├── cti-server.lookup_ip(ip="185.220.101.1")
├── cti-server.check_hash(hash="a1b2c3d4...")
└── asset-server.get_host_info(hostname="workstation-42")
```

All of these are **read-only enrichment tools** — safe to run without human approval.

### Phase 2: Think (Analyze & Decide)

The AI agent processes the collected data:
- **Correlates** indicators across tools
- **Assesses** risk level based on threat intel
- **Determines** the appropriate response domain (Clear, Complicated, Complex)

### Phase 3: Act (Execute Response)

Based on risk assessment:

```
IF risk_level == "Critical" AND domain == "Clear":
    # Auto-respond with low-risk actions
    ticket-server.create_incident(severity="critical", ...)
    siem-server.tag_alert(alert_id="...", tags=["auto-triaged", "critical"])

IF risk_level == "Critical" AND domain == "Complicated":
    # Request human approval for containment
    notify_analyst("Recommend isolating workstation-42. Approve?")
    # AFTER approval:
    edr-server.isolate_host(hostname="workstation-42")
    firewall-server.block_ip(ip="185.220.101.1")
```

## Implementation Pattern: MCP Server per Security Domain

Organize your MCP servers by security domain:

```
SOC Agent
├── Client → cti-enrichment-server
│   ├── tool: lookup_ip
│   ├── tool: check_hash
│   ├── tool: whois_domain
│   └── resource: threat_feed_summary
│
├── Client → siem-server
│   ├── tool: search_events
│   ├── tool: get_alert_details
│   └── tool: get_event_count
│
├── Client → edr-server
│   ├── tool: get_host_info
│   ├── tool: list_processes
│   └── tool: isolate_host (requires approval)
│
└── Client → ticketing-server
    ├── tool: create_incident
    ├── tool: update_ticket
    └── tool: get_open_tickets
```

## Separating Read from Write: The Golden Rule

> **Golden Rule:** Separate read-only tools from destructive actions. Enrich automatically, contain with approval.

This separation enables:
1. **Speed** — Enrichment runs instantly without human bottleneck
2. **Safety** — Destructive actions always get human review
3. **Auditability** — Clear separation in logs between observation and action
4. **Compliance** — Demonstrable human-in-the-loop for containment

## Key Takeaways

- Map each SOC tool category to an MCP server with clearly scoped tools
- Separate **read-only** (enrichment) from **write** (containment) tools
- Enrichment tools can run **automatically** for speed
- Containment tools should require **human approval**
- Organize servers by **security domain** (CTI, SIEM, EDR, ticketing)
- The Sense-Think-Act loop maps naturally to MCP tool categories
