---
status: draft
---

# Workshop 3: Network Analysis with MCP

## Workshop Goal

Build an MCP-powered network analysis workflow that parses a pcap file, identifies suspicious hosts and protocols, enriches external IPs, and produces a network risk report.

## Prerequisites

- Workshop 1 complete (CTI server running and registered).
- Python packages: `mcp`, `fastmcp`, `scapy`.
- Sample pcap file provided in the lab resources.

---

## Lab Overview

| Step | Task | Outcome |
|---|---|---|
| 1 | Build the network analysis server | MCP server with pcap parsing tools |
| 2 | Test server in Inspector | All tools return structured results |
| 3 | Register server in AI workspace | Available alongside CTI server |
| 4 | Run analysis workflow via AI agent | Agent parses pcap and enriches IPs |
| 5 | Produce a network risk report | Structured report with verdict |

---

## Sample PCAP Alternative (No Wireshark Required)

If you don't have a pcap, save this as `d:/mcp_course/labs/sample_network_events.json` instead — the server will use this format too:

```json
[
  {"src_ip": "192.168.1.55", "dst_ip": "185.220.101.45", "dst_port": 443, "protocol": "TCP", "bytes": 48200, "packets": 340, "timestamp": "2026-03-09T21:10:00Z"},
  {"src_ip": "192.168.1.55", "dst_ip": "185.220.101.45", "dst_port": 9001, "protocol": "TCP", "bytes": 12400, "packets": 98, "timestamp": "2026-03-09T21:12:00Z"},
  {"src_ip": "192.168.1.102", "dst_ip": "8.8.8.8", "dst_port": 53, "protocol": "UDP", "bytes": 450, "packets": 12, "timestamp": "2026-03-09T21:14:00Z"},
  {"src_ip": "192.168.1.77", "dst_ip": "204.79.197.200", "dst_port": 80, "protocol": "TCP", "bytes": 3200, "packets": 22, "timestamp": "2026-03-09T21:16:00Z"},
  {"src_ip": "192.168.1.55", "dst_ip": "91.108.4.0", "dst_port": 4444, "protocol": "TCP", "bytes": 92100, "packets": 712, "timestamp": "2026-03-09T21:22:00Z"}
]
```

---

## Step 1: Build the Network Analysis Server

Create `d:/mcp_course/servers/network_server.py`:

