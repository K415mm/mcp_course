---
status: published
---

# Module 03 — Google NotebookLM Slides Generation Prompt

## How to Use

1. Open [NotebookLM](https://notebooklm.google.com/) → create notebook: `Module 03 — Cyber Defense Foundations`.
2. Upload all files from `Module_03_Cyber_Defense_Foundations/` as sources.
3. Paste the **master prompt below** into the Studio/Chat panel.
4. Only change the four `[BRACKETED]` fields for each module run.

---

## MASTER SLIDES PROMPT

```
You are a professional instructional designer creating a slide deck for a technical cybersecurity course. Follow the EXACT design system defined below on every slide. This ensures consistent branding across all 8 modules.

═══════════════════════════════════════════════════
COURSE IDENTITY
═══════════════════════════════════════════════════
Course Name: Beginner's Guide to Agentic AI and MCP in Cyber Defense
Audience: Security analysts, SOC engineers, threat hunters (beginner–intermediate)
Tone: Authoritative, accessible, practical. Explain acronyms on first use.
This is Module: [03]
Module Title: [Cyber Defense Foundations for MCP Use]
Core Topics (3): [SOC Workflow Mapping | The Four Cyber Defense Workstreams | Real-World MCP Security Servers]

═══════════════════════════════════════════════════
DESIGN SYSTEM — APPLY TO EVERY SLIDE (DO NOT CHANGE)
═══════════════════════════════════════════════════

COLOR PALETTE:
- Background: Deep navy #0D1B2A (primary), charcoal #1A2332 (section dividers)
- Primary accent: Electric blue #00A8FF
- Secondary accent: Cyan #00E5CC
- Warning/Act tools: Amber #FFB347
- Safe/Sense tools: Emerald #00D68F
- Text primary: White #FFFFFF
- Text secondary: Light grey #B0BEC5
- Code blocks: Dark panel #0A1628, cyan text #00E5CC

TYPOGRAPHY: Inter/Outfit Bold (titles ALL CAPS), Inter/Roboto Regular (body), JetBrains Mono (code)
BRANDING: Top-left every slide: 🛡 MCP-CD | Bottom-right: M03/S## | Bottom-left: module title (small caps, grey)
MAX 5 BULLETS PER SLIDE. Each bullet ≤ 12 words. Diagram slides = zero bullets.
Every slide has ONE primary takeaway in a callout box or highlighted text.

═══════════════════════════════════════════════════
SLIDE STRUCTURE — GENERATE IN THIS ORDER
═══════════════════════════════════════════════════

SLIDE 01 — TITLE SLIDE
Title: MODULE 03 — CYBER DEFENSE FOUNDATIONS FOR MCP USE
Visual: Shield icon with four branching nodes (representing the 4 workstreams), electric blue on navy
Sub: "Where AI agents plug into your SOC"
Learning objectives (5, one per bullet):
  - Map the 5 SOC phases to MCP tool zones
  - Name the 4 cyber defense workstreams
  - Distinguish Sense vs. Act tools in each workstream
  - Evaluate output normalization requirements
  - Identify which MCP servers to build first

SLIDE 02 — THE ALERT VOLUME PROBLEM
Content: One large stat block (make dramatic):
  - Average enterprise SOC: 10,000+ alerts/day
  - Average analyst capacity: 20–30 alerts/day
  - Gap: 99%+ of alerts cannot be manually triaged
Visual: Simple bar chart or icon grid showing the scale gap
Takeaway: "MCP agents don't replace analysts — they triage the 99% so analysts focus on the 1%."

SLIDE 03 — THE FIVE SOC PHASES (Overview)
Content: Five boxes in a horizontal flow with arrows:
  DETECTION → TRIAGE → INVESTIGATION → CONTAINMENT → RECOVERY
  Each box: phase name + one-word MCP role:
  - Detection: AUTO-ENRICH
  - Triage: VERDICT
  - Investigation: GATHER
  - Containment: GATED
  - Recovery: DOCUMENT
No bullets — diagram only.
Takeway: "MCP adds leverage in Triage and Investigation. Recovery stays human."

SLIDE 04 — PHASE DETAIL: TRIAGE
Content:
Left: The triage question list (3 bullets max):
  - Is the indicator known? (hash/IP lookup)
  - How many assets are affected?
  - What is the business criticality of the asset?
Right: Simple funnel diagram — 10,000 alerts → analyst reviews 10
Callout: "Triage is where MCP ROI is highest — all Sense tools, no approval needed."

SLIDE 05 — MCP LEVERAGE MAP (Table)
Full-slide table (5 rows × 5 cols):
Columns: Phase | Sense Tools | Think (LLM) | Act Tools | Automation Level
Rows: Detection / Triage / Investigation / Containment / Recovery
Row colors: alternating navy/charcoal
Header: electric blue
Act column: use ❌/⚠️/✅ symbols

SLIDE 06 — THE FOUR WORKSTREAMS (Overview)
Content: 2×2 grid — one workstream per quadrant
  - Top-left (cyan): CTI — Who is attacking and how?
  - Top-right (blue): Threat Hunting — What has evaded detection?
  - Bottom-left (emerald): Network Analysis — What traffic is anomalous?
  - Bottom-right (amber): Malware Analysis — What does this file do?
No bullets — let the icons and labels speak.
Takeaway: "Each workstream has its own tool set, data sources, and risk profile."

SLIDE 07 — CYBER THREAT INTELLIGENCE (CTI)
Content:
Left: 3 bullets on the CTI workflow: Ingest → Normalize → Enrich → Correlate → Brief
Right: Table — 3 CTI MCP tools: enrich_ip, enrich_domain, enrich_hash + their source APIs
Callout box: "CTI tools are almost always Read-only. Start here."
Color: Cyan accent throughout

SLIDE 08 — THREAT HUNTING
Content:
Left: The hypothesis structure (3 lines):
  - Threat basis (TTP or anomaly)
  - Testable query
  - Data source + verdict criteria
Right: "WHO GENERATES THE HYPOTHESIS?" callout in amber:
  → Human analyst — always. Agent queries, not decides.
Takeaway: "In Complex domain — analysts hypothesize, agents gather evidence."

SLIDE 09 — NETWORK ANALYSIS
Content:
Left: Passive vs Active tool distinction table (3 rows):
  - Passive (WHOIS, passive DNS) → No approval
  - Semi-active (SSL cert check) → Optional
  - Active (port scan, brute force) → Required
Right: Warning box in amber: "Active tools on unauthorized targets = legal violation."
Takeaway: "Network tools require the clearest authorization boundaries in your stack."

SLIDE 10 — MALWARE ANALYSIS
Content:
Left: Static vs Dynamic (2 columns):
  Static: no execution, safe → file type, hash, strings, YARA, PE headers
  Dynamic: sandbox only → file writes, API calls, network traffic
Right: Safety rule box: "NEVER analyze unvetted files on production host."
Takeaway: "Start static, go dynamic only with isolated infrastructure."

SLIDE 11 — OUTPUT NORMALIZATION (Deep Focus)
Content:
Two-column table (before/after):
| Raw Output | Normalized for LLM |
| "malicious file" | "flagged by vendor" |
| "dangerous API" | "commonly scrutinized API" |
| "suspicious traffic" | "notable traffic pattern" |
| "HIGH RISK" | "elevated indicator count" |
Callout: "Loaded terms cause LLMs to over-escalate. Normalize before returning to agent."
Design: amber warning accent on left column, emerald on right

SLIDE 12 — REAL TOOLS: REMnux EXAMPLE (Code Slide)
Content:
Left: 3 bullets on REMnux design patterns:
  - Path validation (os.path.realpath)
  - Output cap ([:100] on strings)
  - Subprocess timeout (timeout=30)
Right: Code block showing the run_yara_scan tool signature and path validation
Code background: dark panel, cyan text

SLIDE 13 — SENSE vs ACT — THE CORE SAFETY LINE
Full-slide diagram:
Left half (emerald): SENSE TOOLS
  - Read-only
  - No state change
  - Safe to automate
  - Examples: enrich_ip, get_alert_details, extract_strings
Right half (amber): ACT TOOLS
  - State-changing
  - May be irreversible
  - Require approval gate
  - Examples: quarantine_file, block_ip, disable_user

SLIDE 14 — WHICH SERVER FIRST?
Priority list (vertical numbered list):
  1. CTI Enrichment (Sense-only, max ROI, easiest)
  2. SIEM/Log Query (Sense-only, core investigation)
  3. Threat Hunting (semi-automated, analyst-led)
  4. Network Analysis (requires baseline first)
  5. Malware Analysis (requires sandbox infrastructure)
  6. Pentest/Active (authorized scope only — last)
Takeaway: "Build in this order. Don't skip to step 5 before you've validated step 1."

SLIDE 15 — WALKTHROUGH RECAP: CTI CHAIN
Brief timeline (horizontal flow):
Alert (23:47) → enrich_ip → get_host_events → get_connections → Verdict (23:51) → Analyst approval → Block + Isolate
Key metric: "4 minutes vs 15 minutes manual"
Callout: "Not faster because AI is smarter — faster because Sense calls run in parallel."

SLIDE 16 — MODULE SUMMARY
5 takeaways (one per learning objective):
  - 5 SOC phases: Detection and Triage = highest MCP ROI
  - 4 workstreams: each has its own tools, data, and risk profile
  - Sense = always safe. Act = always gated.
  - Output normalization prevents LLM over-escalation
  - Build CTI enrichment server first — maximum ROI, minimum risk

SLIDE 17 — WHAT'S NEXT: MODULE 04
Preview card:
Module 04 — Python Essentials for MCP
You will learn:
  - Write functions with type hints
  - Handle errors without crashing
  - Parse API responses safely
  - Store secrets in environment variables
CTA: "After Module 04, you will write your first working tool. It's closer than you think."

═══════════════════════════════════════════════════
GENERATION RULES (SAME FOR ALL MODULES)
═══════════════════════════════════════════════════

Output format per slide:
--- SLIDE [##] ---
TITLE: [ALL CAPS]
LAYOUT: [visual description]
CONTENT: [bullets, table, diagram, or code]
DESIGN_NOTES: [colors, icons, emphasis]
SPEAKER_NOTES: [2–3 sentence instructor script — MANDATORY on slides 05, 08, 11, 13]

Never use "suspicious", "dangerous", "malicious" without qualification.
Code blocks: JetBrains Mono, dark panel #0A1628, cyan text, line numbers.
All tables: electric blue header, striped navy/charcoal rows.
After all slides: output a SLIDE DECK INDEX table (Slide# | Title | Type | Source Block).

SOURCE MATERIAL: Generate ONLY from uploaded files. Write [VERIFY] in speaker notes if uncertain.
```

---

## Reuse Guide — Key Fields to Change Per Module

| Module | Title | Topics | Slides |
|---|---|---|---|
| 01 | Agentic AI Foundations | 5 | 20 |
| 02 | MCP Fundamentals | 4 | 18 |
| **03** | **Cyber Defense Foundations** | **3** | **17** |
| 04 | Python Essentials for MCP | 5 | 18 |
| 05 | Build MCP Servers | 4 | 18 |
| 06 | Build MCP Clients | 3 | 16 |
| 07 | Integrate MCP into AI Workspaces | 4 | 16 |
| 08 | Policy, Guardrails, Safe Autonomy | 4 | 18 |
