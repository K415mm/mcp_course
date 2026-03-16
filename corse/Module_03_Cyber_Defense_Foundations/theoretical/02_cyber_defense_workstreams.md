---
status: published
---

# 02 — The Four Cyber Defense Workstreams

> **Theoretical Block 2 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## 2.1 Why Workstreams Matter for Tool Design

Every MCP tool you build serves a specific workstream. The workstream determines:
- What **data types** the tool must handle (IP vs. file vs. log)
- What **external sources** the tool needs to call
- What **output format** the tool should return
- What **approval gates** are needed before the agent acts

Learning the four core workstreams gives you the mental model to design the right tool for the right job.

---

## 2.2 Workstream 1: Cyber Threat Intelligence (CTI)

### What It Is
CTI is the practice of collecting, normalizing, and analyzing information about threats and threat actors to support defensive decision-making. CTI answers: *"Who is attacking, how, and what do their indicators look like?"*

### Data Types
- **IOCs (Indicators of Compromise):** IP addresses, domain names, file hashes, URLs, email addresses
- **STIX/TAXII:** Structured threat data exchange formats used by platforms like MISP and ISACs
- **TTPs:** Tactics, Techniques, and Procedures (mapped to MITRE ATT&CK)
- **Threat actor profiles:** Attribution, campaigns, toolsets

### The CTI Workflow
```
INGEST raw feed → NORMALIZE format → DEDUPLICATE → ENRICH with context → CORRELATE with alerts → PRODUCE brief
```

### MCP Tools Needed
| Tool | Type | API/Source |
|---|---|---|
| `enrich_ip(ip)` | Sense | AbuseIPDB, VirusTotal, Shodan |
| `enrich_domain(domain)` | Sense | VirusTotal, WHOIS, URLScan |
| `enrich_hash(hash)` | Sense | VirusTotal, MalwareBazaar, ThreatFox |
| `get_threat_actor_profile(actor)` | Sense | MISP, OpenCTI, MITRE ATT&CK API |
| `create_ioc_report(ioc_list)` | Act (low-risk) | Internal SIEM or CTI platform |

### Key Challenges
- **Volume:** automated feeds produce thousands of IOCs per day — most are low-value
- **False positive rate:** shared CTI feeds include many indicators that are benign in your context
- **Staleness:** an IP flagged 6 months ago may be clean today (reused cloud IP)
- **Contextualization:** a hash that is malicious in a Windows environment may not matter in your Linux-only infrastructure

### Security-Safe Design Notes
- Always include `last_seen` and `confidence` in enrichment results — never just a binary verdict
- Return the raw indicator counts (X/68 vendors) not just "malicious/clean" — let the LLM reason from evidence
- Apply output normalization: replace "malicious" with "flagged by vendor"

---

## 2.3 Workstream 2: Threat Hunting

### What It Is
Threat hunting is the proactive search for threats that have evaded automated detection. Unlike alert-driven investigation, hunting starts with a **hypothesis** ("I believe this TTP is being used in my environment") and searches for evidence that confirms or denies it.

### The Hypothesis-Driven Approach
A good hunting hypothesis has:
1. **Threat basis:** a TTP, threat actor TTP, or behavioural anomaly (e.g., MITRE ATT&CK T1053.005)
2. **Testable condition:** a query that could find evidence if the hypothesis is true
3. **Data source:** the log type that would contain the evidence
4. **Verdict criteria:** what constitutes "confirmed" vs. "not found"

```
HYPOTHESIS → QUERY DESIGN → DATA COLLECTION → ANALYSIS → VERDICT → UPDATE DEFENSES
```

### Data Types
- SIEM/EDR event logs: process names, command lines, parent-child relationships
- Network flows: source/destination IP, port, byte volume, timing patterns
- DNS logs: query patterns, newly observed domains, entropy scores
- Authentication logs: login times, countries, devices, success/failure rates

### MCP Tools Needed
| Tool | Type | Source |
|---|---|---|
| `search_logs_by_process(name)` | Sense | SIEM API, Elastic/Splunk |
| `search_logs_by_parent(parent)` | Sense | EDR API |
| `search_logs_by_keyword(keyword)` | Sense | SIEM |
| `get_events_for_host(hostname)` | Sense | SIEM |
| `get_dns_query_log(domain_filter)` | Sense | DNS log source |

### The Human-AI Collaboration Model
In threat hunting, the AI agent is a **data gathering and hypothesis structuring** assistant. The analyst provides the initial hypothesis; the agent runs the queries; the analyst interprets the evidence and decides what to investigate further.

The agent should **never** generate its own hypothesis without analyst input in a production environment. Hypotheses are analytical judgments, not pattern matching.

### Key Challenges
- **Signal-to-noise:** hundreds of events that look relevant but are not
- **Living-off-the-land:** attackers using legitimate Windows tools (schtasks, wmic, certutil) — normal activity looks identical to malicious
- **Time correlation:** an attack staged over weeks produces widely separated log entries that are individually unremarkable

---

