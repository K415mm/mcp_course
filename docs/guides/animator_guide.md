# Neptune Strike — Animator & Mentor Guide

Welcome to the **Neptune Strike** Animator and Mentor Guide. In this simulation, the Animator (often representing the National Cybersecurity Agency or ANCS) is a hybrid facilitator: you do not control the technical console (which is the Moderator's role), and you are not an evaluated player. 

Your mission is to act as an **active observer**, **critical thinking coach**, and **mentor**. This guide outlines how to guide teams through crisis phases, facilitate communication, and support the retrospective debriefing.

---

## 1. The Role of the Mentor

A successful simulation relies on participants experiencing realistic pressure, navigating ambiguity, and learning to collaborate. As a Mentor, you help them by:
1. **Guiding, Not Doing:** Never give the players the direct answer. Instead, ask questions that lead them to discover the solution.
2. **Breaking Down Silos:** Encourage the five sector teams (CERT, Port Authority, Police, Navy, Ministry) to talk to one another. Information hoarding is the most common cause of failure in a crisis.
3. **Challenging Assumptions:** If teams make decisions too quickly without consulting legal or operational constraints, challenge their rationale.
4. **Capturing Observations:** Note down team dynamics, leadership structures, communication failures, and brilliant decisions to use during the final debrief.

---

## 2. Phase-by-Phase Facilitation Strategies

### Phase 1 — Initial Detection & Containerization
* **Core Theme:** Initial cyber anomalies are detected in port infrastructure (Marseille-Fos VTMS/SCADA systems).
* **Mentor Posture:** Watch how teams react to the first signs of trouble. Do they ignore the warnings? Do they jump to conclusions without data?
* **Key Interventions:**
  * If the CERT team identifies an anomaly but doesn't tell anyone, ask: *"Who else in the port relies on this system? What happens if they keep operating blind?"*
  * If the Port Authority panics, ask: *"Do you have a manual Business Continuity Plan (BCP)? What is the procedure to switch to manual mooring?"*

### Phase 2 — Threat Analysis & Undersea Infrastructure
* **Core Theme:** Geopolitical attribution, NIS2 regulatory reporting compliance, and hybrid threats to submarine cables.
* **Mentor Posture:** Focus on information analysis and regulatory thresholds. Ensure teams understand their international obligations.
* **Key Interventions:**
  * Prompt the Ministry and Navy on attribution: *"What level of technical proof do we have before we publicly attribute this to a state-sponsored group? What are the diplomatic risks?"*
  * Remind CERT and Ministry of NIS2: *"Under EU guidelines, what is our timeline to notify ENISA? Have we met the criteria for a significant incident?"*

### Phase 3 — Multi-Vector Escalation & Life Safety
* **Core Theme:** Direct physical danger (drifting crude tankers, SCADA valve manipulations, rumors of toxic gas leaks).
* **Mentor Posture:** Pacing increases. High stress. Help teams manage cognitive overload and prioritize human life.
* **Key Interventions:**
  * When multiple alerts trigger simultaneously, ask: *"What is our number one priority right now? Is it economic continuity or saving human lives?"*
  * On rumor control: *"There is panic on social media about a toxic cloud. How do we reassure the public without causing more panic? Who issues the statement?"*

### Phase 4 — Governance & Policy Architecture
* **Core Theme:** Long-term policy changes, UNCLOS legal reforms, funding cyber-defense, and creating a Mediterranean Cyber Hub.
* **Mentor Posture:** Shift from tactical crisis management to high-level strategic tradeoffs. Help teams frame their arguments before voting.
* **Key Interventions:**
  * Ask: *"If we establish a new Mediterranean CERT, who funds it? How do we share intelligence with non-EU partners without exposing our own defense networks?"*
  * On UNCLOS: *"What is the definition of 'innocent passage'? Can a cyber operation violate this under current maritime law?"*

---

## 3. Collaboration with the Moderator (Game Master)

You work hand-in-hand with the Moderator to manage the simulation flow:
* **Adjusting Difficulty:** If you notice a team is solving tasks too easily, ask the Moderator to deploy a **Surprise Inject** or launch a **Phantom Ransom Note** targeting their sector.
* **Injecting Advice:** You can use the consultant role dashboard to send non-evaluated recommendations or advisory bulletins to specific teams.
* **Time Management:** If teams are stuck in a deep debate, suggest the Moderator pause the countdown clock to allow for a structured facilitation session.

---

## 4. Key Mentoring "Do's and Don'ts"

### Do:
* **Do** ask open-ended questions (e.g., *"What is your backup plan if the network remains down?"*).
* **Do** push teams to document their decisions before submitting them.
* **Do** look for quiet team members and encourage their participation.
* **Do** collaborate with the Moderator to tailor the simulation intensity.

### Don't:
* **Don't** write the decision texts for the teams.
* **Don't** tell the CERT team which IP addresses to block or how to resolve a PLC anomaly.
* **Don't** let one dominant player make all the decisions for a team without consensus.
* **Don't** interrupt an active crisis discussion; let the teams experience the pressure of time constraints.

---

## 5. Structuring the Debriefing (Phase 5)

The debriefing is where the core learning takes place. After the Moderator ends the session:
1. **Team Self-Evaluation:** Have each team summarize their biggest challenge and how they resolved it.
2. **Reviewing key decision points:** Discuss the outcomes of the **Strategic Votes** (e.g., the decision to suspend port traffic or redirect shipping lines).
3. **Sharing Observations:** Highlight moments of excellent coordination (e.g., when CERT shared IoCs with Naval Command) and areas of improvement (e.g., delayed public communications).
4. **Translating to Real-World Value:** Ask: *"How does this simulation map to our actual crisis plans? What is the single biggest gap we need to fix in our real-world organizations?"*
