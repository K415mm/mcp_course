# Neptune Strike — Moderator Guide (Game Master)

Welcome to the **Neptune Strike** Moderator Guide. As the Moderator (Game Master), you serve as the coordinator, controller, and evaluator of the simulation. This guide provides instructions on how to use the Moderator Console to run the game, inject cyber incidents, manage phase progressions, and score participating teams.

---

## 1. Role of the Moderator

The Moderator orchestrates the crisis timeline. You are responsible for:
1. **Pacing the Exercise:** Managing the simulation clock and advancing phases.
2. **Injecting Drama:** Introducing pre-configured injects, media files, and attacker ransom notes (Phantom popups) to simulate operational friction.
3. **Evaluating Responses:** Reading, grading, and rewarding team decisions with score adjustments and performance badges in real-time.
4. **Triggering Consensus Checks:** Initiating strategic votes and quizzes from the scenario bank.

---

## 2. Setting Up the Session

Before starting the exercise:
1. **Session Creation:** From the Admin panel, select the **Neptune Strike** scenario and generate a new session. This creates a unique **6-letter Session Code** (e.g., `FH7UAZ`).
2. **Accessing the Console:** Navigate to the moderator URL:
   * `/neptune/moderator/{session_code}`
3. **Player Roster:** Open the **Manage Players** tab. As participants join the session via the lobby using the session code, assign them to their specific team roles:
   * **CERT Command** (Cyber Security Incident Response)
   * **Port Authority** (Operations and Mooring)
   * **Maritime Police** (Physical Harbor Security)
   * **Naval Command** (Military Coastal Sovereignty)
   * **Ministry Cell** (Inter-ministerial Policy and PR)
4. **The Grand Screen:** Open the **Grand Screen** (dashboard) view on a secondary projector or shared monitor so that all participants in the room can see the global countdown, the news feed ticker, active threat levels, and live vote tallies.

---

## 3. Controlling the Session Lifecycle

The top control bar of the console contains the core game mechanics:

### 3.1. Timer Management
* **Start / Pause / Reset:** Click `START` to begin the countdown. Click `PAUSE` to halt the clock during key debates or facilitation interludes. Click the reset icon to restart the timer.
* **Custom Time:** Input the desired phase duration in the text box (e.g., `20` minutes) and click `Set (min)` to adjust the remaining time.

### 3.2. Phase Control
* **Advance Phase (`Phase ▶`):** Transitions the simulation to the next phase. This updates the scenario timeline, unlocks new files in the participant libraries, and updates the local question banks.
* **Manual Jump:** Click on any numbered phase button (e.g., `Phase 1`, `Phase 2`) to jump directly to that phase's configuration.
* **End Session (`⏹ FIN`):** Closes the session and navigates the Grand Screen and participants to the final leaderboard/podium.

### 3.3. Atmosphere Tweaks
Adjust the physical and visual intensity of the room by selecting an atmosphere setting. This instantly changes the color scheme and styling of the participant dashboards and the Grand Screen:
* **CALM (Green):** Initial phase. Use for peaceful, standard operations.
* **TENSION (Orange):** Initial anomaly detection. Triggers alert states.
* **CRISIS (Red):** Severe disruption (e.g., vessel failures, SCADA shutdown).
* **HACKED (Purple):** Attacker takeover. Displays digital "glitch" animations on all active participant screens.
* **VICTORY (Yellow):** End of simulation. Celebratory theme.

---

## 4. Using the Scenario Question Library (The Bank)

The scenario library (located on the right-hand panel) contains a pre-configured bank of **10 strategic questions** per active phase. These questions are tailored to test strategic decision-making under pressure.

### 4.1. Library Question Types
Each phase is populated with three specific categories of questions:
* **[QUIZ] (4 per phase):** Operational and technical knowledge tests.
* **[VOTE] (3 per phase):** Strategic policy dilemmas requiring cross-team debate.
* **[OPEN] (3 per phase):** Free-form situational report prompts (Sitreps) where teams draft structured proposals.

### 4.2. Pre-loading and Prefilling
To avoid manual typing errors during the exercise:
1. Locate the **Questions** tab in the library panel.
2. Click **Prefill Quiz** to load a quiz question (and its correct answers/point weights) directly into the Quiz Question form.
3. Click **Prefill Vote** to load a strategic voting question (and its HSL options, points, and notes) directly into the Strategic Vote form.
4. Review the prefilled forms, adjust as necessary, and click **Broadcast** to send the question live.

---

## 5. Driving the Storyline (Injects & Media)

### 5.1. Planned & Surprise Injects
* **Planned Injects:** Listed under the **Injects** tab. Click `ENVOYER` (Send) to publish the inject. It will immediately pop up as a SweetAlert warning overlay on all active participant dashboards and log itself in their communications panel.
* **Surprise Injects:** Use the **Broadcast** alert box to write custom text alerts on the fly. This allows you to respond dynamically to team actions (e.g., "ATTENTION ALL TEAMS: MV Olympia reports rudder control failure").

### 5.2. Attacker Ransom Notes (Phantom Modals)
To simulate a direct cyber-extortion attack:
1. Select the **Phantom (Attacker)** tab.
2. Enter the attacker's ransom text.
3. Click **Deploy Ransom Note**. This interrupts all active participant dashboards with a full-screen, un-dismissible dark overlay containing the ransom message.

### 5.3. Media Injections
* Use the **Media Library** tab to push maps, schematics, or video feeds live to participant dashboards.
* You can also upload a custom file or URL via the upload forms, save it to the current phase bank, or inject it live immediately.

---

## 6. Reviewing and Scoring Team Actions

As teams collaborate, they will submit decisions, escalation alerts, and public relations statements. These appear in real-time in the **Decisions Received** queue.

### 6.1. Grading Submissions
For each submission, read the text drafted by the team and select one of the following actions:
* **Approve / Grade:** Award points based on quality and speed:
  * `+5` points for standard compliance.
  * `+10` points for comprehensive, cooperative actions.
  * `+20` points for exceptional strategic foresight.
* **Reject / Penalize:** Award `0` or negative points (`-5` or `-10`) for decisions that violate standard operating procedures or compromise safety.

### 6.2. Awarding Badges
Recognize outstanding team behaviors by awarding unique achievements. Clicking a badge applies positive score adjustments (+5 points) and triggers a visual celebration on the team's dashboard:
* 🛡️ **Cyber Shield:** Exceptional incident detection or network containment.
* 📢 **Crisis Comm:** Clear, structured stakeholder notification.
* 🤝 **Joint Force:** Successful cross-sector or civil-military coordination.
* ⚖️ **Legal Mind:** Strong application of international law (e.g., UNCLOS, NIS2).

---

## 7. Strategic Voting & Quizzes

### 7.1. Orchestrating a Vote
1. Prefill or write a vote question.
2. Click **Start Vote**. This locks the vote interface on participant screens.
3. Watch the real-time vote tally build on the Moderator Console and the Grand Screen.
4. Once all sectors submit their selections, click **Close Vote**. This awards points to the teams according to their selections and reveals the final statistics.

### 7.2. Launching a Quiz
1. Prefill or write a quiz question.
2. Click **Broadcast Quiz**. This pushes the question to all participant dashboards.
3. Once teams submit their answers, click **Close Quiz** to grade responses and display the correct answers.
