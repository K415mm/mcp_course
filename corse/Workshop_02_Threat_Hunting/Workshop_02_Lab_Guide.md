---
status: draft
---

# Workshop 2: Threat Hunting with MCP

## Workshop Goal

Build an MCP-powered threat hunting workflow that takes a hypothesis, generates structured SIEM queries, enriches findings, and produces a hunting report with supporting evidence.

## Prerequisites

- Workshop 1 complete (CTI server running and registered).
- Access to a SIEM or log source (Elastic, Splunk, or the provided sample log file).
- Python packages: `mcp`, `fastmcp`, `requests`, `anthropic`.

---

## Lab Overview

| Step | Task | Outcome |
|---|---|---|
| 1 | Build the hunting server | MCP server with log query + hypothesis tools |
| 2 | Test server in Inspector | All tools return structured results |
| 3 | Define a hunting hypothesis | Structured hypothesis using PEAK/TTP format |
| 4 | Run hunting workflow via AI agent | Agent queries logs, enriches hits, summarizes |
| 5 | Produce a hunting report | Structured report with evidence and verdict |

---

## Background: Hypothesis-Driven Hunting

Threat hunting is not random log searching. It starts with a **hypothesis** — a testable prediction inspired by threat intelligence or behavioral anomaly:

> "Hypothesis: A threat actor is using living-off-the-land binaries (LOLBins) to establish persistence on Windows endpoints in our environment, consistent with TA0003 (Persistence) using T1053.005 (Scheduled Task)."

A good hypothesis has:
- **Actor/technique context:** what TTP or behavior are you hunting?
- **Testable condition:** what data signals would prove or disprove it?
- **Data sources:** which logs contain the relevant signals?
- **Verdict criteria:** what evidence count crosses from "not found" to "confirmed"?

---

## Sample Log Data

Save this as `d:/mcp_course/labs/sample_logs.json` for use in the lab:

```json
[
  {"timestamp": "2026-03-09T21:10:00Z", "host": "WIN-DESK-04", "process": "schtasks.exe", "parent": "cmd.exe", "cmdline": "schtasks /create /tn UpdateHelper /tr C:\\Users\\Public\\upd.exe /sc onlogon", "user": "jsmith"},
  {"timestamp": "2026-03-09T21:10:05Z", "host": "WIN-DESK-04", "process": "upd.exe", "parent": "svchost.exe", "cmdline": "upd.exe --connect 185.220.101.45:443", "user": "SYSTEM"},
  {"timestamp": "2026-03-09T21:15:00Z", "host": "WIN-DESK-07", "process": "schtasks.exe", "parent": "powershell.exe", "cmdline": "schtasks /create /tn SysCheck /tr C:\\Temp\\sys.bat /sc daily", "user": "admin"},
  {"timestamp": "2026-03-09T21:20:00Z", "host": "WIN-DESK-12", "process": "wscript.exe", "parent": "excel.exe", "cmdline": "wscript C:\\Users\\Public\\load.vbs", "user": "alee"},
  {"timestamp": "2026-03-09T21:22:00Z", "host": "WIN-DESK-12", "process": "cmd.exe", "parent": "wscript.exe", "cmdline": "cmd /c powershell -enc JAB...", "user": "alee"}
]
```

---

## Step 1: Build the Hunting Server

Create `d:/mcp_course/servers/hunting_server.py`:

```python
import json, re, os
from mcp.server.fastmcp import FastMCP

mcp = FastMCP("Threat Hunting Server")

LOG_PATH = os.environ.get("LOG_PATH", "d:/mcp_course/labs/sample_logs.json")


@mcp.tool()
def search_logs_by_process(process_name: str) -> dict:
    """Search endpoint logs for all events involving a specific process name.
    Use when hunting for known LOLBins (schtasks, wscript, mshta, certutil, etc).
    Returns matching events with host, timestamp, parent process, and command line.
    Read-only. Safe to automate."""
    try:
        with open(LOG_PATH) as f:
            logs = json.load(f)
        hits = [e for e in logs if process_name.lower() in e.get("process", "").lower()]
        return {"process": process_name, "hit_count": len(hits), "events": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def search_logs_by_parent(parent_process: str) -> dict:
    """Search endpoint logs for processes spawned by a specific parent process.
    Use when hunting for suspicious parent-child relationships (e.g., Office spawning cmd).
    Returns matching events with host, child process, and command line.
    Read-only. Safe to automate."""
    try:
        with open(LOG_PATH) as f:
            logs = json.load(f)
        hits = [e for e in logs if parent_process.lower() in e.get("parent", "").lower()]
        return {"parent": parent_process, "hit_count": len(hits), "events": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def search_logs_by_keyword(keyword: str) -> dict:
    """Search endpoint log command lines for a keyword or substring.
    Use for hunting encoded commands, suspicious paths, or specific flags.
    Returns all matching events.
    Read-only. Safe to automate."""
    try:
        with open(LOG_PATH) as f:
            logs = json.load(f)
        hits = [e for e in logs if keyword.lower() in e.get("cmdline", "").lower()]
        return {"keyword": keyword, "hit_count": len(hits), "events": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def list_unique_hosts() -> dict:
    """Return a deduplicated list of all host names in the log dataset.
    Use when starting a hunt to understand the scope of endpoints in the log.
    Read-only. Safe to automate."""
    try:
        with open(LOG_PATH) as f:
            logs = json.load(f)
        hosts = sorted(set(e.get("host", "unknown") for e in logs))
        return {"hosts": hosts, "host_count": len(hosts), "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


@mcp.tool()
def get_events_for_host(hostname: str) -> dict:
    """Return all log events for a specific endpoint hostname.
    Use when pivoting on a host of interest to review its full activity.
    Read-only. Safe to automate."""
    try:
        with open(LOG_PATH) as f:
            logs = json.load(f)
        hits = [e for e in logs if e.get("host", "").lower() == hostname.lower()]
        return {"host": hostname, "event_count": len(hits), "events": hits, "status": "ok"}
    except Exception as e:
        return {"status": "error", "reason": str(e)}


if __name__ == "__main__":
    mcp.run()
```