## 2.4 Workstream 3: Network Analysis

### What It Is
Network analysis examines traffic patterns, flow data, and packet captures to identify anomalous or malicious communications — C2 beaconing, data exfiltration, lateral movement over the network, and unauthorized protocol usage.

### Data Types
- **pcap files:** full packet captures, including payload content (requires legal authorization)
- **NetFlow/IPFIX:** metadata about flows (who talked to who, how much, when) — no payload
- **DNS logs:** every domain queried, by which host, and resolved to which IP
- **Proxy logs:** HTTP/HTTPS request URLs (with SSL inspection) or just domains (without)

### The Analysis Workflow
```
CAPTURE/INGEST → BASELINE (what's normal?) → ANOMALY DETECTION → ENRICH ANOMALIES → REPORT
```

### MCP Tools Needed
| Tool | Type | Notes |
|---|---|---|
| `get_network_summary()` | Sense | Overview of flow counts, protocols |
| `get_unique_external_ips()` | Sense | Only external IPs for enrichment |
| `get_suspicious_port_connections()` | Sense | Non-standard ports (4444, 9001, 1337) |
| `get_high_volume_flows(min_bytes)` | Sense | Potential exfiltration |
| `enrich_ip(ip)` | Sense | Called via CTI server for each flagged IP |

### Key Challenges
- **Encryption:** most modern C2 uses HTTPS; without SSL inspection you see only metadata
- **CDN overlap:** malicious IPs often share ranges with legitimate CDN services
- **Volume:** enterprise networks generate millions of flows per hour; manual analysis is impossible
- **Baseline dependency:** anomaly detection requires knowing what normal looks like first

### Output Normalization Note
Network analysis tools frequently return terms like "malicious traffic," "suspicious connection," or "anomalous pattern." Apply output normalization before returning to the LLM to prevent over-escalation based on language rather than evidence.

---

## 2.5 Workstream 4: Malware Analysis

### What It Is
Malware analysis is the systematic examination of potentially malicious files to understand their behavior, extract indicators, and determine their threat level. It operates in two stages:

**Static Analysis** — examine the file without executing it:
- File type identification (PE, PDF, ZIP, ELF, script)
- Hash computation and threat intel lookup
- String extraction (embedded URLs, commands, API names)
- Header analysis (PE import table, section names, timestamps)
- YARA rule matching

**Dynamic Analysis** — execute the file in a controlled sandbox:
- Observe system changes (file writes, registry modifications, network connections)
- Capture API call sequences
- Extract network IOCs from live traffic
- Assess behavioral profile

### The Safety-First Rule
Never analyze a potentially malicious file on a host connected to your production network. Always use:
- Isolated virtual machines with snapshots
- Dedicated sandbox environments (Cuckoo, Any.run, Triage.cert.ee)
- Docker containers with network isolation
- REMnux or FlareVM for manual analysis

### MCP Tools Needed
| Tool | Type | Library/Source |
|---|---|---|
| `compute_file_hashes(path)` | Sense | `hashlib` |
| `detect_file_type(path)` | Sense | Magic bytes |
| `extract_strings(path)` | Sense | `re` module |
| `detect_pe_characteristics(path)` | Sense | `pefile` library |
| `submit_to_sandbox(hash)` | Sense | Any.run or Cuckoo API |
| `quarantine_file(path, reason)` | Act | Local filesystem |

### Neutral Language in Malware Analysis
Malware analysis is the workstream most vulnerable to LLM output bias. Raw tool output routinely contains terms that cause the LLM to amplify risk verdicts beyond what the evidence supports.

**Examples of normalization needed:**

| Raw output | Normalized for LLM |
|---|---|
| "Suspicious API: VirtualAlloc" | "API commonly observed in code injection: VirtualAlloc" |
| "Dangerous import: CreateRemoteThread" | "Import commonly used in process injection: CreateRemoteThread" |
| "Malware detected: Trojan.GenericKD" | "Flagged by vendor as: Trojan.GenericKD" |
| "HIGH RISK file" | "Elevated risk indicator present" |

This is not about hiding information — it is about preventing the LLM from jumping to false conclusions before all evidence is considered.

---

## Key Takeaways

1. CTI workstream: ingest, normalize, enrich, correlate. Tools call threat intel APIs.
2. Threat hunting: hypothesis-driven, analyst-led. Agent gathers evidence; analyst decides.
3. Network analysis: flow and packet data. Anomaly detection requires a baseline.
4. Malware analysis: static first, dynamic only in sandbox. Output normalization is critical.
5. Each workstream has a distinct set of Sense tools (safe) and Act tools (gated).

---

## Further Reading

- [Practical Cyber Defense via MCP.md](file:///d:/mcp_course/Practical%20Cyber%20Defense%20via%20MCP.md)
- [Agentic_Cyber_Defense_via_MCP.pdf](file:///d:/mcp_course/Agentic_Cyber_Defense_via_MCP.pdf)
- MITRE ATT&CK Framework: [attack.mitre.org](https://attack.mitre.org)
