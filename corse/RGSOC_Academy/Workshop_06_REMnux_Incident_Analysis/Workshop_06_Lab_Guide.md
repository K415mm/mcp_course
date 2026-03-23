---
status: draft
---

# Workshop 6: Incident Analysis with REMnux & MCP

## Workshop Goal

Analyze a simulated real-world incident — a Go-based malware implant ("GhostForge") captured in network traffic — using the REMnux malware analysis toolkit, assisted by AI via the Model Context Protocol (MCP). Students also learn the equivalent manual techniques using Scapy and IDA Pro / Ghidra.

## Prerequisites

- Modules 1–6 complete (MCP Fundamentals, Servers & Clients)
- Workshop 1 (CTI Automation) recommended
- Trae AI installed on your workstation
- Your team's REMnux MCP endpoint URL and token (provided by instructor)

---

## Scenario Briefing

> **Classification:** INTERNAL — LAB EXERCISE ONLY

At **09:15 UTC on March 20, 2026**, the SOC flagged anomalous outbound HTTP traffic from **dmz-web-03** (`10.0.5.42`), a Linux server in the DMZ. The investigation uncovered:

- Repeated HTTP POST requests to **unknown domains** not in the whitelist
- DNS queries matching known C2 infrastructure patterns
- A large HTTP POST suggesting **data exfiltration**

The incident response team has:
1. Isolated the server
2. Captured a **2-hour PCAP** of network traffic
3. Recovered a **suspicious binary** from `/tmp/.cache_update`

**Your mission:** Analyze both artifacts using REMnux + MCP and produce a full incident report.

---

## Threat Actor: GhostForge

| Attribute | Detail |
|---|---|
| **Designation** | GhostForge |
| **Attribution** | Eastern European cybercriminal group |
| **Active Since** | 2024 |
| **Known TTPs** | Go-based implants, UPX packing, HTTP/DNS C2, credential harvesting |

### Known IOCs

| Type | Value | Confidence |
|---|---|---|
| Domain | `update-service[.]xyz` | High |
| Domain | `cdn-assets[.]live` | High |
| Domain | `telemetry-collector[.]tech` | Medium |
| Domain | `backup-update[.]xyz` | Medium |
| IP | `185.234.72.19` | Medium |
| IP | `91.215.85.144` | Medium |
| IP | `194.36.28.53` | Low |

### MITRE ATT&CK Mapping

| Tactic | Technique | ID |
|---|---|---|
| Execution | Command and Scripting Interpreter | T1059 |
| Persistence | Scheduled Task/Job: Cron | T1053.003 |
| Persistence | Systemd Service | T1543.002 |
| Defense Evasion | Obfuscated Files (UPX) | T1027.002 |
| Defense Evasion | Debugger Evasion | T1622 |
| Discovery | System Information Discovery | T1082 |
| Command & Control | Application Layer Protocol: HTTP | T1071.001 |
| Command & Control | Application Layer Protocol: DNS | T1071.004 |
| Exfiltration | Exfiltration Over C2 Channel | T1041 |

---

## Team Assignments

| Team | REMnux URL | Token |
|---|---|---|
| Alpha | `http://164.90.233.77:3001/mcp` | _(from instructor)_ |
| Bravo | `http://164.90.233.77:3002/mcp` | _(from instructor)_ |
| Delta | `http://164.90.233.77:3003/mcp` | _(from instructor)_ |

---

## Step 1: Configure MCP in Trae AI

Open your Trae AI **MCP settings** and add:

```json
{
  "mcpServers": {
    "remnux": {
      "type": "streamable-http",
      "url": "http://164.90.233.77:300X/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_TEAM_TOKEN"
      }
    }
  }
}
```

> Replace `300X` with your team port (3001-3003) and `YOUR_TEAM_TOKEN` with the token from the instructor.

**Verify:** Ask Trae AI: _"Check which REMnux analysis tools are available"_

You should see tools like `capa`, `floss`, `tshark`, `zeek`, `strings`, `yara`, etc.

---

## Step 2: Network Traffic Analysis (MCP-Assisted)

### 2.0 — Agentic Artifact Download 

The SOC has securely hosted the evidence zip for you on the internal Web App server. Instead of uploading the file manually, command your Trae AI agent to go fetch it!

```
Hey Trae, the incident evidence has been securely hosted by the SOC Lead at https://tunai.cloud/downloads/ghostforge_artifacts.zip.

Please use your `download_from_url` tool to securely download this archive. Then, use the `extract_archive` tool to unzip it (the password is: infected).
```
*Note: The AI will download the file, unzip it, and reveal `incident_capture.pcap` and `suspicious_binary` in your local samples directory!*

### 2.1 — PCAP Overview

```
Analyze the file incident_capture.pcap and give me an overview of the network traffic
```

### 2.2 — DNS Analysis

```
Run tshark on incident_capture.pcap to extract all unique DNS queries:
tshark -r /home/remnux/files/samples/incident_capture.pcap -Y "dns.qr==0" -T fields -e dns.qry.name | sort -u
```

**Look for:** Suspicious domains (`update-service.xyz`, `cdn-assets.live`) and subdomain-encoded DNS beacons (`a1b2c3d4.beacon.backup-update.xyz`)

### 2.3 — HTTP Traffic

```
Run tshark to show all HTTP requests:
tshark -r /home/remnux/files/samples/incident_capture.pcap -Y "http.request" -T fields -e http.host -e http.request.method -e http.request.uri
```

**Look for:** POST to `/api/v2/checkin` (beacon), GET to `/packages/update_v2.bin` (dropper), POST to `/report` (exfiltration)

### 2.4 — Decode C2 Payloads

