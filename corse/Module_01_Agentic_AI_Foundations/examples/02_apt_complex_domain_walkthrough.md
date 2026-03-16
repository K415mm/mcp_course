---
status: published
---

# Example 02 — Novel APT Campaign: Where the Agent Stops

> **Example Block 2 of 2 | Module 01: Agentic AI Foundations**

---

## Scenario Overview

This example demonstrates the **opposite** of Example 01. Instead of showing a successful agent triage, it shows how and why an agent *correctly* operates with extreme restraint when the situation is in the Complex domain.

The goal: understand that an agent that says "I cannot act here — here are my hypotheses" is not a failure. It is the correct and desired behavior.

---

## Starting Conditions

A new external threat intelligence report is shared with the SOC:

```
THREAT ADVISORY — TLP:AMBER

A nation-state actor (unattributed, internally tracked as GRAPHITE-21) 
has been observed targeting financial sector entities.

Indicators (medium-low confidence):
- IP range 91.108.4.0/24 associated with C2 infrastructure (unverified)
- Possible use of custom backdoor — no YARA signatures yet
- TTPs suggest initial access via spearphish, but delivery mechanism unknown
- No prior detections of this actor in public threat feeds

Your environment shows 3 connections to 91.108.4.46 in the last 7 days.
```

---

## Step 0: Domain Classification (Human First)

Analyst reviews the advisory:

> "Novel actor. No signatures. Medium-low confidence indicators. My environment has only 3 connection events over 7 days — could be coincidence, could be legitimate traffic to that IP range. Cause and effect are NOT self-evident. Historical patterns may NOT apply. Classifying as **Complex**. Agent role: Hypothesis Generator only. No act permission. No automated actions."

---

## Step 1: SENSE — Connection Pivot

**Tool called:** `query_siem(filter="dst_ip=91.108.4.46 days=7")`

**Result:**
```json
{
  "events": [
    {"timestamp":"2026-03-02T14:22:00Z","src_ip":"192.168.1.55","dst_ip":"91.108.4.46","port":443,"bytes":4200},
    {"timestamp":"2026-03-05T09:11:00Z","src_ip":"192.168.1.55","dst_ip":"91.108.4.46","port":443,"bytes":6700},
    {"timestamp":"2026-03-08T22:45:00Z","src_ip":"192.168.1.55","dst_ip":"91.108.4.46","port":443,"bytes":3800}
  ],
  "total_count": 3,
  "status": "ok"
}
```

All 3 connections from the same internal host: `192.168.1.55`.

---

## Step 2: THINK — First Reasoning Cycle

LLM chain-of-thought:

> "Three connections over 6 days from a single host to this IP. The traffic uses port 443 (HTTPS — common, could be legitimate). Byte counts are small (3–7KB each). This is consistent with both C2 beaconing AND normal HTTPS traffic to a legitimate service hosted in that IP range. I need to enrich this IP and look at the host's other activity before forming any hypothesis."

---

## Step 3: SENSE — IP Enrichment + Host Activity

**Tool called (1):** `enrich_ip("91.108.4.46")`

**Result:**
```json
{
  "ip": "91.108.4.46",
  "abuse_confidence": 12,
  "country": "NL",
  "isp": "Digital Ocean (cloud hosting)",
  "total_reports": 3,
  "usage_type": "Data Center/Web Hosting",
  "status": "ok"
}
```

**Tool called (2):** `get_events_for_host("192.168.1.55", days=7)`

**Result:**
```json
{
  "host": "192.168.1.55",
  "event_count": 1847,
  "notable_events": [
    {"type": "outbound_dns", "domain": "91.108.4.46.ptr.unknown"},
    {"type": "process_start", "process": "chrome.exe", "user": "jdoe"},
    {"type": "file_access", "path": "C:\\Users\\jdoe\\Documents\\client_data.xlsx"}
  ],
  "status": "ok"
}
```

---

## Step 4: THINK — Second Reasoning Cycle (Generating Hypotheses)

LLM review of all gathered data:

> **Available evidence:**
> - 3 connections over 6 days (regular interval: roughly every 3 days).
> - Small byte counts (3–7KB per session).
> - IP has LOW abuse score (12) — unknown, not confirmed malicious.
> - ISP is DigitalOcean cloud hosting (commonly used for both legitimate services and C2).
> - Host is a user desktop (jdoe), running Chrome.
> 
> **Conflicting interpretations:**
> - The regular 3-day interval + small bytes is consistent with C2 beaconing.
> - It is also consistent with a Chrome extension, telemetry service, or software update check that phones home every 72 hours.
> - The IP's low abuse score does not confirm legitimacy — a freshly stood-up C2 server would also have a low score.
>
> **Conclusion: I cannot determine which hypothesis is correct from this data. Pattern matching fails here — the data is genuinely ambiguous.**

