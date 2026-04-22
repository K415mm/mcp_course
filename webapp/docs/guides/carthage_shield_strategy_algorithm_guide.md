# Carthage Shield Strategy And Algorithm Guide

## 1) Purpose

This guide explains how Carthage Shield works as a cyber-crisis tabletop simulation engine, with focus on:

- Core game architecture and data flow
- Decision, vote, and scoring algorithms
- Moderator control logic
- Alignment with tabletop exercise practice and Tunisian/international procedures

## 2) System Model (How The Game Works)

### 2.1 Main Objects

- `Session`: one running exercise (`lobby`, `active`, `paused`, `finished`)
- `Scenario`: static definition of narrative, phases, durations, and voting options
- `Teams`: six institutional teams (ANCS, CERT, Finance, Transport, E-Gov, Communication)
- `Players`: participants attached to teams, with captain auto-assigned to first joiner
- `Injects`: scripted events (standard + surprise), optionally targeted to a specific team
- `Broadcasts`: moderator messages and attacker ("phantom") pressure messages
- `Decisions`: structured team outputs (`decision`, `escalade`, `communication`, `question`)
- `Votes`: phase-level collective choices with tally and winner logic
- `Badges`: bonus awards with fixed additional points

### 2.2 High-Level Runtime Loop

1. Moderator creates a session and selects scenario.
2. System creates default teams and session code.
3. Players join teams and submit heartbeat updates.
4. Moderator starts timer for current phase.
5. Teams discuss injects and submit decisions.
6. Moderator may open/close vote and trigger injects/phantom messages.
7. Moderator awards decision points and badges.
8. Moderator advances phase until final phase, then session is finished.

## 3) Phase And Timer Algorithm

### 3.1 Phase State Machine

- Initial state: `lobby`, `current_phase_index = 0`
- Start phase: `status = active`, `timer_ends_at = now + remaining`
- Pause phase: `status = paused`, persist remaining seconds
- Advance phase:
  - If next phase exists: move index, set `paused`, preload phase duration
  - If no next phase: set `finished`, stop timers, set atmosphere to `victory`

### 3.2 Authoritative Timer Rule

Server computes remaining time from database state:

- Running: `remaining = max(0, timer_ends_at - now)`
- Paused: `remaining = timer_paused_remaining`
- Timer is considered running only when status is `active` and `timer_ends_at` is in future

This prevents client-side time drift and keeps all teams synchronized.

## 4) Decision, Vote, And Scoring Algorithms

### 4.1 Team Decisions

- Teams submit structured decision records with type + content
- Each decision is phase-stamped for traceability
- Moderator can award points (0-100) per decision
- Awarded points are added to team score

### 4.2 Voting Logic

- Moderator opens one vote at a time (existing open vote is auto-closed)
- One vote entry per team (`teamHasVoted` guard)
- On vote close:
  - System tallies counts by option key
  - Tie -> no winner, no points awarded
  - Single winner with configured points -> points added to all teams (collective governance bonus)

### 4.3 Score Guardrails

- Score adjustments are bounded operationally in API validation (`delta` limits)
- Team score cannot become negative (`max(0, value)`)
- Badge bonuses are deterministic (currently +5 each)

## 5) Information And Visibility Model

- Moderator state includes all decisions, inject catalog, online players, and awarded badges
- Player state is filtered:
  - Inject feed only includes global injects or those targeted to player team type
  - Teams see own recent decisions feed
- Presence is inferred from heartbeat (`last_seen_at`) with short online window

This supports realistic partial information, while preserving moderator omniscience.

## 6) Tabletop Exercise Alignment

### 6.1 Structural Alignment (TTX Good Practice)

- Scenario-driven phases mirror tabletop progression: briefing -> detection -> escalation -> communications -> arbitration -> debrief
- Inject model supports progressive disclosure and surprise events
- Decision logging provides evidence trail for after-action review
- Timed phases enforce pressure and prioritization under uncertainty
- Voting and arbitration emulate inter-agency governance decisions

### 6.2 Facilitation Alignment

- Moderator has controlled intervention levers (injects, atmosphere, phantom pressure, score adjudication)
- Teams are role-based (policy, technical, sectoral, public communication)
- Debrief outputs can be directly linked to phase-stamped decisions and scores

## 7) Standards And Procedure Mapping

Use this mapping to align exercise objectives and scoring rubrics.

| Carthage Shield Mechanic | Tabletop / Standard Reference | Alignment Note |
|---|---|---|
| Detection and qualification phases | ISO/IEC 27035 (incident identification and assessment) | Supports early triage and incident classification drills |
| Escalation and containment decisions | NIST SP 800-61 (containment strategy) | Decision matrix options can be scored against containment quality |
| Inter-ministerial coordination vote | ISO 22320 (incident command and coordination) | Collective vote simulates command-level posture selection |
| Public communication phase | Crisis communication procedures (national CERT + government communication protocols) | Communication options map to rumor control, spokesperson discipline, and public trust protection |
| Inject-driven stress testing | Tabletop inject methodology (HSEEP-style design) | Standard and surprise injects emulate dynamic threat evolution |
| Decision/badge traceability | After Action Review and lessons learned practice | Phase-stamped records enable measurable capability review |

## 8) Tunisian National Procedure Integration Checklist

For alignment with Tunisian institutional context, configure each run with:

- Clear escalation chain between ANCS/CERT and sector operators
- Mandatory incident classification thresholds (what triggers national coordination)
- Defined media approval workflow for crisis statements
- Legal/privacy escalation points (citizen data leakage, cross-border reporting)
- Inter-ministerial coordination protocol and contact matrix
- Post-exercise corrective action register with owner and deadline

## 9) Recommended Scoring Rubric For Compliance-Oriented Sessions

Use weighted dimensions per phase:

- Technical response quality: 35%
- Governance and escalation discipline: 25%
- Inter-team coordination: 20%
- Public communication quality: 10%
- Documentation and traceability: 10%

This keeps gameplay aligned with operational maturity, not only speed.

## 10) Known Gaps To Address For Stronger Formal Compliance

- Add explicit legal/regulatory checkpoints as structured decision prompts
- Export machine-readable after-action report (JSON/PDF)
- Add objective-level scoring templates per scenario and phase
- Add evaluator role separate from moderator for independent assessment
- Add bilingual policy packs (Arabic/French/English) for national exercises

## 11) Bottom Line

Carthage Shield already implements a strong algorithmic core for cyber tabletop simulation:

- Server-authoritative time and state
- Phase-based crisis progression
- Traceable decisions and inject handling
- Governed voting and scoring

With a compliance scoring rubric and formal SOP overlays, it can align well with tabletop exercise doctrine and Tunisian/international incident management procedures.
