---
status: draft
---

# Example 02 — Agent Loop: MCPClient + LLM-Driven Triage

> **Example Block 2 of 2 | Module 06: Building MCP Clients**

---

## Scenario

An alert arrives automatically at 02:00 AM. No analyst is at a workstation. The autonomous agent connects to the CTI server, enriches the IOCs, applies triage rules, and produces a structured brief — ready for the analyst when they arrive.

This walkthrough shows the complete agentic loop.

---

## The Agent Architecture

```
alert dict
    ↓
MCPClient.connect_to_server("cti", "...")  →  tool list
    ↓
For each IOC in alert:
    MCPClient.invoke_tool("cti", "enrich_X", {ioc: value})
    ↓
All results collected
    ↓
apply_triage_rules(results)  →  verdict + recommended actions
    ↓
write_brief()  →  structured triage brief (no actions taken)
    ↓
notify_analyst()  →  brief stored, analyst reviews on arrival
```

---

## The Complete Agent Script

```python
# autonomous_triage.py
# Run: uv run autonomous_triage.py
import asyncio
import json
import datetime
from mcp_client import MCPClient


# ── Triage rules ─────────────────────────────────────────────────
def apply_triage_rules(enrichments: dict) -> dict:
    """Apply SOC triage policy to enrichment results."""
    verdict = "LOW"
    evidence = []
    actions = []

    ip_raw = enrichments.get("ip", {})
    if isinstance(ip_raw, str):
        try:
            ip_data = json.loads(ip_raw)
        except Exception:
            ip_data = {}
    else:
        ip_data = ip_raw

    hash_raw = enrichments.get("hash", {})
    if isinstance(hash_raw, str):
        try:
            hash_data = json.loads(hash_raw)
        except Exception:
            hash_data = {}
    else:
        hash_data = hash_raw

    score = ip_data.get("abuse_score", 0)
    is_tor = ip_data.get("is_tor", False)
    found_in_malware_db = hash_data.get("found", False)
    malware_family = hash_data.get("signature", "unknown")

    # Apply policy rules
    if score >= 80:
        verdict = "HIGH"
        evidence.append(f"Abuse score {score}/100 — top percentile")
        actions.append("[PENDING APPROVAL] Block IP at perimeter firewall")

    if is_tor:
        verdict = "HIGH"
        evidence.append("Confirmed Tor exit node — always escalate")
        actions.append("[PENDING APPROVAL] Block Tor exit node")

    if found_in_malware_db:
        verdict = "HIGH"
        evidence.append(f"File hash confirmed as {malware_family} in MalwareBazaar")
        actions.append("[PENDING APPROVAL] Quarantine file on affected endpoint")

    if score >= 40 and verdict == "LOW":
        verdict = "MEDIUM"
        evidence.append(f"Elevated abuse score: {score}/100 — review required")

    return {
        "verdict": verdict,
        "evidence": evidence,
        "recommended_actions": actions,
        "cynefin": "Clear" if verdict == "HIGH" and len(evidence) >= 2 else "Complicated"
    }


# ── Brief writer ─────────────────────────────────────────────────
def write_triage_brief(alert: dict, enrichments: dict, analysis: dict) -> str:
    """Write a structured triage brief for analyst review."""
    timestamp = datetime.datetime.utcnow().strftime("%Y-%m-%dT%H:%M:%SZ")

    brief = f"""
╔══════════════════════════════════════════════════════╗
║          AUTONOMOUS TRIAGE BRIEF                     ║
╚══════════════════════════════════════════════════════╝
Alert ID:   {alert.get('id', 'UNKNOWN')}
Generated:  {timestamp}
Agent:      AutonomousTriageAgent v1.0

VERDICT: {analysis['verdict']}
Cynefin Domain: {analysis['cynefin']}

EVIDENCE ({len(analysis['evidence'])} indicators):
""" + "\n".join(f"  • {e}" for e in analysis["evidence"]) + """

ENRICHMENT DETAILS:
""" + json.dumps(enrichments, indent=2) + """

RECOMMENDED ACTIONS [AWAITING ANALYST APPROVAL]:
""" + "\n".join(f"  {i+1}. {a}" for i, a in enumerate(analysis["recommended_actions"])) + """

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
NO AUTOMATED ACTIONS TAKEN. Analyst approval required.
"""
    return brief


# ── Main agent loop ───────────────────────────────────────────────
async def autonomous_triage(alert: dict):
    client = MCPClient()

    try:
        # Connect to CTI server
        print(f"[AGENT] Connecting to CTI server...")
        await client.connect_to_server(
            "cti", "d:/mcp_course/cti-mcp-server/server.py"
        )
        print(f"[AGENT] Connected. Starting enrichment for alert {alert['id']}...")

        enrichments = {}

        # Enrich IP
        if alert.get("ip"):
            print(f"[AGENT] Enriching IP: {alert['ip']}")
            result = await client.invoke_tool(
                "cti", "enrich_ip", {"ip_address": alert["ip"]}
            )
            enrichments["ip"] = result

        # Enrich hash
        if alert.get("hash"):
            print(f"[AGENT] Enriching hash: {alert['hash'][:12]}...")
            result = await client.invoke_tool(
                "cti", "enrich_hash", {"hash_value": alert["hash"]}
            )
            enrichments["hash"] = result

        # Enrich domain
        if alert.get("domain"):
            print(f"[AGENT] Enriching domain: {alert['domain']}")
            result = await client.invoke_tool(
                "cti", "enrich_domain", {"domain": alert["domain"]}
            )
            enrichments["domain"] = result

        # Apply triage rules
        print("[AGENT] Applying triage rules...")
        analysis = apply_triage_rules(enrichments)

        # Write brief
        brief = write_triage_brief(alert, enrichments, analysis)
        print(brief)

        # Save brief to file
        brief_file = f"triage_{alert['id']}.txt"
        with open(brief_file, "w") as f:
            f.write(brief)
        print(f"[AGENT] Brief saved to {brief_file}")

        # Audit log
        with open("agent_audit.jsonl", "a") as f:
            f.write(json.dumps({
                "timestamp": datetime.datetime.utcnow().isoformat(),
                "alert_id": alert["id"],
                "verdict": analysis["verdict"],
                "actions_count": len(analysis["recommended_actions"]),
                "tools_called": list(enrichments.keys()),
                "analyst_approval_needed": len(analysis["recommended_actions"]) > 0
            }) + "\n")

    finally:
        await client.cleanup()
        print("[AGENT] Cleanup complete.")


# ── Test alert ────────────────────────────────────────────────────
TEST_ALERT = {
    "id": "ALT-20260310-0099",
    "type": "C2 Communication Suspected",
    "ip": "185.220.101.45",
    "hash": "3395856ce81f2b7382dee72602f798b642f14d8",
    "domain": None,
    "asset": "192.168.1.55",
    "owner": "john.doe@company.com"
}

asyncio.run(autonomous_triage(TEST_ALERT))
```

