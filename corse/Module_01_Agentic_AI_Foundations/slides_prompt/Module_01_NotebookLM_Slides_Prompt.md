---
status: draft
---

# Module 01 — Google NotebookLM Slides Generation Prompt

## How to Use This Prompt

1. Open [NotebookLM](https://notebooklm.google.com/).
2. Create a new notebook named `Module 01 — Agentic AI Foundations`.
3. Upload all source files from `Module_01_Agentic_AI_Foundations/` as sources (theoretical files, examples, the main Module_01_Content.md).
4. Open the **Studio** panel → **Slides** (or the Chat).
5. Copy and paste **the full prompt below** into the chat/studio input.
6. Attach relevant source files as context.
7. Generate. Use the **regenerate** button if needed — the design system constraints ensure consistency across runs.

> **Reuse this same prompt for every module.** Only change the bracketed `[MODULE_NUMBER]`, `[MODULE_TITLE]`, `[TOPIC_COUNT]`, and `[TOPIC_LIST]` fields. The brand, color scheme, layout rules, and tone stay identical — every module's deck will look and feel like the same course.

---

## MASTER SLIDES PROMPT

```
You are a professional instructional designer and presentation specialist creating a slide deck for a technical cybersecurity course. Your task is to produce a complete slide deck for the module specified below, using the exact design system defined in this prompt. You must follow every constraint precisely to ensure consistent branding across all modules in the course.

═══════════════════════════════════════════════════
COURSE IDENTITY
═══════════════════════════════════════════════════
Course Name: Beginner's Guide to Agentic AI and MCP in Cyber Defense
Target Audience: Security analysts, SOC engineers, and threat hunters (beginners to MCP/agentic AI)
Tone: Authoritative but accessible. No unnecessary jargon. Explain acronyms on first use.
This is Module: [01]
Module Title: [Agentic AI Foundations for Cyber Defense]
Number of core topics in this module: [5]
Topics: [What Is an AI Agent | The Sense-Think-Act Loop | Memory Goals and State | Core Characteristics and Orchestration | Safe Autonomy: The Cynefin Lens]

═══════════════════════════════════════════════════
DESIGN SYSTEM — APPLY TO EVERY SLIDE
═══════════════════════════════════════════════════

COLOR PALETTE (use these exact color roles):
- Background: Deep navy #0D1B2A (primary) or charcoal #1A2332 (section dividers)
- Primary accent: Electric blue #00A8FF
- Secondary accent: Cyan #00E5CC
- Warning / destructive action indicator: Amber #FFB347
- Success / safe/read-only indicator: Emerald #00D68F
- Text primary: White #FFFFFF
- Text secondary: Light grey #B0BEC5
- Code blocks: Dark panel #0A1628 with cyan text #00E5CC

TYPOGRAPHY:
- Title font: Inter or Outfit — Bold, ALL CAPS for slide titles
- Body font: Inter or Roboto — Regular weight, 16–18pt
- Code font: JetBrains Mono or Source Code Pro
- No decorative or serif fonts

LOGO / BRANDING:
- Top-left corner of every slide: small white shield icon (🛡) + course acronym "MCP-CD"
- Bottom-right of every slide: slide number in format "M01 / S##"
- Bottom-left of every slide: module title in small caps, text-secondary color

VISUAL LANGUAGE:
- Use minimal, flat-style icons (not 3D, not illustrations)
- Diagrams use the electric blue / cyan palette — no red/green traffic light color coding except for the Cynefin matrix
- Arrows: thin, white or cyan
- Tables: striped rows (navy/charcoal), header row in electric blue, text white
- Code blocks: rounded corners, dark panel background, line numbers shown

LAYOUT RULES:
- Each slide has ONE primary message (the "takeaway" in large text at the bottom or highlighted in a callout box)
- No slide has more than 5 bullet points. Each bullet is maximum 12 words.
- Diagram slides have zero or minimal bullets — let the diagram speak
- Every definition slide uses the format: [TERM] in electric blue, definition in white

═══════════════════════════════════════════════════
SLIDE STRUCTURE — GENERATE IN THIS ORDER
═══════════════════════════════════════════════════

SLIDE 01 — TITLE SLIDE
Content:
- Large: Module [01] — [AGENTIC AI FOUNDATIONS FOR CYBER DEFENSE]
- Sub: Course: Beginner's Guide to Agentic AI and MCP in Cyber Defense
- Visual: a glowing shield at center with interconnected nodes radiating outward (representing AI tools), electric blue on navy background
- Bottom: Learning outcomes preview (1 line each, max 5)
- Mood: bold, confident, technical

SLIDE 02 — THE PROBLEM THIS MODULE SOLVES
Content:
- Headline: "SOC Alert Volume Has Outpaced Human Capacity"
- Left column (text): 3 bullet points — the analyst alert overload problem
- Right column (visual): simple bar chart or icon grid showing "alerts received" vs "alerts triaged"
- Takeaway box (bottom): "AI agents are not about replacing analysts — they're about sustainable scale."

SLIDE 03 — WHAT IS AN AI AGENT? (Definition)
Content:
- Definition slide format
- [AI AGENT]: "An autonomous system that perceives, reasons, acts, and iterates to achieve a goal."
- Comparison table: 3 columns — Chatbot / Traditional Automation / AI Agent
- Rows: multi-step, adapts, uses tools, human per step, failure mode
- Key insight callout box: "Agents fail differently — confidently and unpredictably."

SLIDE 04 — THE FIVE CHARACTERISTICS
Content:
- 5 icon blocks in a horizontal row or pentagon diagram
- Each block: icon + label + 1-line description
  1. Autonomy — operates without per-step human input
  2. Perception — gathers data via tool calls
  3. Reasoning — LLM processes context, decides next action
  4. Action — tool calls modify the world
  5. Adaptation — updates conclusions as new data arrives
- Color coding: Autonomy (#00A8FF), Perception (#00E5CC), Reasoning (#B0BEC5), Action (#FFB347), Adaptation (#00D68F)

SLIDE 05 — THE SENSE-THINK-ACT LOOP (Diagram)
Content:
- Full-slide diagram: circular loop with three nodes
  - SENSE (cyan #00E5CC): "MCP tools read — read-only, always safe"
  - THINK (electric blue #00A8FF): "LLM processes — probabilistic, not cognition"
  - ACT (amber #FFB347): "MCP tools write — state-changing, high risk"
- Arrows connecting nodes clockwise, labeled "tool result" and "tool call"
- Bottom: key asymmetry highlighted: "SENSE errors = recoverable | ACT errors = may be irreversible"
- No bullets — diagram only

SLIDE 06 — WHAT THE LLM ACTUALLY DOES (Demystifying Think)
Content:
- Title: "The LLM Does Not Think — It Pattern Matches"
- Left: two columns "What analysts think the AI does" vs "What the AI actually does"
- Right: simple brain icon crossed out → replaced by a pattern-matching node diagram
- Takeaway: "LLM confidence ≠ LLM accuracy. Especially in novel situations."

SLIDE 07 — MEMORY IN AGENTS
Content:
- 3 memory types in a stacked or layered diagram:
  - Layer 1 (session): Episodic — conversation history, cleared each session
  - Layer 2 (persistent): Semantic — vector DB, survives across sessions
  - Layer 3 (built-in): Procedural — tool docstrings, the agent's "muscle memory"
- Right panel: context window illustration — a fixed-size box filling up as tool results accumulate
- Callout: "A tool that returns 10,000 lines of raw log text may consume the entire memory budget."

SLIDE 08 — THE CYNEFIN FRAMEWORK (Overview)
Content:
- Full-slide 2x2 matrix diagram:
  - Top-left: COMPLICATED (blue) — Expert domain
  - Top-right: COMPLEX (purple/navy) — Emergent domain
  - Bottom-left: CLEAR (emerald) — Best practice domain
  - Bottom-right: CHAOTIC (amber) — Crisis domain
  - Center: CONFUSION (grey) — Most dangerous
- Each quadrant: domain name + 1-word agent role
- No bullets — diagram only

SLIDE 09 — CLEAR DOMAIN: THE EXECUTOR
Content:
- Left: Cynefin matrix with CLEAR quadrant highlighted in bright emerald
- Right (3 bullets):
  - Cause and effect are self-evident
  - Agent role: EXECUTOR — full autonomous action permitted
  - Example: Known malware hash → automatic quarantine
- Callout box: "Autonomous action risk: VERY LOW ✅"
- Color: emerald accent throughout

SLIDE 10 — COMPLICATED DOMAIN: THE ANALYST
Content:
- Same layout, COMPLICATED quadrant highlighted in electric blue
- Right (3 bullets):
  - Cause discoverable through expert analysis
  - Agent role: ANALYST — gathers data, presents recommendation
  - Human validates before any action
- Callout box: "Autonomous action risk: LOW ⚠️ (analysis only)"

SLIDE 11 — COMPLEX DOMAIN: THE PROBER
Content:
- Same layout, COMPLEX quadrant highlighted in purple/dark blue
- Right (3 bullets):
  - Cause only understood in retrospect — patterns unreliable
  - Agent role: HYPOTHESIS GENERATOR — proposes, never decides
  - Every hypothesis must have a human-defined test
- Callout box: "Autonomous action risk: HIGH 🚫 DO NOT PERMIT"

SLIDE 12 — CHAOTIC DOMAIN: DATA TRIAGE ONLY
Content:
- Same layout, CHAOTIC quadrant highlighted in amber
- Right (3 bullets):
  - Active crisis — no stable patterns, act-first environment
  - Agent role: DATA TRIAGE — summarizes logs, drafts communications
  - Human commands all action — agent only multiplies response capacity
- Callout box: "Autonomous action risk: CRITICAL 🔴 PROHIBIT ENTIRELY"

SLIDE 13 — THE CONFUSION STATE (Special Warning Slide)
Content:
- Full-slide design: dark background, Cynefin center (confusion) — red/grey vortex icon
- Title in amber: "The Most Dangerous State for AI Agents"
- 3 points:
  - LLMs cannot recognize confusion — they force patterns on ambiguous situations
  - AI will mis-classify Confusion as Complicated and begin acting with false confidence
  - Human leadership must classify the domain BEFORE AI engagement begins
- Takeaway box: "RULE: No agent actions permitted until a human has classified the incident domain."

SLIDE 14 — THE AUTONOMY MATRIX SUMMARY TABLE
Content:
- Full-slide table with 5 columns: Domain | Agent Role | Sense | Act | Human Role
- One row per domain (Clear / Complicated / Complex / Chaotic / Confusion)
- Color-code rows: emerald / blue / purple / amber / grey
- Act column shows: ✅ Auto / ⚠️ Approval / 🚫 Forbidden / 🔴 Prohibited / ⛔ N/A

SLIDE 15 — PRACTICAL: CLASSIFYING A REAL ALERT
Content:
- Split slide: left = real alert text (formatted as a terminal/SIEM output block)
- Right = analyst decision tree (3 yes/no questions → domain classification)
- Alert used: Phishing email with known hash match (answer: Clear → auto-quarantine)
- Bottom: "Apply this decision tree to every alert before engaging an agent."

SLIDE 16 — PRACTICAL: WHERE THE AGENT STOPS (APT Example)
Content:
- Brief version of Example 02 scenario
- Diagram: agent generates 3 hypotheses instead of a verdict → hands off to human
- Key message: "An agent that refuses to act in Complex/Chaotic domains is not failing — it is working correctly."
- Compare: Example 01 (61 sec, auto-resolved) vs Example 02 (8 min, human resolved) — both correct outcomes

SLIDE 17 — KEY GUARDRAILS (Preview of Module 8)
Content:
- 4 boxes (horizontal row): Tool-level | Server-level | Workflow-level | Organizational
- Each box: 1-line description of what that level controls
- Callout: "These will be covered in full in Module 8. For now: every Act tool needs an approval gate."
- Color: each box in the 4 accent colors

SLIDE 18 — KNOWLEDGE CHECK
Content:
- 5 questions in a clean layout (numbered list, electric blue numbers)
- Space below each for written answer (if printed) or discussion prompt (if live)
- Questions from the module's knowledge check file

SLIDE 19 — MODULE SUMMARY
Content:
- 5 key takeaways in a clean bulleted list (one per learning objective)
- Each takeaway prefaced with a relevant icon
- Bottom progress bar: "Module 01 of 08 — COMPLETE"

SLIDE 20 — WHAT'S NEXT: MODULE 02
Content:
- Preview card for Module 02: MCP Fundamentals and Architecture
- 3 questions Module 02 will answer (create from module 02 content)
- CTA: "Build your first MCP server in Module 05"
- Bottom: reading list from Module 01 source files

═══════════════════════════════════════════════════
GENERATION RULES — STRICTLY FOLLOW
═══════════════════════════════════════════════════

1. Generate slides in ORDER — do not skip or reorder.
2. Each slide output format:
   --- SLIDE [##] ---
   TITLE: [slide title in ALL CAPS]
   LAYOUT: [describe the visual layout]
   CONTENT: [bullet points, table, diagram description, or code]
   DESIGN_NOTES: [specific colors, icons, emphasis to apply]
   SPEAKER_NOTES: [2–3 sentence script for the instructor]

3. NEVER use the words: "suspicious", "malicious", "dangerous" in learner-facing slides without qualification — use neutral framing consistent with the course's output normalization philosophy.

4. Code blocks must use JetBrains Mono, dark panel background, cyan text. Show line numbers.

5. Every table must have a header row in electric blue (#00A8FF) with white text.

6. Every diagram slide must include a "KEY" or legend explaining color coding.

7. Speaker notes are mandatory on slides 05, 08, 13, and 14 — these are the conceptually dense slides.

8. After generating all slides, output a SLIDE DECK INDEX table:
   Slide# | Title | Type (Definition/Diagram/Table/Practical/Summary) | Source Block

═══════════════════════════════════════════════════
SOURCE MATERIAL
═══════════════════════════════════════════════════
Generate slide content ONLY from the uploaded source files. Do not introduce facts, statistics, or examples not present in the sources. If you are unsure, write "[VERIFY]" in speaker notes.

Key source files to prioritize:
- theoretical/01_what_is_an_agent.md
- theoretical/02_sense_think_act_loop.md
- theoretical/03_memory_goals_state.md
- theoretical/04_core_characteristics_orchestration.md
- theoretical/05_cynefin_autonomy_matrix.md
- examples/01_phishing_alert_walkthrough.md
- examples/02_apt_complex_domain_walkthrough.md
- Agentic AI Integration and the Cynefin Framework for SOC Operations (1).md

Generate the full 20-slide deck now.
```

---

## How to Reuse for Other Modules

Change only these four fields in the prompt:

| Field | Module 01 Value | Your New Value |
|---|---|---|
| `This is Module:` | 01 | 02, 03, etc. |
| `Module Title:` | Agentic AI Foundations for Cyber Defense | MCP Fundamentals and Architecture |
| `Number of core topics:` | 5 | (count from the module's theoretical files) |
| `Topics:` | What Is an AI Agent \| The Sense-Think-Act Loop \| ... | (list from the module's theoretical files) |

And update the `SOURCE MATERIAL` section with the file names from the new module.

The design system (colors, typography, layout rules, generation rules) remains **identical** across all modules — ensuring a consistent visual identity for the full 8-module course.

---

## Module Prompt Reference Table

| Module | Title | Topics Count | Slide Count Suggested |
|---|---|---|---|
| 01 | Agentic AI Foundations | 5 | 20 |
| 02 | MCP Fundamentals and Architecture | 4 | 18 |
| 03 | Cyber Defense Foundations | 3 | 16 |
| 04 | Python Essentials for MCP | 4 | 16 |
| 05 | Build MCP Servers | 4 | 18 |
| 06 | Build MCP Clients | 3 | 16 |
| 07 | Integrate MCP into AI Workspaces | 4 | 16 |
| 08 | Policy, Guardrails, and Safe Autonomy | 4 | 18 |
