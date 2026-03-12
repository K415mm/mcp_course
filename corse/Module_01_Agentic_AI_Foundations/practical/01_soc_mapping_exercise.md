---
status: draft
---

# Practical 01 — SOC Mapping: Classify Alerts on the Cynefin Framework

> **Practical Block 1 of 3 | Module 01: Agentic AI Foundations**

---

## Exercise Goal

Apply the Cynefin autonomy matrix to realistic SOC alert scenarios. For each alert, you will:
1. Identify the Cynefin domain.
2. Define the maximum safe AI agent role.
3. Design the human-AI interaction model for that alert type.

This is the most important practical skill in Module 1 — before you build any agent, you must know what it is and is not allowed to do.

---

## Background: The Decision Rule

Ask three questions in order:

1. **Is cause and effect immediately obvious?** → Yes → **Clear** (agent can act automatically)
2. **Can experts discover the cause through analysis?** → Yes → **Complicated** (agent assists, human decides)
3. **Does the future reliably resemble the past for this type of event?** → No → **Complex** or **Chaotic** (agent does not act)

If you cannot confidently answer question 1 or 2 with "yes," default to Complex.

---

## Part A: Individual Classification Exercise

For each of the following 10 alerts, assign:
- **Domain:** Clear / Complicated / Complex / Chaotic / Confusion
- **Agent Role:** Executor / Analyst / Hypothesis Generator / Data Triage / None
- **Can Agent Block/Isolate Automatically?** Yes / No
- **Required Human Action:** (describe)

---

### Alert 01: Known Ransomware Hash
```
Alert: File hash match detected.
File: C:\Users\jsmith\Downloads\invoice_Q4.exe
Hash SHA256: a3f9d1b2... → matches "Locky Ransomware" in threat intelligence feed.
Detection confidence: 100% (exact hash match).
```

**Hint:** Exact hash match = deterministic, known outcome, best-practice response documented.

---

### Alert 02: Unusual Login Country
```
Alert: Successful login from unusual location.
User: alice.manager@company.com
Location: Sofia, Bulgaria
Previous login: 3 hours ago from London, UK.
Travel time impossible.
```

**Hint:** Impossible travel is a well-defined, discoverable pattern.

---

### Alert 03: Encoded PowerShell
```
Alert: PowerShell executed with -EncodedCommand flag.
Host: WIN-DESK-07
Parent: excel.exe
Decoded command: (unavailable — requires decoding)
```

**Hint:** Parent process is suspicious, but encoded command content is unknown. Could be legitimate (some enterprise tools use encoded PS), could be malicious.

---

### Alert 04: Active Ransomware Spread
```
CRITICAL: Mass file encryption detected.
Affected hosts so far: 47 (growing).
File shares being encrypted in real time.
Backup systems unreachable.
Network team unresponsive.
```

**Hint:** Active crisis, multiple failures simultaneously, no stable pattern.

---

### Alert 05: Novel APT Indicators
```
Alert: Threat intelligence team reports a newly disclosed nation-state APT
campaign targeting financial sector. No attribution yet. Your environment
shows 3 connections to an IP range mentioned in the advisory (confidence: medium).
No prior detections of this actor in your logs.
```

---

### Alert 06: Port Scan from Internal Host
```
Alert: Internal host 192.168.1.88 performed an nmap-style scan of 
192.168.1.0/24 at 02:14 AM.
Host owner: IT Asset Management system (runs scheduled scans).
Last similar scan: 7 days ago, same host, same subnet.
```

---

### Alert 07: Privilege Escalation Attempt
```
Alert: Repeated sudo attempts detected on Linux server.
Host: prod-db-02 (production PostgreSQL server)
User: jenkins_svc (CI/CD service account)
4 failed sudo attempts in 2 minutes, then one success.
```

**Hint:** Requires investigating whether this is a CI/CD pipeline test, a configuration error, or malicious escalation.

---

### Alert 08: Unclassified Critical Incident
```
INCIDENT BRIDGE OPEN:
Multiple simultaneous reports arriving:
- Network: "something is wrong with routing"
- Security: "SIEM is showing anomalies"  
- Helpdesk: "users can't log in"
- Executive: "our website is down"
No clear technical root cause established yet.
```

**Hint:** No domain has been established yet. Multiple simultaneous signals, no coherent pattern.

---

### Alert 09: Suspicious DNS Beaconing
```
Alert: Host 192.168.1.44 making DNS queries every 60 seconds to:
a1b2c3d4e5.suspiciousdomain.ru
f6g7h8i9j0.suspiciousdomain.ru
k1l2m3n4o5.suspiciousdomain.ru
(Pattern: different subdomain, same base domain, exact 60s interval)
```

---

### Alert 10: Insider with Mixed Signals
```
Alert: User bob.engineer has:
- Downloaded 2 GB of design documents over 6 months (slightly above average)
- Submitted resignation letter 3 weeks ago (HR flag)
- Last access review showed all permissions are legitimate for his role
- No malware detected on his endpoint
- Access to 3 sensitive systems above his peer group median
```

---

## Part A: Answer Template

Copy and complete for each alert:

```
Alert 01:
  Domain:          [Clear / Complicated / Complex / Chaotic / Confusion]
  Agent Role:      [Executor / Analyst / Hypothesis Generator / Data Triage / None]
  Automated Act:   [Yes / No]
  Human Action:    [describe]
  Reasoning:       [why did you choose this domain?]
```

---

## Part B: Comparative Discussion

After completing Part A individually, compare answers with a partner or the group:

1. Which alert generated the most disagreement? Why?
2. How would your answers change if the alert came in during a major product launch vs. on a normal Tuesday?
3. Look at your "Automated Act: Yes" answers. For each one — what would be the worst-case consequence if the agent's verdict was wrong?

---

## Part C: Build an Alert Classification SOP

Based on the Cynefin matrix, write a 5-step Standard Operating Procedure for classifying a new alert into the correct Cynefin domain at the start of a triage process. Each step should be a yes/no question the analyst asks before engaging the AI agent.

---

## Reference Answers (Instructor Guide)

| Alert | Domain | Agent Role | Auto Act? |
|---|---|---|---|
| 01 | Clear | Executor | ✅ Yes |
| 02 | Clear | Executor | ✅ Yes (block session, notify user) |
| 03 | Complicated | Analyst | ❌ No |
| 04 | Chaotic | Data Triage | ❌ No |
| 05 | Complex | Hypothesis Generator | ❌ No |
| 06 | Clear | Executor (resolve: benign) | ✅ Yes (auto-close) |
| 07 | Complicated | Analyst | ❌ No |
| 08 | Confusion | None | ❌ No |
| 09 | Complicated | Analyst | ❌ No |
| 10 | Complex | Hypothesis Generator | ❌ No |