```
Extract HTTP POST bodies and decode the Base64 payload
```

The beacons contain JSON with hostname, OS, username, local IPs, implant version (`2.1.4-ghostforge`), and a checkin timestamp.

### 2.5 — Extract IOCs from PCAP

```
Extract IOCs from all the network analysis results
```

---

## Step 3: Malware Binary Analysis (MCP-Assisted)

The binary is at `/home/remnux/files/samples/suspicious_binary`.

### 3.1 — File Identification

```
Get file info for suspicious_binary
```

Result: ELF 64-bit Go binary, **UPX packed**

### 3.2 — Automated Analysis

```
Analyze the file suspicious_binary with deep analysis
```

REMnux will auto-detect UPX and run `capa`, `floss`, `peframe`, `readelf`, etc.

### 3.3 — Unpack UPX

```
Copy suspicious_binary to suspicious_binary_unpacked, then run upx -d on the copy
```

### 3.4 — Deep Analysis of Unpacked Binary

```
Analyze the file suspicious_binary_unpacked with deep analysis
```

**Reveal:** Embedded C2 URLs, XOR-encoded dropper URL, Go function names (`main.beacon`, `main.dropperStage`, `main.gatherSystemInfo`), persistence templates, anti-analysis routines.

### 3.5 — String Extraction

```
Run floss on suspicious_binary_unpacked
```

**Key strings to find:** C2 domains, `/tmp/.cache_update`, `ghostforge`, cron entries, systemd service templates.

### 3.6 — Capability Detection

```
Run capa -vv on suspicious_binary_unpacked
```

Maps to: HTTP communication, system recon, file operations, sandbox detection, encoding/decoding.

---

## Step 4: Manual Analysis (Alternative Path)

### 4.1 — PCAP with Scapy

```python
from scapy.all import *
import base64, json

packets = rdpcap("/home/remnux/files/samples/incident_capture.pcap")

# Extract all DNS queries
dns_queries = set()
for pkt in packets:
    if pkt.haslayer(DNS) and pkt[DNS].qr == 0:
        dns_queries.add(pkt[DNS].qd.qname.decode())

# Separate suspicious from legitimate
legit = {"www.google.com.", "github.com.", "docs.microsoft.com.", "cdn.jsdelivr.net."}
suspicious = dns_queries - legit
print("Suspicious domains:", suspicious)

# Decode C2 beacon payloads
for pkt in packets:
    if pkt.haslayer(Raw):
        payload = pkt[Raw].load.decode(errors='ignore')
        if "POST" in payload and "update-service" in payload:
            body = payload.split("\r\n\r\n", 1)[1]
            decoded = json.loads(base64.b64decode(body))
            print(json.dumps(decoded, indent=2))
```

### 4.2 — Binary with IDA Pro / Ghidra

```bash
# Triage
file suspicious_binary
strings suspicious_binary | grep -i upx

# Unpack
cp suspicious_binary suspicious_binary_unpacked
upx -d suspicious_binary_unpacked

# String analysis
strings -n 8 suspicious_binary_unpacked | grep -iE "(http|\.xyz|\.live|\.tech|ghost|forge|dropper)"

# CAPA capability detection
capa -vv suspicious_binary_unpacked
```

**In IDA Pro / Ghidra:** Navigate to `main.main` → trace calls to `main.beacon`, `main.dropperStage`, `main.installPersistence`. Examine `main.xorDecode` to understand the dropper URL decryption.

---

## Step 5: Incident Report

Complete the following report with your findings:

### Report Template

| Section | Contents |
|---|---|
| **Executive Summary** | 2-3 sentence overview of the incident |
| **Timeline** | Chronological events from the PCAP |
| **IOCs** | All domains, IPs, hashes, file paths discovered |
| **Malware Analysis** | Binary type, capabilities, packing, embedded strings |
| **ATT&CK Mapping** | Techniques with evidence from your analysis |
| **C2 Analysis** | Protocol, beacon interval, payload format, fallback channels |
| **Recommendations** | Immediate actions, short-term, long-term mitigations |

### IOC Export (JSON)

```json
{
  "domains": ["update-service.xyz", "cdn-assets.live", "telemetry-collector.tech"],
  "ips": ["185.234.72.19", "91.215.85.144", "194.36.28.53"],
  "hashes": { "sha256": ["<packed_hash>", "<unpacked_hash>"] },
  "file_paths": ["/tmp/.cache_update"],
  "urls": [
    "http://update-service.xyz/api/v2/checkin",
    "http://cdn-assets.live/packages/update_v2.bin",
    "http://telemetry-collector.tech/report"
  ]
}
```

---

## Lab Checklist

- [ ] Trae AI connected to your team's REMnux container via MCP
- [ ] DNS analysis: identified all suspicious domains
- [ ] HTTP analysis: found C2 beacons, dropper download, and exfil
- [ ] Decoded at least one Base64 beacon payload
- [ ] Binary identified as UPX-packed Go ELF
- [ ] Successfully unpacked with UPX
- [ ] Extracted C2 URLs and dropper path from strings
- [ ] CAPA identified malware capabilities
- [ ] IOCs cross-referenced between PCAP and binary
- [ ] ATT&CK mapping completed
- [ ] Incident report submitted

---

## Extension Challenge

1. Use Scapy to write a Python script that **automatically detects** DNS tunneling by finding queries with encoded subdomains longer than 20 characters.
2. Write a YARA rule that matches the GhostForge implant based on the unique strings you extracted.
3. Use the `extract_iocs` MCP tool to generate a STIX bundle from your findings.

**Time Limit:** 120 minutes | **Difficulty:** Intermediate | **Team Size:** up to 17
