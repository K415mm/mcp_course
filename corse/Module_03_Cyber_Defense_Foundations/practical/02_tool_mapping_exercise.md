---
status: draft
---

# Practical 02 — Tool Mapping Exercise

> **Practical Block 2 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## Exercise Goal

Map specific cyber defense tasks to the correct MCP tool type, server, and workstream. Then evaluate whether each task is appropriate for automation based on the Cynefin framework.

---

## Part A: Task-to-Tool Mapping

For each of the 15 cyber defense tasks below, complete the mapping:

| Column | Options |
|---|---|
| **Workstream** | CTI / Threat Hunting / Network Analysis / Malware Analysis |
| **MCP Tool Type** | Sense (read-only) / Act (state-changing) |
| **Cynefin Threshold** | Clear / Complicated / Forbidden |
| **Example Tool Function Name** | `function_name(params)` |

---

| # | Task | Workstream | Tool Type | Cynefin Threshold | Tool Name |
|---|---|---|---|---|---|
| 1 | Check if an IP address has been reported for abuse | ? | ? | ? | ? |
| 2 | Look up the registration date of a newly seen domain | ? | ? | ? | ? |
| 3 | Compute MD5/SHA256 of a suspicious file | ? | ? | ? | ? |
| 4 | Search endpoint logs for `cmd.exe` spawned by `excel.exe` | ? | ? | ? | ? |
| 5 | Block a confirmed-malicious IP at the perimeter firewall | ? | ? | ? | ? |
| 6 | Extract all printable strings from a binary file | ? | ? | ? | ? |
| 7 | Find all internal hosts that communicated with a flagged IP | ? | ? | ? | ? |
| 8 | Identify all DNS queries on port 53 from a specific host | ? | ? | ? | ? |
| 9 | Quarantine a file confirmed to contain malware | ? | ? | ? | ? |
| 10 | Build a timeline of events for a user account over 7 days | ? | ? | ? | ? |
| 11 | Find all outbound flows over 50 MB in the last 24 hours | ? | ? | ? | ? |
| 12 | Disable a user account in Active Directory | ? | ? | ? | ? |
| 13 | Look up a hash against VirusTotal | ? | ? | ? | ? |
| 14 | Scan a file against YARA rules | ? | ? | ? | ? |
| 15 | Send an incident notification email to the affected user | ? | ? | ? | ? |

---

## Part B: Server Architecture Planning

Based on your tool inventory from Part A, group the tools into MCP servers:

**Design rule:** Each MCP server should have a single, coherent purpose. Tools within a server share the same data sources, API keys, and risk profile.

Suggested server groups for this exercise:
1. **CTI Enrichment Server** — tools that call external threat intelligence APIs
2. **SIEM/Log Query Server** — tools that read from the SIEM or EDR
3. **Network Analysis Server** — tools that process network flow and pcap data
4. **Malware Analysis Server** — tools that inspect files statically
5. **Response Server** — tools that change system state (Act tools)

Assign each of the 15 tools to a server. Then answer:
- Which server should be deployed first, and why?
- Which server requires the most strict access controls, and why?
- Which server should never be available to an AI agent without an explicit human approval gate on every tool?

---

## Part C: Designing the Permission Matrix

Design a permission matrix for your five servers. Define:
- Who can add tools to each server (Developer / SOC Lead / CISO)?
- Which users/roles can call act tools on the Response Server?
- What logging is required per server (minimal / standard / full audit)?

```
PERMISSION MATRIX

| Server               | Add Tools   | Call Act Tools | Audit Level |
|----------------------|-------------|----------------|-------------|
| CTI Enrichment       | Developer   | N/A (no act)   | Standard    |
| SIEM/Log Query       | ?           | N/A (no act)   | ?           |
| Network Analysis     | ?           | N/A (no act)   | ?           |
| Malware Analysis     | ?           | N/A (no act)   | ?           |
| Response (Act)       | SOC Lead    | Analyst+       | Full audit  |
```

---

## Reference Answers (Instructor)

| # | Workstream | Type | Cynefin | Tool Name |
|---|---|---|---|---|
| 1 | CTI | Sense | Clear | `enrich_ip(ip_address)` |
| 2 | CTI | Sense | Clear | `get_whois(domain)` |
| 3 | Malware | Sense | Clear | `compute_file_hashes(file_path)` |
| 4 | Hunting | Sense | Complicated | `search_logs_by_parent("excel.exe")` |
| 5 | CTI/Response | Act | Clear (w/ approval) | `block_ip(ip, approved_by)` |
| 6 | Malware | Sense | Clear | `extract_strings(file_path)` |
| 7 | Network | Sense | Complicated | `get_connections_for_ip(ip)` |
| 8 | Network | Sense | Complicated | `get_dns_query_log(host)` |
| 9 | Malware/Response | Act | Clear (w/ approval) | `quarantine_file(path, reason, approved_by)` |
| 10 | Hunting | Sense | Complicated | `get_user_timeline(user, days=7)` |
| 11 | Network | Sense | Complicated | `get_high_volume_flows(min_bytes=50000000)` |
| 12 | Response | Act | Complicated (SOC Lead) | `disable_user_account(user, approved_by)` |
| 13 | CTI | Sense | Clear | `enrich_hash(hash_value)` |
| 14 | Malware | Sense | Clear | `run_yara_scan(file_path, ruleset)` |
| 15 | Response | Act | Clear (low-risk) | `send_notification(user, template)` |
