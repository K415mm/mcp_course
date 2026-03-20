---
status: published
---

# Example 01 — Phishing Alert: Full Sense-Think-Act Walkthrough

> **Example Block 1 of 2 | Module 01: Agentic AI Foundations**

---

## Scenario Overview

This walkthrough traces a complete AI agent response to a phishing alert from the first signal to the final disposition. It is annotated at each step to show which part of the Sense-Think-Act loop is active and which complexity domain applies.

**Starting conditions:**
- SIEM fires alert `ALT-20260309-0042`.
- Alert type: "Suspicious email — potential phishing."
- The assigned MCP agent has access to: email header tools, URL tools, hash lookup, reputation APIs, domain WHOIS, and a quarantine tool.

---

## Step 0: Domain Classification (Human First)

Before the agent engages, the on-duty analyst performs a 10-second mental classification:

> "Phishing alert with a URL? This pattern is well-understood. SOC has documented playbooks for phishing. Cause and effect are well-known — I can classify this as **Complicated** until proven otherwise (since we don't know the URL yet). Agent can analyze. No act permission yet."

The analyst sets the agent scope: "Analyze this phishing alert. Do not take any containment action. Produce a triage brief."

---

## Step 1: SENSE — Alert Ingestion

**Tool called:** `get_alert_details("ALT-20260309-0042")`

**What the framework does:** Executes the tool call, queries the SIEM API, returns:

```json
{
  "alert_id": "ALT-20260309-0042",
  "type": "Phishing Email",
  "sender": "noreply@safe-secure-update.net",
  "recipient": "jsmith@company.com",
  "subject": "Action Required: Verify your account",
  "attachments": ["invoice_Q4.pdf"],
  "attachment_hash_sha256": "3395856ce81f2b7382dee72602f798b642f14d8",
  "urls_in_body": ["https://safe-secure-update.net/verify"],
  "timestamp": "2026-03-09T20:41:00Z",
  "status": "ok"
}
```

**Loop position:** Sense complete. LLM adds result to context. No action taken.

---

## Step 2: THINK — First Reasoning Cycle

The LLM processes the alert data. Its chain-of-thought reasoning (summarized):

> "I have a phishing alert. I have: a sender domain, a URL, and an attachment hash. Best next step: enrich the domain and the hash in parallel to gather threat intelligence before reaching any verdict."

**Output:** Two tool calls generated simultaneously.

---

## Step 3: SENSE — Parallel Enrichment

**Tool called (1):** `enrich_domain("safe-secure-update.net")`

**Result:**
```json
{
  "domain": "safe-secure-update.net",
  "creation_date": "2026-03-07",
  "age_days": 2,
  "registrar": "NameCheap (privacy-masked)",
  "malicious_votes": 18,
  "harmless_votes": 4,
  "status": "ok"
}
```

**Tool called (2):** `enrich_hash("3395856ce81f2b7382dee72602f798b642f14d8")`

**Result:**
```json
{
  "hash": "3395856ce81f2b7382dee72602f798b642f14d8",
  "malicious_detections": 41,
  "total_scans": 68,
  "known_families": ["FormBook", "AgentTesla"],
  "status": "ok"
}
```

**Loop position:** Both results appended to context. LLM moves to Think.

---

## Step 4: THINK — Second Reasoning Cycle

The LLM now has:
- Domain: 2 days old, 18 malicious votes → strong phishing indicator.
- Hash: 41/68 vendor detections → known malware (FormBook/AgentTesla).

Chain-of-thought:

> "Multiple independent corroborating indicators: newly registered domain, high malicious vote count, and hash matching a known credential-stealing malware family (FormBook is an information stealer). I'm now in the **Clear** domain — the cause-and-effect is self-evident: this is a known phishing attack with a known malware payload. I can recommend automated quarantine AND flag to the analyst that act permission can be safely extended."

**Output:** No more sense calls needed. Final response generated.

---

## Step 5: ACT — (Conditional on Agent Permission)

The agent was initially scoped to "analyze only." It produces the brief and flags the domain transition:

```
TRIAGE BRIEF — ALT-20260309-0042
Analyst: AI Agent (MCP Triage Server)
Time: 2026-03-09T20:41:47Z

VERDICT: HIGH CONFIDENCE PHISHING + KNOWN MALWARE PAYLOAD

Sender domain: safe-secure-update.net
  - Age: 2 days (strong phishing indicator)
  - 18/22 vendor votes: flagged

Attachment hash: 3395856c...
  - 41/68 vendor detections
  - Known as FormBook (credential stealer) and AgentTesla

complexity domain TRANSITION: Complicated → Clear
  Evidence is unambiguous. Multiple independent corroborating sources.

RECOMMENDED ACTIONS (all require your approval to execute):
  1. Quarantine the email for all recipients
  2. Block domain safe-secure-update.net at email gateway
  3. Notify jsmith@company.com of the phishing attempt
  4. Search for other recipients of emails from this domain in last 48h

I am requesting approval to execute actions 1–4.
```

**Analyst response:** "Approved. Execute items 1, 2, 3. Hold item 4 — I'll run that search myself."

**Tool called (4a):** `quarantine_email(alert_id="ALT-20260309-0042", approved_by="analyst_mf")`
**Tool called (4b):** `block_domain("safe-secure-update.net", approved_by="analyst_mf")`
**Tool called (4c):** `send_notification(user="jsmith@company.com", template="phishing_alert")`

---

## Step 6: AUDIT TRAIL

Every tool call logged to SIEM:

```json
[
  {"ts":"20:41:05Z","tool":"get_alert_details","input":"ALT-42","status":"ok"},
  {"ts":"20:41:09Z","tool":"enrich_domain","input":"safe-secure-update.net","status":"ok"},
  {"ts":"20:41:09Z","tool":"enrich_hash","input":"3395856c...","status":"ok"},
  {"ts":"20:41:58Z","tool":"quarantine_email","input":"ALT-42","approved_by":"analyst_mf","status":"ok"},
  {"ts":"20:41:59Z","tool":"block_domain","input":"safe-secure-update.net","approved_by":"analyst_mf","status":"ok"},
  {"ts":"20:42:01Z","tool":"send_notification","input":"jsmith","approved_by":"analyst_mf","status":"ok"}
]
```

Total elapsed time from alert to disposition: **61 seconds**.

---

## Key Learning Points from This Walkthrough

| Observation | Principle |
|---|---|
| Human classified the domain before agent engaged | Domain classification is always human-first |
| Initial scope was "analyze only" — no act permission | Act permission granted only after evidence review |
| Two enrichment tools called in parallel | Sense calls are safe to parallelize |
| LLM explicitly noted the domain transition | Make domain reasoning visible, not implicit |
| Every act tool included `approved_by` | Approval must be recorded in tool call, not just in chat |
| Analyst retained control of item 4 | Human can always override and take direct action |
| Total elapsed: 61 seconds | What would have been a 15-minute manual triage completed in ~1 minute |

---

## Discussion

1. At what point could the analyst have safely expanded act permission to "fully autonomous" for this alert type?
2. What would have changed if the hash had returned "not found in database" (no detections)?
3. The analyst chose to run the "search for other recipients" themselves. Is this the right call? When might you automate this search?
