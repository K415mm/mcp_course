---
status: published
---

# Example 01 — CTI Enrichment Chain: From Raw Alert to Structured Brief

> **Example Block 1 of 2 | Module 03: Cyber Defense Foundations for MCP Use**

---

## Scenario

A SIEM alert fires at 23:47. An analyst is paged but can't get to their SOC workstation for 4 minutes. In that time, an MCP-powered CTI enrichment agent runs automatically and has a complete triage brief ready.

This walkthrough traces every step — every tool call, every reasoning cycle, and every output — of that 4-minute automated triage.

---

## The Alert

```json
{
  "alert_id": "ALT-20260310-0099",
  "type": "Outbound Connection to Threat Intel Feed Match",
  "source_ip": "192.168.1.55",
  "destination_ip": "185.220.101.45",
  "destination_port": 9001,
  "bytes_transferred": 48200,
  "protocol": "TCP",
  "timestamp": "2026-03-10T23:47:00Z",
  "rule": "TI-MATCH-HIGH",
  "asset_owner": "john.doe@company.com",
  "asset_criticality": "MEDIUM"
}
```

---

## Available MCP Servers

- **CTI Server**: `enrich_ip()`, `enrich_domain()`, `enrich_hash()`, `get_whois()`
- **SIEM Server**: `get_alert_details()`, `get_events_for_host()`
- **Network Server**: `get_connections_for_ip()`, `get_high_volume_flows()`

Agent scope set by analyst automation policy:
- Sense tools: fully automated.
- Act tools: none permitted. All containment requires analyst approval.

---

## Step 1 — SENSE: Alert Ingestion

**Tool:** `get_alert_details("ALT-20260310-0099")`

**Returns:** Raw alert dict (as shown above) + related SIEM event count: 3 events in the last 24h from the same source.

**Reasoning cycle 1 output:**  
"I have an outbound connection to an IP flagged in threat intel, on port 9001 (a commonly abused Tor/C2 port), with 48KB transferred. I should enrich the destination IP and check if this host has other flagged connections."

---

## Step 2 — SENSE: IP Enrichment + Host History (Parallel)

**Tool 1:** `enrich_ip("185.220.101.45")`

**Returns:**
```json
{
  "ip": "185.220.101.45",
  "abuse_score": 98,
  "country": "NL",
  "isp": "Frantech Solutions",
  "is_tor": true,
  "total_reports": 412,
  "status": "ok"
}
```

**Tool 2:** `get_events_for_host("192.168.1.55", days=7)`

**Returns:**
```json
{
  "host": "192.168.1.55",
  "event_count": 2847,
  "notable_events": [
    {"type": "outbound", "dst_ip": "185.220.101.45", "port": 9001, "count": 3},
    {"type": "outbound", "dst_ip": "185.220.101.45", "port": 443, "count": 1},
    {"type": "process", "name": "chrome.exe", "user": "john.doe"}
  ],
  "status": "ok"
}
```

**Reasoning cycle 2:**  
"Abuse score 98 — top percentile. Confirmed Tor exit node. 412 reports. Port 9001 is commonly used for Tor and C2 traffic. The same host has connected 4 times in 7 days, always to the same IP. This is a strong pattern.  
complexity domain assessment: **Clear**. Multiple independent corroborating indicators. The pattern is unambiguous."

---

## Step 3 — SENSE: Connection Context

**Tool:** `get_connections_for_ip("185.220.101.45")`

**Returns:**
```json
{
  "ip": "185.220.101.45",
  "connection_count": 4,
  "events": [
    {"src": "192.168.1.55", "port": 443, "bytes": 4200, "ts": "2026-03-04T14:21Z"},
    {"src": "192.168.1.55", "port": 9001, "bytes": 12400, "ts": "2026-03-07T09:11Z"},
    {"src": "192.168.1.55", "port": 9001, "bytes": 18100, "ts": "2026-03-09T21:55Z"},
    {"src": "192.168.1.55", "port": 9001, "bytes": 48200, "ts": "2026-03-10T23:47Z"}
  ],
  "status": "ok"
}
```

**Reasoning cycle 3:**  
"The volume per connection is increasing: 12KB → 18KB → 48KB. This escalating transfer size is consistent with an active C2 session where more data is being exfiltrated or larger commands are being transferred. Only one internal host is involved. The asset owner is john.doe@company.com."

---

## Step 4 — ACT Gate: Structured Approval Request

The agent produces the triage brief and requests approval for containment actions:

```
CTI TRIAGE BRIEF — ALT-20260310-0099
Generated: 2026-03-10T23:51:02Z
Agent: CTI Enrichment Bot | Duration: 4m 02s

VERDICT: HIGH CONFIDENCE — C2 COMMUNICATION SUSPECTED
AUTOMATION LEVEL: CLEAR (evidence unambiguous, multiple independent sources)

EVIDENCE:
1. Destination IP 185.220.101.45
   - AbuseIPDB score: 98/100 (top 2% of reported IPs)
   - Confirmed Tor exit node
   - 412 abuse reports from 181 distinct users
   - 4 connections from 192.168.1.55 over 7 days

2. Port 9001 (TCP)
   - Commonly associated with Tor relay and C2 beaconing
   - 3 of 4 connections used this port

3. Escalating Transfer Pattern
   - 12KB → 18KB → 48KB over 7 days
   - Consistent with active C2 session or data staging

AFFECTED HOST: 192.168.1.55 (john.doe@company.com, criticality: MEDIUM)

RECOMMENDED ACTIONS [AWAITING ANALYST APPROVAL]:
[HUMAN REQUIRED] 1. Isolate 192.168.1.55 from network
[HUMAN REQUIRED] 2. Block 185.220.101.45 at perimeter firewall
[AUTO-OK pending approval] 3. Open P1 incident ticket
[AUTO-OK pending approval] 4. Notify john.doe@company.com's manager

I am NOT taking any action until you confirm.
```

---

## Step 5 — ANALYST REVIEW (2:17 AM, 4 minutes after the alert)

Analyst reads the brief on mobile. Responds:
> "Approved. Execute items 1, 2, 3. Hold item 4 until I get to the office — I'll speak to the manager directly."

The agent executes the three approved tools (from Workshop 1 server + Response server), logs everything, and closes the loop.

---

## Key Learning Points

| Observation | Principle |
|---|---|
| All 5 enrichment calls happened in ~4 minutes | Parallel Sense calls at scale |
| Escalating volume was noted in reasoning | Agent synthesizes patterns, not just single data points |
| Domain classified as Clear after 3 corroborating sources | Domain classification happens during investigation, not before |
| Every action awaited approval | Act gate is policy, not a suggestion |
| Analyst held back one action | Human overrides are always possible |

---

## Discussion

1. At what point in the Sense chain did the evidence become sufficient for a Clear domain verdict?
2. If the abuse score had been 42 (not 98), would the verdict change? What tools would the agent need to call additionally?
3. Why did the agent note "escalating transfer patterns" — what algorithm or reasoning produced that observation?