---

## Running the Agent

```powershell
cd d:/mcp_course/mcp-client
uv run autonomous_triage.py
```

**Expected output:**

```
[AGENT] Connecting to CTI server...
[AGENT] Connected. Starting enrichment for alert ALT-20260310-0099...
[AGENT] Enriching IP: 185.220.101.45
[AGENT] Enriching hash: 3395856ce81f...
[AGENT] Applying triage rules...

╔══════════════════════════════════════════════════════╗
║          AUTONOMOUS TRIAGE BRIEF                     ║
...
VERDICT: HIGH
Cynefin Domain: Clear

EVIDENCE (2 indicators):
  • Abuse score 98/100 — top percentile
  • Confirmed Tor exit node — always escalate

RECOMMENDED ACTIONS [AWAITING ANALYST APPROVAL]:
  1. [PENDING APPROVAL] Block IP at perimeter firewall
  2. [PENDING APPROVAL] Block Tor exit node

NO AUTOMATED ACTIONS TAKEN. Analyst approval required.
...
[AGENT] Brief saved to triage_ALT-20260310-0099.txt
[AGENT] Cleanup complete.
```

---

## Key Learning Points

| Observation | Principle |
|---|---|
| All enrichments collected before `apply_triage_rules` | Collect evidence first, decide after — no early termination |
| `write_triage_brief` is pure Python, not an MCP call | Synthesis happens in the agent, not in tools |
| "NO AUTOMATED ACTIONS TAKEN" at end of brief | Act gate enforced by design — brief is read-only output |
| Audit log written to `agent_audit.jsonl` | Every agent run is traceable |
| `finally: await client.cleanup()` | Always runs — no orphaned processes |
