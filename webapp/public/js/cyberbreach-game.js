/**
 * CyberBreach Game Engine — Frontend controller
 * Manages local state, polling, card interactions, and UI updates.
 */
class CyberBreachGame {
    constructor(sessionId, csrfToken) {
        this.sessionId = sessionId;
        this.csrf = csrfToken;
        this.state = null;
        this.selectedCard = null;
        this.timerInterval = null;
        this.timerSeconds = 0;
        this.pollInterval = null;
        this.POLL_MS = 3000;
        this.BASE = `/game/${sessionId}/api`;
    }

    // ── Initialization ──────────────────────────────────────────
    init() {
        this.startPolling();
        this.bindEvents();
    }

    destroy() {
        clearInterval(this.pollInterval);
        clearInterval(this.timerInterval);
    }

    // ── Polling ─────────────────────────────────────────────────
    startPolling() {
        this.fetchState();
        this.pollInterval = setInterval(() => this.fetchState(), this.POLL_MS);
    }

    async fetchState() {
        try {
            const res = await fetch(`${this.BASE}/state`);
            if (!res.ok) return;
            const data = await res.json();
            this.state = data;
            this.render();
        } catch (e) {
            console.warn('Poll failed:', e);
        }
    }

    // ── API Calls ───────────────────────────────────────────────
    async api(endpoint, body = {}) {
        try {
            const res = await fetch(`${this.BASE}/${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (data.success) {
                this.fetchState(); // refresh after action
            }
            return data;
        } catch (e) {
            console.error('API error:', e);
            return { success: false, error: 'Erreur réseau' };
        }
    }

    // ── Game Actions ────────────────────────────────────────────
    async playCard(teamCardId, targetSystem) {
        return this.api('play-card', { team_card_id: teamCardId, target_system: targetSystem });
    }

    async drawCard() { return this.api('draw-card'); }
    async buyFromShop(cardId) { return this.api('buy-shop', { card_id: cardId }); }
    async startRound() { return this.api('start-round'); }
    async advancePhase() { return this.api('advance-phase'); }
    async drawEvent() { return this.api('draw-event'); }
    async adjustTokens(teamType, amount) { return this.api('adjust-tokens', { team_type: teamType, amount }); }
    async dealHands() { return this.api('deal-hands'); }

    // ── Event Binding ───────────────────────────────────────────
    bindEvents() {
        // Card play confirmation
        document.addEventListener('click', (e) => {
            const card = e.target.closest('.cb-card[data-team-card-id]');
            if (card && !card.classList.contains('no-play')) {
                this.selectCard(card);
            }
        });
    }

    selectCard(cardEl) {
        // Deselect previous
        document.querySelectorAll('.cb-card.selected').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        this.selectedCard = {
            teamCardId: parseInt(cardEl.dataset.teamCardId),
            cardName: cardEl.dataset.cardName,
            cost: parseInt(cardEl.dataset.cost),
        };

        // Show play modal
        this.showPlayModal();
    }

    showPlayModal() {
        if (!this.selectedCard) return;
        const modal = document.getElementById('playCardModal');
        if (!modal) return;

        document.getElementById('playCardName').textContent = this.selectedCard.cardName;
        document.getElementById('playCardCost').textContent = this.selectedCard.cost;

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    async confirmPlay() {
        if (!this.selectedCard) return;
        const target = document.getElementById('targetSystem')?.value || null;
        const result = await this.playCard(this.selectedCard.teamCardId, target);

        const modal = bootstrap.Modal.getInstance(document.getElementById('playCardModal'));
        if (modal) modal.hide();

        if (result.success) {
            this.showToast(`Carte jouée ! +${result.points} pts`, 'success');
        } else {
            this.showToast(result.error || 'Erreur', 'danger');
        }
        this.selectedCard = null;
    }

    // ── Rendering ───────────────────────────────────────────────
    render() {
        if (!this.state) return;
        const s = this.state;

        this.renderHeader(s);
        this.renderTeam('blue', s.blueTeam);
        this.renderTeam('red', s.redTeam);
        this.renderInfra(s);
        this.renderHand(s);
        this.renderActionLog(s);

        if (s.role === 'moderator') {
            this.renderModPanel(s);
        }

        if (s.round?.eventCard) {
            this.renderEventCard(s.round.eventCard);
        }
    }

    renderHeader(s) {
        const el = id => document.getElementById(id);
        const sess = s.session;

        if (el('cbRound')) el('cbRound').textContent = `Round ${sess.currentRound}/${sess.maxRounds}`;
        if (el('cbPhase')) el('cbPhase').textContent = sess.phaseLabel;
        if (el('cbStatus')) {
            el('cbStatus').textContent = sess.status.toUpperCase();
            el('cbStatus').className = 'badge ' + (sess.status === 'active' ? 'bg-success' : sess.status === 'lobby' ? 'bg-warning text-dark' : 'bg-secondary');
        }
        if (el('cbCode')) el('cbCode').textContent = sess.code;

        // Update scores in header
        if (el('cbBlueScore')) el('cbBlueScore').textContent = s.blueTeam?.score ?? 0;
        if (el('cbRedScore'))  el('cbRedScore').textContent = s.redTeam?.score ?? 0;
    }

    renderTeam(type, team) {
        if (!team) return;
        const prefix = type === 'blue' ? 'blue' : 'red';

        const el = id => document.getElementById(id);
        if (el(`${prefix}Tokens`))     el(`${prefix}Tokens`).textContent = team.tokens;
        if (el(`${prefix}ShopTokens`)) el(`${prefix}ShopTokens`).textContent = team.shopTokens;
        if (el(`${prefix}Score`))      el(`${prefix}Score`).textContent = team.score;
        if (el(`${prefix}HandCount`))  el(`${prefix}HandCount`).textContent = team.handCount;

        // Players list
        const playersList = el(`${prefix}Players`);
        if (playersList && team.players) {
            playersList.innerHTML = team.players.map(p =>
                `<div class="d-flex align-items-center gap-2 mb-1">
                    <div class="cb-player-avatar" style="background:${type === 'blue' ? '#3a90e8' : '#e83a3a'};">${p.initials}</div>
                    <span class="small">${p.name}${p.isCaptain ? ' <i class="bi bi-star-fill text-warning" style="font-size:.6rem;"></i>' : ''}</span>
                </div>`
            ).join('');
        }

        // Active cards
        const activeList = el(`${prefix}Active`);
        if (activeList && team.activeCards) {
            activeList.innerHTML = team.activeCards.map(tc =>
                `<div class="d-flex align-items-center gap-2 mb-1 p-1 rounded" style="background:rgba(255,255,255,.04);font-size:.75rem;">
                    <span class="cb-${type === 'blue' ? 'defend' : 'compromise'}-dot"></span>
                    <span>${tc.card.name}</span>
                    ${tc.remainingTurns ? `<span class="ms-auto badge bg-dark">${tc.remainingTurns}t</span>` : ''}
                </div>`
            ).join('');
        }
    }

    renderInfra(s) {
        // Update infrastructure nodes based on active cards/compromises
        // This is a visual indicator — in a full implementation we'd track per-node state
    }

    renderHand(s) {
        const container = document.getElementById('myHand');
        if (!container) return;

        const myTeam = s.myTeamType === 'blue' ? s.blueTeam : (s.myTeamType === 'red' ? s.redTeam : null);
        if (!myTeam?.hand) {
            container.innerHTML = '<div class="text-white-50 small p-3">Rejoignez une équipe pour voir vos cartes</div>';
            return;
        }

        container.innerHTML = myTeam.hand.map(tc => this.renderCardHTML(tc.card, tc.id)).join('');
    }

    renderCardHTML(card, teamCardId = null) {
        const cssClass = card.cssClass || ('card-' + card.type);
        const interactive = teamCardId ? `data-team-card-id="${teamCardId}" data-card-name="${card.name}" data-cost="${card.cost}"` : 'class="no-play"';

        return `
        <div class="cb-card ${cssClass}" ${interactive}>
            <div class="cb-card-header">
                <div class="cb-card-stripe"></div>
                <div class="cb-card-type">${card.typeLabel || card.type}</div>
                <div class="cb-card-name">${card.name}</div>
                ${card.phase ? `<div class="cb-card-phase">${card.phase}</div>` : ''}
            </div>
            <hr class="cb-card-divider">
            <div class="cb-card-body">
                <div class="cb-card-desc">${card.description}</div>
                <div class="cb-card-effect">${card.effect || ''}</div>
            </div>
            <div class="cb-card-footer">
                <div class="cb-card-cost">
                    <div class="cb-cost-circle">${card.cost || '—'}</div>
                    <span style="font-size:7px;opacity:.6;">${card.cost ? 'jetons' : ''}</span>
                </div>
                <div class="cb-card-pts">${card.points > 0 ? '+' + card.points : (card.points === 0 ? '' : card.points)}</div>
            </div>
        </div>`;
    }

    renderActionLog(s) {
        const log = document.getElementById('actionLog');
        if (!log || !s.actionLog) return;

        if (s.actionLog.length === 0) {
            log.innerHTML = '<div class="text-white-50 small p-2 text-center">Aucune action ce round</div>';
            return;
        }

        log.innerHTML = s.actionLog.map(a =>
            `<div class="cb-log-entry">
                <div class="cb-log-dot ${a.teamType}"></div>
                <span class="text-white-50">${a.playerName}</span>
                <span class="text-white fw-bold">${a.cardName}</span>
                ${a.targetSystem ? `<span class="text-white-50">→ ${a.targetSystem}</span>` : ''}
                <span class="ms-auto ${a.points >= 0 ? 'text-success' : 'text-danger'}">${a.points > 0 ? '+' : ''}${a.points} pts</span>
            </div>`
        ).join('');
    }

    renderModPanel(s) {
        const panel = document.getElementById('modPanel');
        if (!panel) return;
        panel.style.display = 'block';

        // Enable/disable buttons based on state
        const sess = s.session;
        const btnStart = document.getElementById('btnStartRound');
        const btnPhase = document.getElementById('btnAdvancePhase');
        const btnEvent = document.getElementById('btnDrawEvent');

        if (btnStart) btnStart.disabled = sess.status === 'active' && sess.currentPhase > 0 && sess.currentPhase < 5;
        if (btnPhase) btnPhase.disabled = sess.status !== 'active';
        if (btnEvent) btnEvent.disabled = sess.currentPhase !== 4;
    }

    renderEventCard(event) {
        const container = document.getElementById('eventDisplay');
        if (!container || !event) return;

        const cssMap = { danger: 'card-danger', success: 'card-success', joker: 'card-joker', situation: 'card-situation', alerte: 'card-alerte' };
        const css = cssMap[event.subtype] || 'card-danger';

        container.innerHTML = `
        <div class="cb-event-display ${css}" style="border-color: var(--border-color, rgba(255,255,255,.2));">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;opacity:.7;margin-bottom:.3rem;">
                Événement${event.subtype ? ' — ' + event.subtype.charAt(0).toUpperCase() + event.subtype.slice(1) : ''}
            </div>
            <div style="font-family:'Space Mono',monospace;font-weight:700;font-size:1rem;margin-bottom:.5rem;">${event.name}</div>
            <div style="font-size:.85rem;margin-bottom:.5rem;">${event.description}</div>
            <div style="font-family:'Space Mono',monospace;font-size:.8rem;padding:.4rem .8rem;border-radius:6px;background:rgba(255,255,255,.08);display:inline-block;">${event.effect}</div>
        </div>`;
        container.style.display = 'block';
    }

    // ── Toast Notifications ─────────────────────────────────────
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show mb-2`;
        toast.style.cssText = 'font-size:.85rem;animation:slideIn .3s ease-out;';
        toast.innerHTML = `${message}<button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>`;
        container.prepend(toast);

        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
    }
}

// ── Timer Utility ───────────────────────────────────────────
function startTimer(seconds, display) {
    let timer = seconds;
    const interval = setInterval(() => {
        const mins = Math.floor(timer / 60);
        const secs = timer % 60;
        display.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

        if (timer <= 60) display.classList.add('warning');
        if (timer <= 30) { display.classList.remove('warning'); display.classList.add('critical'); }

        if (--timer < 0) {
            clearInterval(interval);
            display.textContent = '00:00';
        }
    }, 1000);
    return interval;
}
