---
status: draft
---

# Example: Alert Triage Flow with MCP

## Scenario

An automated SOC agent receives a high-severity alert from the SIEM: "Suspicious outbound connection to known C2 IP from workstation-42." The agent uses MCP tools to triage the alert through the Sense-Think-Act loop.

## The Full Triage Flow

### Phase 1: SENSE — Gather Data via MCP Tools

The agent automatically calls multiple MCP tools in parallel to gather context:

```
┌──────────────────────────────────────────────┐
│            SENSE PHASE                       │
│                                              │
│  siem-server.get_alert_details(id="ALT-042") │
│  ├── alert_type: "C2 Communication"          │
│  ├── src_ip: "10.1.50.42"                    │
│  ├── dst_ip: "185.220.101.1"                 │
│  ├── hostname: "workstation-42"              │
│  └── severity: "high"                        │
│                                              │
│  siem-server.search_events(                  │
│    query="src_ip=10.1.50.42 last_24h"        │
│  )                                           │
│  ├── 147 DNS queries (3 to suspicious TLDs)  │
│  ├── 12 outbound connections to non-standard  │
│  └── 1 large data transfer (2.3GB to ext IP) │
│                                              │
│  cti-server.analyze_ip(ip="185.220.101.1")   │
│  ├── verdict: MALICIOUS                      │
│  ├── tags: ["tor-exit", "c2-server"]         │
│  ├── abuse_score: 97/100                     │
│  └── malware_family: "Cobalt Strike"         │
│                                              │
│  edr-server.get_host_info(host="wks-42")     │
│  ├── user: "j.smith"                         │
│  ├── department: "Finance"                   │
│  ├── os: "Windows 11"                        │
│  ├── last_login: "2026-03-12T01:15:00Z"      │
│  └── suspicious_processes: ["beacon.exe"]    │
└──────────────────────────────────────────────┘
```

**All of these are read-only MCP tools** — they ran automatically, no human approval needed.

### Phase 2: THINK — Correlate and Assess

The AI agent processes all gathered data:

```
┌──────────────────────────────────────────────┐
│            THINK PHASE                       │
│                                              │
│  Correlation Results:                        │
│  ├── Destination IP confirmed C2 server      │
│  ├── Cobalt Strike beacon process detected   │
│  ├── Large data exfiltration observed (2.3GB)│
│  ├── Finance department = high-value target  │
│  └── TOR exit node = evasion technique       │
│                                              │
│  Risk Assessment:                            │
│  ├── Confidence: HIGH (multiple indicators)  │
│  ├── Severity: CRITICAL                      │
│  ├── Domain: Complicated (known threat type, │
│  │           but containment has side-effects)│
│  └── Kill Chain Stage: Actions on Objectives │
│                                              │
│  Recommended Actions:                        │
│  ├── [AUTO] Create P1 incident ticket        │
│  ├── [AUTO] Tag alert as confirmed-positive  │
│  ├── [APPROVAL] Isolate workstation-42       │
│  ├── [APPROVAL] Block 185.220.101.1 at FW    │
│  └── [APPROVAL] Disable j.smith account      │
└──────────────────────────────────────────────┘
```

### Phase 3: ACT — Execute Response

#### Automatic actions (Tier 1 & 2):

```
ticket-server.create_incident(
    title="Confirmed Cobalt Strike C2 - workstation-42",
    severity="P1",
    description="Active C2 communication detected...",
    assigned_to="soc-team",
    alert_id="ALT-042"
)
→ Result: INC-2026-0312-001 created

siem-server.tag_alert(
    alert_id="ALT-042",
    tags=["confirmed-positive", "cobalt-strike", "auto-triaged"]
)
→ Result: Alert tagged successfully
```

#### Actions requiring analyst approval (Tier 3):

```
Agent → Analyst notification:
"🚨 CRITICAL: Active Cobalt Strike C2 detected on workstation-42 (Finance).
I recommend:
1. Isolate workstation-42 from network
2. Block 185.220.101.1 at perimeter firewall
3. Disable j.smith account pending investigation

Approve all / Approve selective / Deny?"

Analyst approves actions 1 and 2:

edr-server.isolate_host(
    hostname="workstation-42",
    reason="Active C2 communication - Cobalt Strike",
    approved_by="analyst-kais"
)
→ Result: Host isolated

firewall-server.block_ip(
    ip="185.220.101.1",
    duration="permanent",
    reason="Confirmed C2 infrastructure",
    approved_by="analyst-kais"
)
→ Result: IP blocked at perimeter
```

## Time Comparison

| Step | Without MCP | With MCP |
|------|------------|----------|
| Alert review | 5 min (manual) | Instant (auto) |
| IOC enrichment | 10 min (copy-paste across tools) | 3 sec (parallel MCP calls) |
| Event correlation | 15 min (manual SIEM queries) | 5 sec (auto query + LLM) |
| Risk assessment | 5 min (analyst judgment) | 2 sec (AI + data) |
| Ticket creation | 3 min (manual entry) | 1 sec (auto) |
| Containment | 5 min (manual procedures) | 30 sec (approved + executed) |
| **Total** | **~43 minutes** | **~2 minutes** |

> 📊 **Result:** MCP-powered triage reduced response time from **43 minutes to under 2 minutes** — a 95% reduction.

## Key Takeaways

- The Sense-Think-Act loop maps directly to MCP tool categories
- **Sense** (read-only tools) runs automatically for fast data gathering
- **Think** happens in the AI agent using collected MCP data
- **Act** separates automatic actions from those requiring human approval
- MCP enables **parallel enrichment** across multiple sources simultaneously
- Real-world impact: **95% reduction** in triage time compared to manual workflows
