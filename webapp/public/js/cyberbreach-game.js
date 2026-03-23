/**
 * CyberBreach Game Engine v2 — Enhanced with vis-network, SortableJS, card flip
 */
class CyberBreachGame {
    constructor(sessionId, csrfToken, role) {
        this.sessionId = sessionId;
        this.csrf = csrfToken;
        this.role = role; // 'moderator', 'player', 'spectator'
        this.state = null;
        this.selectedCard = null;
        this.pollInterval = null;
        this.network = null;
        this.sortable = null;
        this.POLL_MS = 3000;
        this.BASE = `/game/${sessionId}/api`;
        this.lastPhase = -1;
    }

    // ── Init ────────────────────────────────────────────────────
    init() {
        this.startPolling();
        this.initNetworkMap();
        this.initSortable();
        this.bindEvents();
    }

    destroy() {
        clearInterval(this.pollInterval);
        if (this.network) this.network.destroy();
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
        } catch (e) { console.warn('Poll failed:', e); }
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
            if (data.success) this.fetchState();
            return data;
        } catch (e) {
            console.error('API error:', e);
            return { success: false, error: 'Erreur réseau' };
        }
    }

    async playCard(teamCardId, targetSystem) { return this.api('play-card', { team_card_id: teamCardId, target_system: targetSystem }); }
    async drawCard() { return this.api('draw-card'); }
    async buyFromShop(cardId) { return this.api('buy-shop', { card_id: cardId }); }
    async startRound() { return this.api('start-round'); }
    async advancePhase() { return this.api('advance-phase'); }
    async drawEvent() { return this.api('draw-event'); }
    async adjustTokens(teamType, amount) { return this.api('adjust-tokens', { team_type: teamType, amount }); }
    async dealHands() { return this.api('deal-hands'); }

    // ── vis-network Infrastructure Map ──────────────────────────
    initNetworkMap() {
        const container = document.getElementById('networkMap');
        if (!container) return;

        const nodes = new vis.DataSet([
            { id: 'internet',  label: '🌐 Internet',          shape: 'diamond', color: { background: '#1a2744', border: '#3a90e8' }, font: { color: '#fff', size: 11 }, borderWidth: 2, size: 25 },
            { id: 'apigw',     label: '🛡️ API Gateway',       shape: 'box',     color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'k8s',       label: '☸️ K8s Cluster',       shape: 'box',     color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'dbprod',    label: '🗄️ DB Prod',           shape: 'database',color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'dbdev',     label: '🧪 DB Dev/Test',       shape: 'database',color: { background: '#1a2744', border: '#5580aa' }, font: { color: '#aaa', size: 9 }, borderWidth: 1 },
            { id: 'docker',    label: '🐳 Docker Registry',   shape: 'box',     color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'cicd',      label: '⚙️ CI/CD Pipeline',    shape: 'box',     color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'npm',       label: '📦 npm Registry',      shape: 'box',     color: { background: '#1a2744', border: '#5580aa' }, font: { color: '#aaa', size: 9 }, borderWidth: 1 },
            { id: 'github',    label: '🐙 GitHub Repos',      shape: 'box',     color: { background: '#0f2847', border: '#3a90e8' }, font: { color: '#c5dcf5', size: 10 }, borderWidth: 2 },
            { id: 'vault',     label: '🔑 Secrets Vault',     shape: 'box',     color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 2 },
            { id: 'slack',     label: '💬 Slack/Comms',        shape: 'box',     color: { background: '#1a2744', border: '#5580aa' }, font: { color: '#aaa', size: 9 }, borderWidth: 1 },
            { id: 'jira',      label: '📋 Jira/Tickets',      shape: 'box',     color: { background: '#1a2744', border: '#5580aa' }, font: { color: '#aaa', size: 9 }, borderWidth: 1 },
            { id: 'aws',       label: '☁️ AWS Prod',           shape: 'box',     color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 2 },
        ]);

        const edges = new vis.DataSet([
            { from: 'internet', to: 'apigw',  arrows: 'to', color: { color: '#3a90e8', opacity: .5 }, width: 2 },
            { from: 'apigw',    to: 'k8s',    arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'apigw',    to: 'vault',  arrows: 'to', dashes: true, color: { color: '#d97706', opacity: .3 } },
            { from: 'k8s',      to: 'dbprod', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'k8s',      to: 'docker', arrows: 'to', color: { color: '#3a90e8', opacity: .3 } },
            { from: 'k8s',      to: 'aws',    arrows: 'to', color: { color: '#d97706', opacity: .3 } },
            { from: 'dbprod',   to: 'dbdev',  arrows: 'to', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'docker',   to: 'cicd',   arrows: 'from', color: { color: '#3a90e8', opacity: .3 } },
            { from: 'cicd',     to: 'github', arrows: 'from', color: { color: '#3a90e8', opacity: .3 } },
            { from: 'cicd',     to: 'npm',    arrows: 'to', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'vault',    to: 'slack',  dashes: true, color: { color: '#555', opacity: .2 } },
            { from: 'vault',    to: 'jira',   dashes: true, color: { color: '#555', opacity: .2 } },
        ]);

        this.networkNodes = nodes;

        const options = {
            nodes: { shape: 'box', margin: 10, shadow: { enabled: true, color: 'rgba(0,0,0,.3)', size: 8 } },
            edges: { smooth: { type: 'cubicBezier', forceDirection: 'horizontal' } },
            layout: { hierarchical: { direction: 'LR', sortMethod: 'directed', levelSeparation: 160, nodeSpacing: 70 } },
            physics: false,
            interaction: { hover: true, tooltipDelay: 200, zoomView: false },
        };

        this.network = new vis.Network(container, { nodes, edges }, options);

        // Click on node shows info
        this.network.on('click', (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = nodes.get(nodeId);
                this.showToast(`${node.label}`, 'info');
            }
        });
    }

    updateNetworkNodeColor(nodeId, status) {
        if (!this.networkNodes) return;
        const colors = {
            safe:        { background: '#0f2847', border: '#3a90e8' },
            compromised: { background: '#3c0d0d', border: '#e83a3a' },
            defended:    { background: '#0a2e1a', border: '#2d9f4f' },
        };
        const c = colors[status] || colors.safe;
        this.networkNodes.update({ id: nodeId, color: c });
    }

    // ── SortableJS — Drag-and-Drop ──────────────────────────────
    initSortable() {
        const hand = document.getElementById('myHand');
        const dropZone = document.getElementById('dropZone');
        if (!hand || !dropZone) return;

        this.sortable = new Sortable(hand, {
            group: { name: 'cards', pull: 'clone', put: false },
            sort: false,
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            filter: '.cb-drop-zone',
            onStart: () => dropZone.classList.add('drag-over'),
            onEnd: () => dropZone.classList.remove('drag-over'),
        });

        new Sortable(dropZone, {
            group: { name: 'cards', pull: false, put: true },
            animation: 200,
            onAdd: (evt) => {
                const cardEl = evt.item;
                const teamCardId = cardEl.dataset?.teamCardId;
                if (teamCardId) {
                    this.selectedCard = {
                        teamCardId: parseInt(teamCardId),
                        cardName: cardEl.dataset.cardName || '',
                        cost: parseInt(cardEl.dataset.cost || 0),
                    };
                    this.showPlayModal();
                }
                // Remove the cloned element from drop zone
                evt.item.remove();
                dropZone.classList.remove('drag-over');
            },
        });
    }

    // ── Event Binding ───────────────────────────────────────────
    bindEvents() {
        document.addEventListener('click', (e) => {
            // Card click to play
            const cardFlip = e.target.closest('.cb-card-flip[data-team-card-id]');
            if (cardFlip) {
                // Double click = flip, single click = select
                this.selectCard(cardFlip);
                return;
            }
            // Flip on right-click
        });

        document.addEventListener('dblclick', (e) => {
            const cardFlip = e.target.closest('.cb-card-flip');
            if (cardFlip) {
                cardFlip.classList.toggle('flipped');
            }
        });
    }

    selectCard(cardEl) {
        document.querySelectorAll('.cb-card-flip.selected').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        this.selectedCard = {
            teamCardId: parseInt(cardEl.dataset.teamCardId),
            cardName: cardEl.dataset.cardName || '',
            cost: parseInt(cardEl.dataset.cost || 0),
        };
        this.showPlayModal();
    }

    showPlayModal() {
        if (!this.selectedCard) return;
        const modal = document.getElementById('playCardModal');
        if (!modal) return;
        document.getElementById('playCardName').textContent = this.selectedCard.cardName;
        document.getElementById('playCardCost').textContent = this.selectedCard.cost;
        new bootstrap.Modal(modal).show();
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
        this.renderTurnIndicator(s);
        this.renderTeam('blue', s.blueTeam);
        this.renderTeam('red', s.redTeam);
        this.renderHand(s);
        this.renderActionLog(s);
        this.renderScoreBar(s);
        if (s.role === 'moderator') this.renderModPanel(s);
        if (s.round?.eventCard) this.renderEventCard(s.round.eventCard);
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
        if (el('cbBlueScore')) el('cbBlueScore').textContent = s.blueTeam?.score ?? 0;
        if (el('cbRedScore'))  el('cbRedScore').textContent = s.redTeam?.score ?? 0;
    }

    renderTurnIndicator(s) {
        const phase = s.session.currentPhase;
        const banner = document.getElementById('turnBanner');
        const bluePanel = document.getElementById('bluePanel');
        const redPanel = document.getElementById('redPanel');
        if (!banner || !bluePanel || !redPanel) return;

        // Reset classes
        bluePanel.classList.remove('active-turn', 'waiting');
        redPanel.classList.remove('active-turn', 'waiting');

        if (phase === 2) {
            // Red Team plays
            banner.className = 'cb-turn-banner red';
            banner.innerHTML = '<i class="bi bi-bug me-2"></i>RED TEAM JOUE <i class="bi bi-bug ms-2"></i>';
            redPanel.classList.add('active-turn');
            bluePanel.classList.add('waiting');
        } else if (phase === 3) {
            // Blue Team plays
            banner.className = 'cb-turn-banner blue';
            banner.innerHTML = '<i class="bi bi-shield me-2"></i>BLUE TEAM JOUE <i class="bi bi-shield ms-2"></i>';
            bluePanel.classList.add('active-turn');
            redPanel.classList.add('waiting');
        } else if (phase === 1) {
            banner.className = 'cb-turn-banner neutral';
            banner.innerHTML = '<i class="bi bi-coin me-2"></i>DISTRIBUTION DES RESSOURCES';
        } else if (phase === 4) {
            banner.className = 'cb-turn-banner neutral';
            banner.innerHTML = '<i class="bi bi-lightning me-2"></i>TIRAGE ÉVÉNEMENT';
        } else if (phase === 5) {
            banner.className = 'cb-turn-banner neutral';
            banner.innerHTML = '<i class="bi bi-trophy me-2"></i>SCORING & BILAN';
        } else {
            banner.className = 'cb-turn-banner neutral';
            banner.innerHTML = '<i class="bi bi-hourglass me-2"></i>EN ATTENTE';
        }

        // Flash banner on phase change
        if (phase !== this.lastPhase && this.lastPhase !== -1) {
            banner.style.animation = 'none';
            banner.offsetHeight; // trigger reflow
            banner.style.animation = 'banner-pulse 2s ease-in-out infinite';
        }
        this.lastPhase = phase;
    }

    renderTeam(type, team) {
        if (!team) return;
        const prefix = type;
        const el = id => document.getElementById(id);
        if (el(`${prefix}Tokens`))     el(`${prefix}Tokens`).textContent = team.tokens;
        if (el(`${prefix}ShopTokens`)) el(`${prefix}ShopTokens`).textContent = team.shopTokens;
        if (el(`${prefix}Score`))      el(`${prefix}Score`).textContent = team.score;
        if (el(`${prefix}HandCount`))  el(`${prefix}HandCount`).textContent = team.handCount;

        const playersList = el(`${prefix}Players`);
        if (playersList && team.players) {
            playersList.innerHTML = team.players.map(p =>
                `<div class="d-flex align-items-center gap-2 mb-1">
                    <div class="cb-player-avatar" style="background:${type === 'blue' ? '#3a90e8' : '#e83a3a'};">${p.initials}</div>
                    <span class="small">${p.name}${p.isCaptain ? ' <i class="bi bi-star-fill text-warning" style="font-size:.6rem;"></i>' : ''}</span>
                </div>`
            ).join('');
        }

        const activeList = el(`${prefix}Active`);
        if (activeList && team.activeCards) {
            activeList.innerHTML = team.activeCards.length > 0 ? team.activeCards.map(tc =>
                `<div class="d-flex align-items-center gap-2 mb-1 p-1 rounded" style="background:rgba(255,255,255,.04);font-size:.75rem;">
                    <span class="cb-${type === 'blue' ? 'defend' : 'compromise'}-dot" style="width:6px;height:6px;border-radius:50%;background:${type === 'blue' ? '#3a90e8' : '#e83a3a'};box-shadow:0 0 6px ${type === 'blue' ? '#3a90e8' : '#e83a3a'};"></span>
                    <span>${tc.card.name}</span>
                    ${tc.remainingTurns ? `<span class="ms-auto badge bg-dark">${tc.remainingTurns}t</span>` : ''}
                </div>`
            ).join('') : '<div class="small text-white-50">Aucune</div>';
        }
    }

    renderHand(s) {
        const container = document.getElementById('myHand');
        if (!container) return;

        const myTeam = s.myTeamType === 'blue' ? s.blueTeam : (s.myTeamType === 'red' ? s.redTeam : null);

        if (!myTeam?.hand) {
            // Keep drop zone but show message
            const dropZone = container.querySelector('.cb-drop-zone');
            const msg = s.role === 'moderator' ? '<i class="bi bi-star-fill text-warning me-1"></i> Vous êtes le modérateur' : 'Vous n\'êtes pas assigné';
            container.innerHTML = `<div class="text-white-50 small p-3">${msg}</div>`;
            if (dropZone) container.appendChild(dropZone);
            return;
        }

        // Build cards + drop zone
        const cardsHtml = myTeam.hand.map(tc => this.renderCardHTML(tc.card, tc.id)).join('');
        const dropZoneHtml = `<div class="cb-drop-zone" id="dropZone"><i class="bi bi-bullseye" style="font-size:1.5rem;opacity:.3;"></i><div class="mt-1">Glissez une carte ici<br>pour jouer</div></div>`;

        container.innerHTML = cardsHtml + dropZoneHtml;

        // Reinitialize sortable after rendering
        this.initSortable();
    }

    renderCardHTML(card, teamCardId = null) {
        const cssClass = card.cssClass || ('card-' + card.type);
        const dataAttrs = teamCardId
            ? `data-team-card-id="${teamCardId}" data-card-name="${this.escHtml(card.name)}" data-cost="${card.cost}"`
            : '';

        return `
        <div class="cb-card-flip" ${dataAttrs}>
            <div class="cb-card-inner">
                <div class="cb-card-front cb-card ${cssClass}">
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
                </div>
                <div class="cb-card-back">
                    <div class="cb-card-back-logo"><span class="blue">CYBER</span><span class="red">BREACH</span><br><span style="font-size:.6rem;opacity:.4;">DevCo v2</span></div>
                </div>
            </div>
        </div>`;
    }

    renderScoreBar(s) {
        const el = id => document.getElementById(id);
        const bScore = s.blueTeam?.score ?? 0;
        const rScore = s.redTeam?.score ?? 0;
        const total = Math.max(bScore + rScore, 1);

        if (el('blueScoreBar')) el('blueScoreBar').textContent = bScore;
        if (el('redScoreBar'))  el('redScoreBar').textContent = rScore;
        if (el('blueScoreFill')) el('blueScoreFill').style.width = `${(bScore / total) * 100}%`;
        if (el('redScoreFill'))  el('redScoreFill').style.width = `${(rScore / total) * 100}%`;
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
        const sess = s.session;
        const el = id => document.getElementById(id);
        if (el('btnStartRound')) el('btnStartRound').disabled = sess.status === 'active' && sess.currentPhase > 0 && sess.currentPhase < 5;
        if (el('btnAdvancePhase')) el('btnAdvancePhase').disabled = sess.status !== 'active';
        if (el('btnDrawEvent')) el('btnDrawEvent').disabled = sess.currentPhase !== 4;
    }

    renderEventCard(event) {
        const container = document.getElementById('eventDisplay');
        if (!container || !event) return;
        const cssMap = { danger: 'card-danger', success: 'card-success', joker: 'card-joker', situation: 'card-situation', alerte: 'card-alerte' };
        const css = cssMap[event.subtype] || 'card-danger';
        container.innerHTML = `
        <div class="cb-event-display ${css}" style="border-color:inherit;">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;opacity:.7;margin-bottom:.3rem;">
                Événement${event.subtype ? ' — ' + event.subtype.charAt(0).toUpperCase() + event.subtype.slice(1) : ''}
            </div>
            <div style="font-family:'Space Mono',monospace;font-weight:700;font-size:1rem;margin-bottom:.5rem;">${event.name}</div>
            <div style="font-size:.85rem;margin-bottom:.5rem;">${event.description}</div>
            <div style="font-family:'Space Mono',monospace;font-size:.8rem;padding:.4rem .8rem;border-radius:6px;background:rgba(255,255,255,.08);display:inline-block;">${event.effect}</div>
        </div>`;
        container.style.display = 'block';
    }

    // ── Utilities ────────────────────────────────────────────────
    escHtml(s) { return s.replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

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
