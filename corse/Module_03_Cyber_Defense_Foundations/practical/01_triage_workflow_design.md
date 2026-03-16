---
status: published
---

# Practical 01 — Triage Workflow Design

> **Practical Block 1 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## Exercise Goal

Design a complete triage workflow for three different alert types, mapping each to the correct SOC phase, complexity domain, and MCP tool set. You will also write the agent prompt that would drive the workflow.

---

## Part A: Workflow Design Exercise

For each of the following three alerts, complete the design template below.

---

### Alert A: Phishing Email with Attachment

```
Alert: Email Security Gateway
Sender: invoice-dept@secure-billing-update.com
Recipient: accounts@yourcompany.org
Subject: "Urgent: Invoice #INV-29847 Overdue"
Attachment: invoice_29847.pdf
Attachment SHA256: b14a7b8059d9c055954c92d74c7ea48e2d027f3f
URL in body: https://secure-billing-update.com/pay/29847
Timestamp: 2026-03-10T00:01:00Z
```

**Design Template:**

```
WORKFLOW DESIGN — Alert A

1. SOC Phase at entry:        [Detection / Triage]
2. complexity domain (initial):  [Clear / Complicated / Complex / Chaotic]

3. MCP Tools to call (in order):
   Step 1: Tool name + why
   Step 2: Tool name + why
   Step 3: Tool name + why
   ...

4. Expected enrichment output fields:
   (list what fields you expect from each tool)

5. Decision rule:
   IF [condition] THEN [verdict + action]
   IF [condition] THEN [verdict + action]

6. Human approval required for: [which actions, if any]

7. Agent prompt you would write to trigger this workflow:
   (write 3–5 sentences instructing the agent what to do)
```

---

### Alert B: Failed Login Spike

```
Alert: Identity and Access Management (IAM)
Type: Brute Force Attempt
User account: admin@yourcompany.org
Source IP: 103.21.244.0
Failed attempts: 847 in 15 minutes
Current status: published NOT locked (threshold not reached)
Last successful login: 14 days ago from Ireland
Timestamp: 2026-03-10T00:03:00Z
```

**Use the same design template.**

Specific question for Alert B: At what point, if any, should the agent request permission to lock the account? What evidence threshold would you require first?

---

### Alert C: Unusual Outbound Traffic

```
Alert: Network Detection (NDR)
Source: 192.168.1.88 (internal workstation, user: dev-team)
Destination: 45.33.32.156:443
Traffic pattern: 2.1 GB transferred over the last 6 hours
Upload/Download ratio: 94% upload (highly asymmetric)
Time pattern: constant transfer, not correlated with business hours
Destination hostname: None resolved (IP only)
```

**Use the same design template.**

Specific question for Alert C: Is 2.1 GB upload alone enough to label this exfiltration? What additional data do you need before reaching any verdict, and what MCP tools produce that data?

---

## Part B: Cross-Alert Comparison

After completing all three:

1. Which alert required the most MCP tool calls before a verdict was possible? Why?
2. Which alert required the least tolerance for automated action? Why?
3. Design a **priority queue rule**: if all three alerts arrived simultaneously, in what order should the agent triage them, and why?

---

## Part C: Write the Multi-Alert Triage Prompt

Write a single agent prompt that instructs the AI agent to triage all three alerts simultaneously and produce a prioritized action brief. The prompt should:
- Reference all three alert IDs
- Specify which tools the agent has available
- Specify which actions require approval before execution
- Require the output to include complexity domain classification per alert

---

## Reference Design (Alert A — Instructor)

```
WORKFLOW DESIGN — Alert A (Reference)

1. SOC Phase: Triage (detection already fired)
2. AUTOMATION LEVEL: Complicated → likely Clear after enrichment

3. Tool call sequence:
   Step 1: enrich_domain("secure-billing-update.com") — age + detection score
   Step 2: enrich_hash("b14a7b8059d9c...") — is attachment known malware?
   Step 3: enrich_ip of resolved domain IP — is it a known bad actor?

4. Expected enrichment fields:
   - Domain: age_days, malicious_votes, registrar
   - Hash: malicious_detections, total_scans, known_families
   - IP: abuse_score, country, isp

5. Decision rule:
   IF hash detections > 20 AND domain age < 7 days → Clear domain → quarantine + block
   IF hash not found AND domain age < 7 days → Complicated → escalate to analyst
   IF all look clean → False positive (close with audit entry)

6. Human approval required for: quarantine email action, block domain at gateway

7. Agent prompt:
   "Triage this phishing alert. Enrich the sender domain, attachment hash, and
   any URLs in the email body using available CTI tools. Classify the decision-complexity
   domain based on your evidence. If Clear domain confirmed, draft a quarantine
   request for my approval. Do not take any containment action without my explicit
   approval. Produce a structured triage brief."
```
