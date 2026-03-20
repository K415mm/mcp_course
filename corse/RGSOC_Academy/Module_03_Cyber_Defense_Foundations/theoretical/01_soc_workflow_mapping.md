---
status: published
---

# 01 — SOC Workflow Mapping

> **Theoretical Block 1 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## 1.1 Why SOC Workflows Matter for MCP Design

Before you build a single MCP tool, you need to understand *when* in the security operations cycle that tool will be used. A tool built for the wrong phase creates either a bottleneck (if it requires human approval at a step that could be automated) or a risk (if it acts at a speed that outpaces analyst oversight).

This block maps the five standard SOC phases to the Sense-Think-Act loop and identifies exactly where MCP adds leverage — and where it should not.

---

## 1.2 The Five SOC Phases

Every security operations workflow cycles through these five phases:

```
DETECTION → TRIAGE → INVESTIGATION → CONTAINMENT → RECOVERY
```

They do not always happen in strict order. In practice, an investigation often reveals new indicators that trigger more detection, or a containment action reveals additional affected systems that require more investigation. The loop is iterative, not linear.

---

### Phase 1: Detection

**What happens:** Security tools (SIEM, EDR, email gateway, IDS/IPS) generate an alert. A raw signal is produced: "something happened that warrants attention."

**Data produced:**
- Alert ID and severity score
- Raw indicator: an IP, domain, hash, user, or process name
- Timestamp and source system
- Affected asset (hostname, user account, device ID)

**MCP role:** An MCP tool can be triggered automatically when a new alert arrives to perform initial data enrichment — pulling additional context without any analyst involvement.

**AUTOMATION LEVEL:** Varies by alert type (see practical exercises).

**Safe automation level:** Full — fetching alert details is a read-only Sense operation.

---

### Phase 2: Triage

**What happens:** The alert is prioritized. Analysts ask: "Is this real? How severe? Which assets are at risk?" Most SOC capacity is consumed here — tens of thousands of alerts per day, most of which are noise.

**Key triage questions:**
- Is this a known threat (hash match, known bad IP)?
- Has this been seen before (false positive history)?
- How many assets are potentially impacted?
- What is the business criticality of the affected system?

**MCP role:** This is where AI agents provide the most immediate ROI. An agent can:
1. Enrich all IOCs in the alert using threat intelligence APIs.
2. Query historical alert data for false positive patterns.
3. Look up asset criticality from a CMDB.
4. Produce a structured triage verdict in seconds.

**AUTOMATION LEVEL:** Clear for known threats. Complicated for mixed signals.

**Safe automation level:** High — all triage operations are Sense + Think. No Act calls required until the verdict is delivered.

---

### Phase 3: Investigation

**What happens:** A triaged alert that is confirmed or suspected as real requires deeper analysis. Analysts reconstruct the attack timeline, identify the scope of compromise, and determine root cause.

**Data needed:**
- Timeline of related events (before and after the initial alert)
- Lateral movement indicators (other hosts accessed by the affected user/system)
- Persistence mechanisms (scheduled tasks, registry keys, startup items)
- Command and control communication patterns

**MCP role:** Automated data gathering at scale. A human analyst would spend 30–90 minutes manually pulling logs across systems. An agent can run those queries in parallel in under 60 seconds, producing a structured evidence timeline.

**AUTOMATION LEVEL:** Complicated (known APT patterns) to Complex (novel attack).

**Safe automation level:** Medium — Sense operations are fully safe. Think outputs (hypotheses, timelines) require analyst review before any action is taken.

---

### Phase 4: Containment

**What happens:** Stop the threat from spreading. Isolate affected systems, block malicious IPs/domains, quarantine files, disable compromised accounts.

**Containment actions:**
- Network: block IP, block domain, isolate VLAN
- Endpoint: isolate machine from network, kill process, quarantine file
- Identity: disable user account, revoke tokens, force MFA re-enroll
- Email: quarantine email, remove from all inboxes, block sender domain