---

## Step 2: Test in MCP Inspector

```bash
$env:LOG_PATH="d:/mcp_course/labs/sample_logs.json"
npx @modelcontextprotocol/inspector python d:/mcp_course/servers/hunting_server.py
```

Test each tool:
- `search_logs_by_process("schtasks")`
- `search_logs_by_parent("excel")`
- `search_logs_by_keyword("-enc")`
- `list_unique_hosts()`
- `get_events_for_host("WIN-DESK-04")`

**Expected:** at least 2 hits for schtasks, 1 for excel parent, 1 for `-enc` keyword.

---

## Step 3: Define the Hunting Hypothesis

Add this server to your AI workspace alongside the CTI server from Workshop 1.

**Hypothesis prompt to your AI agent:**

```
I want to threat hunt for scheduled task persistence using LOLBins.
MITRE ATT&CK technique: T1053.005 (Scheduled Task/Job)
Data source: endpoint process logs

Please:
1. Search for schtasks.exe activity.
2. Search for unusual parent processes spawning cmd, wscript, or powershell.
3. Enrich any external IPs or hashes you find.
4. Assess if the evidence confirms or denies the hypothesis.
5. Produce a hunting report.
```

---

## Step 4: The Hunting Workflow

The AI agent will autonomously:

| Call | Tool | Purpose |
|---|---|---|
| 1 | `search_logs_by_process("schtasks")` | Find scheduled task creation events |
| 2 | `search_logs_by_parent("excel")` | Find Office → script spawning |
| 3 | `search_logs_by_keyword("-enc")` | Find encoded PowerShell |
| 4 | `get_events_for_host("WIN-DESK-04")` | Pivot on highest-activity host |
| 5 | `enrich_ip("185.220.101.45")` | (via CTI server) Enrich suspicious destination |
| 6 | Synthesize | Draft hunting report |

---

## Step 5: Hunting Report — Expected Output Format

```
HUNT REPORT — T1053.005 Scheduled Task Persistence
Date: 2026-03-09

HYPOTHESIS:
A threat actor is using schtasks.exe to establish persistence on Windows endpoints.

VERDICT: CONFIRMED

EVIDENCE SUMMARY:
- 2 instances of schtasks /create detected (WIN-DESK-04, WIN-DESK-07).
- WIN-DESK-04: schtasks spawned by cmd.exe, created task pointing to C:\Users\Public\upd.exe.
- upd.exe immediately connected to 185.220.101.45:443 (AbuseIPDB: score 98, TOR exit node).
- WIN-DESK-07: schtasks spawned by powershell.exe, daily scheduled task to C:\Temp\sys.bat.
- WIN-DESK-12: Excel spawning wscript.exe → cmd.exe with base64-encoded payload (T1059.001).

MITRE ATT&CK TTPs OBSERVED:
- T1053.005 — Scheduled Task (confirmed on 2 hosts)
- T1059.001 — PowerShell (encoded command, WIN-DESK-12)
- T1059.005 — Visual Basic (wscript.exe, WIN-DESK-12)

IMPACTED HOSTS: WIN-DESK-04, WIN-DESK-07, WIN-DESK-12

CYNEFIN DOMAIN: Complicated (multiple TTPs, unclear if coordinated)

RECOMMENDED ACTIONS:
1. [HUMAN REQUIRED] Isolate WIN-DESK-04 — active C2 communication confirmed.
2. [HUMAN REQUIRED] Remove scheduled tasks on WIN-DESK-04 and WIN-DESK-07.
3. [AUTO-OK] Block 185.220.101.45 at perimeter firewall (Clear domain — known bad IP).
4. Collect forensic artifacts from WIN-DESK-12 prior to containment.
```

---

## Lab Checklist

- [ ] Hunting server starts without errors.
- [ ] All 5 tools return valid results in Inspector.
- [ ] Hunting server registered alongside CTI server in AI workspace.
- [ ] Agent correctly executes multi-tool hunt from the hypothesis prompt.
- [ ] Agent calls `enrich_ip` via CTI server for the suspicious C2 IP.
- [ ] Hunting report contains: verdict, evidence summary, TTPs, impacted hosts, recommended actions.
- [ ] Destructive actions in recommendations are marked `[HUMAN REQUIRED]`.

---

## Extension Challenge

Add a tool `search_logs_by_user(username: str) -> dict` that returns all events for a given user across all hosts. Run a hunt for the user `alee` to profile all their activity during the suspicious window.