---

## Step 5: ACT — The Correct Response Is: Do Not Act

The agent does not request approval to block the IP. It does not isolate the host. It does not open a P1 incident. Instead:

```
HYPOTHESIS REPORT — GRAPHITE-21 Advisory / 91.108.4.46
Agent: MCP Triage Agent (Hypothesis Generator Mode)
Domain: COMPLEX — autonomous action forbidden

EVIDENCE GATHERED:
- 3 connections over 6 days from host 192.168.1.55 (user: jdoe)
- Port 443 (HTTPS), small payload (avg 4.9KB per session)
- IP AbuseIPDB score: 12 (low, non-conclusive)
- ISP: DigitalOcean cloud hosting

HYPOTHESIS A (most likely — requires validation):
The connections are legitimate browser traffic from a Chrome extension
or software update service using that IP range as a CDN endpoint.
Test: review Chrome extension list on 192.168.1.55, check if any 
installed extension is documented to use this IP range.

HYPOTHESIS B (must be ruled out):
The connections are C2 beaconing. The 3-day interval + small payload
is consistent with low-and-slow C2 check-in. 
Test: capture full HTTPS session content (requires analyst-approved 
SSL inspection), look for anomalous Host headers or TLS fingerprints.

HYPOTHESIS C (lower probability):
Coincidence — jdoe visited a webpage hosted on DigitalOcean that 
happened to share this IP range.
Test: review browser history for jdoe around each connection timestamp.

RECOMMENDED HUMAN ACTIONS:
1. Analyst reviews Chrome extensions on 192.168.1.55 (test Hypothesis A).
2. Analyst checks browser history for each connection timestamp (Hypothesis C).
3. If Hypotheses A and C are ruled out → analyst approves targeted packet capture (Hypothesis B test).
4. Do NOT block the IP yet — the abuse score is low and this could be a false positive.

I am not requesting any automated actions. Awaiting analyst direction.
```

---

## Step 6: The Analyst's Role

The analyst reads the report and:
1. SSHes into the endpoint remotely.
2. Checks Chrome extension list: finds "Grammarly" installed.
3. Checks Grammarly's known IP ranges — 91.108.4.0/24 is a documented Grammarly CDN range.
4. Closes the investigation: false positive, no threat.

**Outcome:** GRAPHITE-21 was not present. The agent correctly identified ambiguity, generated testable hypotheses, and handed off to the human — who resolved in 8 minutes.

---

## Key Learning Points from This Walkthrough

| Observation | Principle |
|---|---|
| Agent generated no act calls in Complex domain | Domain classification governs agent permissions absolutely |
| Low abuse score did not clear the IP | Low score is not confirmation of legitimacy — only no detection |
| Agent produced 3 competing hypotheses | Hypothesis generation, not verdict issuance, is the correct output |
| Each hypothesis had a testable validation step | Agent's value in Complex domain is structuring the investigation, not deciding |
| Human resolved in 8 mins using agent's framework | Agent's hypothesis structure saved analyst from wasted enrichment cycles |
| False positive correctly avoided | Premature block of Grammarly CDN would have affected all users site-wide |

---

## Contrast with Example 01

| Dimension | Example 01 (Phishing) | Example 02 (APT Campaign) |
|---|---|---|
| complexity domain | Complicated → Clear | Complex |
| Agent verdict | High confidence: phishing | Cannot determine — hypotheses only |
| Agent act calls | Yes, with approval | None |
| Human role | Approver | Investigator |
| Resolution time | 61 seconds | 8 minutes |
| Threat confirmed? | Yes | No (false positive) |

Both outcomes represent **correct agent behavior**. The agent in Example 02 did not fail by refusing to act — it succeeded by correctly recognizing the limits of its reliability.

---

## Discussion

1. What would have happened if the agent had automatically blocked 91.108.4.0/24 (the IP range, not just the single IP)?
2. The analyst resolved Example 02 in 8 minutes manually. Should this be considered a failure of the AI agent? Why or why not?
3. How could a future version of the agent be improved to better handle this type of alert — without moving act permission into the Complex domain?
