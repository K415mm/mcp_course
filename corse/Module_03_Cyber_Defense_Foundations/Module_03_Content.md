---
status: draft
---

# Module 3: Cyber Defense Foundations for MCP Use

## Module Goal

Build a clear understanding of how SOC workflows map to MCP capabilities, and identify the real-world cyber defense tools that MCP can safely orchestrate.

## Learning Objectives

1. Map the five SOC phases to Sense-Think-Act and identify where MCP adds leverage.
2. Describe the four core cyber defense workstreams and their data types.
3. Explain how open-source MCP servers (Pentest, Kali, REMnux, IDA Pro) expose security tools to AI agents.
4. Apply safe-use principles to each workstream (what can be automated vs. what requires human approval).

---

## Theoretical Section

### 3.1 SOC Workflow Mapping

Modern SOC operations follow a repeating cycle of five phases. MCP enables AI agents to assist at each phase — but the level of safe autonomy varies.

| Phase | Description | MCP Role | Safe Autonomy Level |
|---|---|---|---|
| Detection | Ingest alerts from SIEM, EDR, email | Sense — pull alert data via MCP tools | ✅ Fully automatable |
| Triage | Prioritize and enrich alerts | Sense + Think — enrich IOCs, correlate events | ✅ Fully automatable |
| Investigation | Deep-dive analysis of suspicious activity | Think — query logs, pcap, malware reports | ⚠️ Analyst validates |
| Containment | Block IPs, isolate hosts, quarantine files | Act — invoke destructive tools | 🚫 Human approval required |
| Recovery | Restore systems, close alerts, document findings | Act — generate reports, close tickets | ⚠️ Analyst reviews |

**Key principle:** MCP tools that only read data (enrichment, queries, analysis) can be automated safely. MCP tools that modify state (block, isolate, patch) must require human approval in all but the Clear Cynefin domain.

---

### 3.2 The Four Core Cyber Defense Workstreams

#### Cyber Threat Intelligence (CTI)

- **Data types:** IOCs (IPs, domains, hashes, URLs), STIX/TAXII feeds, threat actor TTPs.
- **AI role:** ingest raw feeds → normalize → enrich → correlate → produce a ranked intelligence brief.
- **MCP tools needed:** DNS lookup, WHOIS, reputation APIs (VirusTotal, AbuseIPDB), hash lookup.

#### Threat Hunting

- **Data types:** SIEM event logs, endpoint telemetry, network flows.
- **AI role:** translate a hunting hypothesis into structured queries → run queries → summarize evidence.
- **MCP tools needed:** SIEM search API, EDR query API, log file reader.

#### Network Analysis

- **Data types:** pcap files, NetFlow/IPFIX records, DNS logs.
- **AI role:** parse packet captures → identify suspicious protocols, IPs, and patterns → generate a triage report.
- **MCP tools needed:** tshark/Wireshark pcap parser, IP geolocation lookup, protocol classifier.

#### Malware Analysis

- **Data types:** PE files, scripts, office documents, archives.
- **AI role:** detect file type → select correct analysis chain → extract IOCs → produce neutral-language summary.
- **MCP tools needed:** file hash, strings extractor, PE header parser, sandbox API, YARA scanner.

---

### 3.3 Real-World MCP Servers for Cyber Defense

These open-source servers demonstrate how practitioners are already using MCP to orchestrate security tools with AI.

#### Pentest MCP Server

- **Purpose:** autonomous penetration testing over SSH on Linux distributions (Kali, Parrot).
- **Key feature — Persistent Sessions (tmux):** wraps AI terminal access in a tmux session. Long-running scans (e.g., 3-hour nmap sweep) survive network drops; the AI reconnects and reads results.
- **Key feature — Interactive Tool Support:** lets the AI send input to interactive tools (msfconsole, SQL shells, reverse shells) without freezing — something standard terminals cannot do.
- **Safe-use rule:** limit to isolated lab environments. Never connect to production infrastructure.

#### awsome_kali_MCPServers

- **Purpose:** expose Kali Linux tools to AI clients via Docker sandbox containers.
- **Network analysis tools:**
  - `basic_scan` / `stealth_scan` using Nmap.
  - Real-time traffic capture using Wireshark/tshark.
- **Binary analysis tools:**
  - `nm` — sort and decode binary symbols.
  - `objdump` — read file headers and disassemble code.
  - `strings` — extract text by encoding type or offset.
- **Safe-use rule:** run all tools inside Docker; never mount production volumes.

#### REMnux MCP Server

