---
status: draft
---

# Practical 02 — Designing Autonomy Guardrails

> **Practical Block 2 of 3 | Module 01: Agentic AI Foundations**

---

## Exercise Goal

Design the guardrail architecture for a proposed SOC AI agent deployment. You will identify which actions require automatic, assisted, or human-only handling, and write the policy rules that enforce those boundaries.

---

## Background: The Guardrail Stack

A guardrail is any mechanism that prevents an AI agent from taking a harmful action. Guardrails operate at four levels (covered in full in Module 8):

1. **Tool-level:** validation and markers built into each tool function.
2. **Server-level:** rate limits, auth, and audit logging at the MCP server.
3. **Workflow-level:** approval gates and evidence thresholds in the client.
4. **Organizational-level:** policies, human roles, and review cadences.

This exercise focuses on Levels 1 and 3 — the tool-level and workflow-level guardrails most relevant to Module 1 concepts.

---

## Exercise 2.1 — The Action Inventory

You are deploying an AI agent for your SOC. The agent will have access to the following 12 potential actions. For each action:
- Classify it as **Sense** (read-only) or **Act** (state-changing).
- Assign the minimum Cynefin domain at which this action is ever safe to automate.
- Write the **guardrail level** for that action (None / Tool marker / Approval gate / Forbidden).

| Action | Sense or Act | Min Safe Domain | Guardrail Level |
|---|---|---|---|
| Query SIEM for alert details | ? | ? | ? |
| Enrich IP via AbuseIPDB | ? | ? | ? |
| Look up hash on VirusTotal | ? | ? | ? |
| Get WHOIS for a domain | ? | ? | ? |
| Read a pcap file and extract hosts | ? | ? | ? |
| Block IP on perimeter firewall | ? | ? | ? |
| Quarantine a file to an isolated directory | ? | ? | ? |
| Isolate an endpoint via EDR | ? | ? | ? |
| Close an alert ticket in the SIEM | ? | ? | ? |
| Create a P1 incident ticket | ? | ? | ? |
| Send notification email to affected user | ? | ? | ? |
| Disable a user account in Active Directory | ? | ? | ? |

---

## Exercise 2.2 — Write the Guardrail Policy Statements

For each action you marked as requiring an **Approval gate**, write a policy statement in the following format:

```
GUARDRAIL POLICY — [Action Name]

Trigger condition: [When would the agent want to call this action?]
Evidence required before requesting approval: [What enrichment must exist first?]
Approver required: [L1 analyst / L2 analyst / SOC lead / CISO]
Approval format: [Explicit "yes" in chat / Written justification / Out-of-band approval]
Rollback capability: [Yes — via [tool name] / No — irreversible]
SIEM audit record: [What fields must be logged?]
```

Complete this for at least 3 actions from Exercise 2.1.

---

## Exercise 2.3 — The Agent Safety Brief

You are presenting your guardrail design to the SOC manager before going live. Write a one-page (maximum 400 words) **Agent Safety Brief** that covers:

1. What the agent can do automatically (and why it is safe).
2. What the agent will request approval for (and how).
3. What the agent is never permitted to do (and what happens instead).
4. How every agent action is audited.
5. The "off-switch" — how can any analyst immediately stop the agent?

---

## Exercise 2.4 — Failure Scenario Analysis

For each of the following failure scenarios, identify:
- Which guardrail level failed.
- What would have prevented it.

### Scenario A
The AI agent was enriching IOCs from a phishing alert. It identified an IP with an AbuseIPDB score of 92. It then called `block_ip("185.220.101.45")` automatically. The IP turned out to be a shared CDN exit node — blocking it disrupted traffic for 150 internal users.

### Scenario B
The AI agent spent 47 tool-call iterations enriching the same 3 IOCs repeatedly, consuming the entire rate limit budget for the VirusTotal API for the day. The root cause: the agent lost track of which IPs it had already enriched (context window trimmed early history).

### Scenario C
A SOC analyst said in the chat: "The IP looks bad, go ahead." The agent interpreted this as an approval to block the IP, even though the analyst only meant "proceed with further investigation." The IP was blocked without a formal approval record.

---

## Exercise 2.5 — Design a Minimal Approval Gate

Write the approval gate logic for an `isolate_endpoint` tool. The gate must:
1. Emit a structured approval request (JSON format) with: the endpoint name, evidence summary (max 3 bullet points), risk level, and approver requirement.
2. Only proceed if the response includes an explicit string "APPROVED" and an analyst identifier.
3. Log the approval with timestamp, approver ID, and tool arguments.
4. Reject any call where the evidence summary is empty.

you may write this as pseudocode:

```python
def approval_gate_isolate(endpoint: str, evidence: list[str], approver: str) -> dict:
    # Your approval gate logic here
    ...
```

---

## Reference Discussion Points (Instructor)

**Exercise 2.1 Expected Classifications:**
- All "Query/Enrich/Get/Read" actions → Sense / Clear domain / No guardrail needed.
- Block IP → Act / Clear domain minimum / Approval gate (can auto-approve in Clear with strong evidence, but gate recommended for first deployments).
- Quarantine file → Act / Clear domain minimum / Approval gate.
- Isolate endpoint → Act / Complicated minimum / Approval gate + L2 required.
- Close ticket → Act / Clear / Tool marker (low risk, but audit log required).
- Create P1 ticket → Act / Clear / No gate (low blast radius — creating a ticket).
- Send notification → Act / Clear / Tool marker.
- Disable AD account → Act / Chaotic forbidden / Complicated: approval gate + SOC lead.

**Scenario Analysis:**
- A: Workflow-level (no approval gate for Act call). Prevention: approval gate + evidence threshold.
- B: Workflow-level (no state tracking). Prevention: explicit "already enriched" tracking in system prompt or framework state.
- C: Organizational-level (informal approval channel). Prevention: structured approval format requirement (explicit JSON, not natural language).