```python
import json, os
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("Network Analysis Server")

LOG_PATH = os.environ.get("NETWORK_LOG_PATH", "d:/mcp_course/labs/sample_network_events.json")

# Ports commonly used for C2 or non-standard traffic
SUSPICIOUS_PORTS = {4444, 4445, 9001, 9002, 1337, 31337, 8080, 8443, 2222}

# Private RFC1918 ranges (simplified check)
def is_private(ip: str) -> bool:
    return ip.startswith("192.168.") or ip.startswith("10.") or ip.startswith("172.")


def load_events() -> list:
    with open(LOG_PATH) as f:
        return json.load(f)


@mcp.tool()
def get_unique_external_ips() -> dict:
    """Extract all unique external (non-private) destination IP addresses from network logs.
    Use at the start of a network hunt to identify IPs worth enriching.
    Read-only. Safe to automate."""
    try:
        events = load_events()
        external = sorted(set(
            e["dst_ip"] for e in events if not is_private(e["dst_ip"])
        ))
        return {"external_ips": external, "count": len(external), "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def get_connections_for_ip(ip_address: str) -> dict:
    """Return all recorded network connections to or from a specific IP address.
    Use when pivoting on a suspicious external IP to see all internal hosts communicating with it.
    Read-only. Safe to automate."""
    try:
        events = load_events()
        hits = [e for e in events if e["src_ip"] == ip_address or e["dst_ip"] == ip_address]
        return {"ip": ip_address, "connection_count": len(hits), "events": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def get_high_volume_flows(min_bytes: int = 50000) -> dict:
    """Find network flows that transferred more than a threshold of bytes.
    Use to detect potential data exfiltration or large unusual transfers.
    Default threshold is 50,000 bytes. Adjust as needed.
    Read-only. Safe to automate."""
    try:
        events = load_events()
        hits = [e for e in events if e.get("bytes", 0) >= min_bytes]
        hits.sort(key=lambda x: x["bytes"], reverse=True)
        return {"threshold_bytes": min_bytes, "flow_count": len(hits), "flows": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def get_suspicious_port_connections() -> dict:
    """Find all connections using non-standard or commonly abused ports (4444, 9001, 1337, etc).
    Use to detect reverse shells, C2 beaconing, or non-standard remote access.
    Read-only. Safe to automate."""
    try:
        events = load_events()
        hits = [e for e in events if e.get("dst_port") in SUSPICIOUS_PORTS]
        return {
            "suspicious_ports_checked": sorted(SUSPICIOUS_PORTS),
            "hit_count": len(hits),
            "events": hits,
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def get_network_summary() -> dict:
    """Return a high-level summary of the network log: total flows, unique source IPs,
    unique destination IPs, total bytes transferred, and protocol breakdown.
    Use at the start of any network investigation to understand the data scope.
    Read-only. Safe to automate."""
    try:
        events = load_events()
        protocols = {}
        for e in events:
            p = e.get("protocol", "unknown")
            protocols[p] = protocols.get(p, 0) + 1
        return {
            "total_flows": len(events),
            "unique_src_ips": len(set(e["src_ip"] for e in events)),
            "unique_dst_ips": len(set(e["dst_ip"] for e in events)),
            "total_bytes": sum(e.get("bytes", 0) for e in events),
            "protocol_breakdown": protocols,
            "status": "ok"
        }
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## Step 2: Test in Inspector

```bash
$env:NETWORK_LOG_PATH="d:/mcp_course/labs/sample_network_events.json"
npx @modelcontextprotocol/inspector python d:/mcp_course/servers/network_server.py
```

Expected results:
- `get_network_summary()` → 5 flows, breakdown of TCP/UDP.
- `get_suspicious_port_connections()` → 2 hits (ports 9001 and 4444).
- `get_high_volume_flows(50000)` → 2 flows over threshold.
- `get_unique_external_ips()` → 3 external IPs identified.

---

## Step 3: Register in AI Workspace

Add `network_server.py` alongside `cti_server.py` from Workshop 1. Both servers should be active simultaneously so the agent can call enrichment tools on IPs found during network analysis.

---

## Step 4: Live Analysis Exercise

**Prompt to AI agent:**

```
Analyze the network connections in our log file.
1. Summarize the overall scope of the network data.
2. Identify all suspicious port connections.
3. Find high-volume data transfers that could be exfiltration.
4. Get all unique external IPs.
5. Enrich each external IP with AbuseIPDB threat intel.
6. Produce a network risk report.
```

**Expected agent call sequence:**

| Call | Tool | Server |
|---|---|---|
| 1 | `get_network_summary()` | Network |
| 2 | `get_suspicious_port_connections()` | Network |
| 3 | `get_high_volume_flows(50000)` | Network |
| 4 | `get_unique_external_ips()` | Network |
| 5–7 | `enrich_ip(...)` × 3 | CTI |

---

## Step 5: Network Risk Report Format

```
NETWORK RISK REPORT
Date: 2026-03-09
Data Source: sample_network_events.json

SCOPE:
- Total flows: 5 | Protocols: TCP (4), UDP (1)
- Unique internal sources: 3 | External destinations: 3

HIGH-RISK FINDINGS:

1. CRITICAL — C2 Beaconing Suspected
   Host 192.168.1.55 → 185.220.101.45 on ports 443 and 9001
   - Port 9001 is a commonly flagged Tor/C2 port
   - 185.220.101.45: AbuseIPDB score 98, Tor exit node
   - Total data transferred: 60,600 bytes

2. HIGH — Reverse Shell Candidate
   Host 192.168.1.55 → 91.108.4.0 on port 4444
   - Port 4444 is the Metasploit default listener
   - 92,100 bytes transferred (largest flow in dataset)
   - [AbuseIPDB enrich result here]

3. LOW — Standard DNS Traffic
   Host 192.168.1.102 → 8.8.8.8:53 (normal)

IMPACTED INTERNAL HOSTS: 192.168.1.55 (primary), 192.168.1.77

CYNEFIN DOMAIN: Clear (192.168.1.55) — known bad IP with active C2 indicators

RECOMMENDED ACTIONS:
[HUMAN REQUIRED] Isolate 192.168.1.55 immediately
[AUTO-OK] Block 185.220.101.45 and 91.108.4.0 at perimeter firewall
[HUMAN REQUIRED] Capture full memory from 192.168.1.55 before isolation
```

---

## Lab Checklist

- [ ] Network server starts without errors.
- [ ] All 5 tools return valid results in Inspector.
- [ ] Both network and CTI servers active in AI workspace.
- [ ] Agent correctly chains network tools → CTI enrichment.
- [ ] Report identifies suspicious ports, high-volume flows, and enriched IPs.
- [ ] Destructive recommendations marked `[HUMAN REQUIRED]`.

---

## Extension Challenge

Add a tool `get_dns_queries(domain_filter: str = "") -> dict` that extracts all DNS (UDP port 53) events and optionally filters by a domain keyword. Re-run the investigation to include DNS query analysis.

