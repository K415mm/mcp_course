---
status: published
---

# Practical 03 — Knowledge Check and Module Checklist

> **Practical Block 3 of 3 | Module 03: Cyber Defense Foundations for MCP Use**

---

## Section A: Knowledge Check Quiz

Answer each question in 2–4 sentences without looking at your notes.

**Q1.** Name the five SOC phases in order. For which two phases does MCP provide the highest ROI, and why?

**Q2.** A colleague says: "Phase 3 (Investigation) should be fully automated — it's just log queries." Provide the strongest counter-argument using complexity-based reasoning.

**Q3.** What is the difference between a Sense tool and an Act tool? Why does this distinction matter for which tools you build first?

**Q4.** Name the four cyber defense workstreams. For each, give one example MCP Sense tool (name and primary input parameter).

**Q5.** Why should a CTI tool return `malicious_detections: 41` and `total_scans: 68` rather than simply `verdict: "malicious"`?

**Q6.** What is "output normalization" and why is it a security concern for LLM-powered agents?

**Q7.** A REMnux MCP tool receives the file path `/home/analyst/samples/../../../etc/passwd`. What should happen, and what code pattern prevents this?

**Q8.** You are prioritizing which MCP server to build first. You have resources for one. CTI enrichment server or network analysis server — which do you build first? Justify using ROI, risk, and complexity arguments.

**Q9.** A Metasploit MCP tool offers `run_exploit(target, module)`. At what decision-complexity level is this tool appropriate, and what guardrails are absolutely required?

**Q10.** What is the difference between "passive" and "active" tools in the Kali Linux MCP server context? Give one example of each.

---

## Section B: Module 03 Operational Readiness Checklist

Before building any cyber defense MCP server, confirm:

### Workstream and Scope
- [ ] Your target workstream is defined (CTI / Hunting / Network / Malware).
- [ ] Every tool in scope has been classified as Sense or Act.
- [ ] No Act tool is included in your first deployment without an approval gate.
- [ ] All tools are scoped to a single SOC phase — not multi-phase.

### Tool Design Minimums
- [ ] All tools return `{"status": "ok"}` or `{"status": "error", "reason": "..."}`.
- [ ] No tool returns raw API text — all responses are structured dicts.
- [ ] Output normalization is applied before returning text to the LLM.
- [ ] All file-handling tools include path validation with `os.path.realpath()`.

### Data Sources and Permissions
- [ ] You have API keys for all external sources (stored as environment variables).
- [ ] You have access permissions to all internal data sources (SIEM, EDR, network tools).
- [ ] Rate limits for all APIs are documented and handled in code.
- [ ] You have confirmed which internal assets are in scope for active tools.

### Safety and Audit
- [ ] An audit log records every tool call with timestamp, input, and status.
- [ ] A process exists to halt the server if unexpected behavior is observed.
- [ ] Your first deployment is in a non-production (test) environment.

---

## Section C: Module 03 Exit Ticket

Write your answers before moving to Module 04:

1. **The biggest misconception I had about SOC automation before this module was...**
2. **The workstream where MCP would have had the most impact immediately in my environment is..., because...**
3. **The guardrail I would implement first in any real deployment is..., because...**