**MCP role:** Containment tools *can* be built as MCP Act tools — but they are the highest-risk tools in the stack.

**AUTOMATION LEVEL:** Clear domain actions can be automated (known bad IP + 98 abuse score + human approval). Complicated domain actions require analyst decision. Complex/Chaotic actions require senior analyst/SOC lead.

**Safe automation level:** Low — every containment action needs a human approval gate. No exceptions except in pre-defined Clear domain playbooks.

---

### Phase 5: Recovery

**What happens:** Restore affected systems to a known-good state. Document the incident. Update defenses to prevent recurrence. Close the alert.

**Recovery activities:**
- Reimaging impacted hosts
- Restoring from clean backups
- Patching the exploited vulnerability
- Updating firewall, EDR, and email gateway rules
- Documenting the full incident timeline for the SIEM
- Conducting a post-incident review

**MCP role:** Limited. Orchestration of recovery steps (pushing patches, triggering reimaging workflows) is possible but high-risk and rarely appropriate for beginner-level agent deployments.

**Safe automation level:** Low to None for agent-driven recovery. Agents can assist by generating incident documentation and drafting post-incident reports — but not by executing recovery steps autonomously.

---

## 1.3 MCP Leverage Map

Summary of where MCP agents add safe value across the five phases:

| Phase | MCP Sense Tools | MCP Think (LLM) | MCP Act Tools | Automation Level |
|---|---|---|---|---|
| Detection | ✅ Pull alert details | ✅ Initial categorization | ❌ None needed | Full (read-only) |
| Triage | ✅ Enrich IOCs, lookup history | ✅ Verdict + severity score | ⚠️ Auto-close only if Clear | High (with policy) |
| Investigation | ✅ Timeline queries, pivot | ✅ Timeline synthesis, hypotheses | ❌ No action | Medium (Sense only) |
| Containment | ⚠️ Limited reads | ✅ Recommendation draft | ⚠️ Human-approved gates only | Low (gated) |
| Recovery | ⚠️ CMDB reads | ✅ Report/doc generation | ❌ Not recommended | Very low |

---

## 1.4 The Alert-to-Action Pipeline

A complete alert-to-action pipeline with MCP looks like this:

```
SIEM fires alert
      │
      ▼
[MCP AGENT: TRIAGE BOT]
  Sense: pull alert details + enrich all IOCs
  Think: assess domain, generate verdict
  Output: structured brief + domain classification
      │
      ▼
Is domain CLEAR?
  YES → automatic containment (pre-approved policy)
  NO  → brief sent to analyst
      │
      ▼
ANALYST REVIEWS BRIEF
  Approves action → MCP executes approved tools
  Rejects/modifies → analyst takes manual action
      │
      ▼
AUDIT LOG: every step recorded in SIEM
```

---

## Key Takeaways

1. MCP adds the most value in the Detection and Triage phases (read-only, high volume).
2. Investigation benefits from MCP's data gathering speed; hypotheses need analyst validation.
3. Containment tools must always have approval gates — no exceptions in early deployments.
4. Recovery is the phase least suited for autonomous agent action.
5. Design your tools for a specific phase — a tool that works across all phases is a tool with no clear safety boundary.

---

## Discussion Questions

1. An SOC manager wants to automate the Investigation phase entirely. What is the strongest counter-argument?
2. For the Detection phase, what is the minimum enrichment set an agent should always perform before presenting a triage verdict?
3. Why does the "Recovery" phase have almost no safe autonomous action potential — even for Clear-domain incidents?

---

## Further Reading

- [Practical Cyber Defense via MCP.md](file:///d:/mcp_course/Practical%20Cyber%20Defense%20via%20MCP.md)
- [Governing_SOC_Agentic_AI.pdf](file:///d:/mcp_course/Governing_SOC_Agentic_AI.pdf)
- [Module_03_Content.md](file:///d:/mcp_course/corse/Module_03_Cyber_Defense_Foundations/Module_03_Content.md)