- **Purpose:** connect AI assistants to the REMnux malware analysis toolkit, operating safely via Docker exec, SSH, or local execution.
- **Key feature — Encoded Expert Workflows:** the server detects the file type (malicious PDF, Windows PE, Office macro) and automatically selects and runs the correct chain of analysis tools. No prompt engineering needed.
- **Key feature — Neutral Language Normalization:** raw tool output uses emotionally loaded terms ("suspicious," "dangerous"). When an LLM reads these, it can hallucinate malicious intent for benign files. The REMnux server translates terms:
  - `suspicious` → `notable`
  - `dangerous API` → `API commonly observed in malware` 
  - This forces the LLM to reason from evidence rather than react to language.
- **Safe-use rule:** process only copies of samples; keep original files hash-verified and locked.

#### IDA Pro MCP Plugin

- **Purpose:** turn an AI chat window into a reverse engineering co-pilot by installing an MCP server plugin directly into IDA Pro.
- **Read capabilities:**
  - Fetch raw byte data and disassembly for any function.
  - Pull decompiled pseudocode for rapid understanding.
  - List all functions, find cross-references.
- **Write capabilities:**
  - Batch rename functions and variables (e.g., `sub_401A3C` → `decrypt_config`).
  - Patch assembly instructions directly within IDA Pro.
- **Safe-use rule:** enable write operations only in sandboxed IDA sessions backed up before analysis.

---

## Practical Section

### Exercise: SOC Phase Mapping

For each of the following alert types, identify which SOC phase(s) can be safely handled by an MCP agent and which requires human approval:

1. A phishing email alert with a known malicious URL (in threat intel feed).
2. An EDR alert for an unusual process spawning `cmd.exe` from a document.
3. A SIEM alert for failed login attempts from an unusual country.
4. A network flow anomaly showing large outbound data transfer at 3 AM.
5. A file hash match against a known ransomware sample.

### Exercise: Workstream Tool Mapping

For each workstream (CTI, Threat Hunting, Network Analysis, Malware Analysis), list:
- Two MCP tools you would build as read-only.
- One MCP tool that would require human approval before execution.

### Checklist: Safe Automation Boundaries

Before deploying any MCP security tool, verify:
- [ ] The tool reads data only (or if it writes, an approval gate exists).
- [ ] The tool runs in an isolated environment (Docker, VM, sandbox).
- [ ] Output is normalized to avoid LLM bias.
- [ ] All tool calls are logged with timestamp, input parameters, and output.
- [ ] The tool scope is limited to the minimum required permissions.

---

## Example Section

### Phishing Alert — Full MCP Triage

**Incoming alert:** Email from `noreply@safe-update-portal[.]net` with attachment `invoice_Q4.pdf`.

1. **Sense (MCP tools fire automatically):**
   - DNS lookup: `safe-update-portal[.]net` → newly registered 3 days ago.
   - WHOIS: registrant is privacy-masked.
   - VirusTotal hash: `invoice_Q4.pdf` hash → 12/68 vendor detections.
   - URLScan: page screenshot shows credential harvest form.

2. **Think (LLM correlates):**
   - Multiple indicators align: new domain + hash detections + phishing page.
   - Cynefin domain: **Clear** (known phishing kit signature).

3. **Act (auto-action, no human needed):**
   - Quarantine email from all inboxes.
   - Block domain at email gateway.
   - Notify affected user.
   - Generate IOC report for CTI feed.

### Ransomware Sample — MCP Malware Analysis

**File received:** `update_patch.exe` (hash does not match known families).

1. **Sense:** REMnux server detects PE type, runs strings, PE header parser, import table analysis.
2. **Neutral output:** "The file imports `VirtualAlloc`, `CreateRemoteThread`, and `WriteProcessMemory`. These are APIs commonly observed in shellcode injection workflows."
3. **Think:** LLM notes injection-capable imports + no known hash → Cynefin **Complicated**.
4. **Act (human required):** Analyst reviews evidence → approves sandbox detonation → MCP triggers sandbox API → behavioral report returned.

---

## Knowledge Check

1. Which SOC phase must always require human approval before an MCP agent can take action?
2. What is the purpose of neutral language normalization in the REMnux MCP server?
3. Why does the Pentest MCP server use tmux instead of a standard terminal?
4. Name one MCP tool you would classify as read-only and one that would need an approval gate.
5. In which Cynefin domain is it safe to let an MCP agent take containment action autonomously?

---

## Reading List (Module 3 Source Files)

- [Practical Cyber Defense via MCP.md](file:///d:/mcp_course/Practical%20Cyber%20Defense%20via%20MCP.md)
- [Agentic_Cyber_Defense_via_MCP.pdf](file:///d:/mcp_course/Agentic_Cyber_Defense_via_MCP.pdf)
- [MCP_Cyber_Defense.pdf](file:///d:/mcp_course/MCP_Cyber_Defense.pdf)
- [Governing_SOC_Agentic_AI.pdf](file:///d:/mcp_course/Governing_SOC_Agentic_AI.pdf)
