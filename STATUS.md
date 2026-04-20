# Carthage Shield Integration & Infrastructure Status

## 🏗️ 1. Infrastructure Migration (Azure & Docker)

We successfully migrated the application from a bare-metal setup (Nginx + PHP direct) to a **Containerized Docker Compose Stack** hosted on a new Azure instance (`52.236.175.71`). 

### Core Components
- **`lms_app`**: PHP-FPM container running the main Laravel 11 application.
- **`lms_nginx`**: Nginx web server handling routing and static assets.
- **`lms_worker`**: Laravel Queue worker handling background jobs (like sending Microsoft Graph emails).
- **`lms_scheduler`**: Laravel cron scheduler component.
- **Database**: External or inter-container MySQL `lms_mysql` mapped via existing volumes.

### Deployment Workflow
Updates to production are now streamlined through Git:
1. Push local changes to GitHub (`main` branch).
2. SSH into the Azure server (`dluser@52.236.175.71`).
3. Run `git pull origin main` in `~/lms/mcp_course`.
4. Run cache clearance and container restarts to load new code:
   ```bash
   sudo docker exec lms_app php artisan optimize:clear
   sudo docker compose restart app worker scheduler
   ```

---

## 🎮 2. Carthage Shield (Game Module)

MIGRATION: The legacy tabletop simulation logic has been fully ported into the main application.

### Data Models & Architecture
We built 10 dedicated models heavily bound to the game logic, complete with migrations:
1. `CsSession`: The root simulation session managing overall game state and the active phase (`1` to `4`).
2. `CsTeam`: Teams competing and collaborating within the session.
3. `CsPlayer`: Users registered into a specific session/team.
4. `CsInject`: The canonical catalog of all scenarios, attacks, and inject cards.
5. `CsSessionInject`: **(Pivotal)** Tracks the delivery and state of an inject within a live session.
6. `CsBroadcast`: Global messages sent by the moderator.
7. `CsDecision`: Options tied to an inject.
8. `CsVote` & `CsVoteEntry`: Tracks team votes against decisions.
9. `CsScenario`: Higher level structure defining the narrative map.

### Live Engine
- `CsService`: A robust Service layer handling all the heavy lifting behind the scenes (`triggerInject`, `advancePhase`, `getState`, `calculateScores`).
- API Polling: The `getState` REST endpoint serves lightweight JSON to the frontend, syncing up timers, currently active injects (e.g., Phantom Grid), and total scores.

### Global Dashboard (Big Screen)
- Re-architected `/cs/{code}/dashboard` to be a **Full Screen / No Navigation Bar** immersive experience suitable for projection during live tabletop events.
- Employs our HUD styling CSS directly, featuring pulsing alarms and responsive grid designs.

---

## 📧 3. Communications (Graph API & Emails)

### Graph API Integration fixed
- The server employs a heavily custom **Microsoft Graph API Mailer** instead of traditional basic SMTP to bypass Microsoft 365 security restrictions (`event.ancs@defensy.io`).
- **Resolved 500 Errors:** Fixed the critical 500 error preventing Invitations by successfully re-linking the custom `GraphMailServiceProvider` class into the Laravel Framework `bootstrap/providers.php` and `config/mail.php` arrays.

### Game Invitation Update
- Completely rewrote the `emails.invitation` Blade template.
- Purged all references to the legacy M01-M08 training modules.
- The invitations now dynamically brief the recipient on their objective for the **Carthage Shield Cyber Breach Exercise** and the *Phantom Grid* attack scenario.

---

## 🚀 4. Next Steps / Pending Actions

1. **Player HUD Enhancement**: We need to continue adapting the exact internal Player views so teams can securely log in, view the active inject, and cast their decisions in real-time.
2. **Moderator Controls Setup**: Refining the Moderator Dashboard to ensure the host can flawlessly control phase advancement and inject visibility during live operations.
3. **Automated CI/CD**: Right now, deploying requires a manual `git pull` over SSH. Our next infrastructure goal should be setting up a GitHub Action to automate the SSH pulling mechanism.
