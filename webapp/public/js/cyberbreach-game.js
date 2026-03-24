/**
 * CyberBreach Game Engine v3 — Card-to-Node Scoring, Node States, Zoom
 */
class CyberBreachGame {
    constructor(sessionId, csrfToken, role, scenario = null) {
        this.sessionId = sessionId;
        this.csrf = csrfToken;
        this.role = role;
        this.scenario = scenario ? parseInt(scenario) : null;
        this.state = null;
        this.selectedCard = null;
        this.pollInterval = null;
        this.network = null;
        this.networkNodes = null;
        this.POLL_MS = 3000;
        this.BASE = `/game/${sessionId}/api`;
        this.lastPhase = -1;
        this.effectivenessCache = {};
    }

    init() {
        this.startPolling();
        this.initNetworkMap();
        this.bindEvents();
    }

    destroy() {
        clearInterval(this.pollInterval);
        if (this.network) this.network.destroy();
    }

    // ── Polling ─────────────────────────────────────────────
    startPolling() {
        this.fetchState();
        this.pollInterval = setInterval(() => this.fetchState(), this.POLL_MS);
    }

    async fetchState() {
        try {
            const res = await fetch(`${this.BASE}/state`);
            if (!res.ok) return;
            this.state = await res.json();
            this.render();
        } catch (e) { console.warn('Poll failed:', e); }
    }

    // ── API Calls ───────────────────────────────────────────
    async api(endpoint, body = {}, method = 'POST') {
        try {
            const opts = {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
            };
            if (method === 'POST') opts.body = JSON.stringify(body);
            const url = method === 'GET' && Object.keys(body).length
                ? `${this.BASE}/${endpoint}?${new URLSearchParams(body)}`
                : `${this.BASE}/${endpoint}`;
            const res = await fetch(url, opts);
            const data = await res.json();
            if (data.success && method === 'POST') this.fetchState();
            return data;
        } catch (e) { return { success: false, error: 'Erreur réseau' }; }
    }

    async playCard(teamCardId, targetSystem) { return this.api('play-card', { team_card_id: teamCardId, target_system: targetSystem }); }
    async drawCard() { return this.api('draw-card'); }
    async buyFromShop(cardId) { return this.api('buy-shop', { card_id: cardId }); }
    async startRound() { return this.api('start-round'); }
    async advancePhase() { return this.api('advance-phase'); }
    async drawEvent() { return this.api('draw-event'); }
    async adjustTokens(teamType, amount) { return this.api('adjust-tokens', { team_type: teamType, amount }); }
    async dealHands() { return this.api('deal-hands'); }
    async endGame() {
        const r = await this.api('end-game');
        if (r.success) this.showToast(`Partie terminée ! Blue: ${r.blueScore} pts — Red: ${r.redScore} pts`, 'warning');
        return r;
    }

    async getCardEffectiveness(cardName, cardType, basePoints) {
        const key = `${cardName}_${cardType}_${basePoints}`;
        if (this.effectivenessCache[key]) return this.effectivenessCache[key];
        const data = await this.api('card-effectiveness', { card_name: cardName, card_type: cardType, base_points: basePoints }, 'GET');
        if (data.success) this.effectivenessCache[key] = data.targets;
        return data.targets || {};
    }

    // ── NODE MAP ↔ Display Name Mapping ────────────────────────
    static NODE_MAP = {
        internet: '🌐 Internet', apigw: '🛡️ API Gateway', k8s: '☸️ K8s Cluster',
        dbprod: '🗄️ DB Prod', dbdev: '🧪 DB Dev/Test', docker: '🐳 Docker Registry',
        cicd: '⚙️ CI/CD Pipeline', npm: '📦 npm Registry', github: '🐙 GitHub Repos',
        vault: '🔑 Secrets Vault', slack: '💬 Slack/Comms', jira: '📋 Jira/Tickets',
        aws: '☁️ AWS Prod',
        scada: '🏭 SCADA', plc: '🔧 PLC Controllers', hmi: '💻 HMI', sis: '⚠️ SIS',
    };

    static NODE_TO_DISPLAY = {
        internet: 'Internet', apigw: 'API Gateway', k8s: 'Kubernetes Cluster',
        dbprod: 'DB Production', dbdev: 'DB Dev/Test', docker: 'Docker Registry',
        cicd: 'CI/CD Pipeline', npm: 'npm Registry', github: 'GitHub Repos',
        vault: 'Secrets Vault', slack: 'Slack/Comms', jira: 'Jira/Tickets',
        aws: 'AWS Production',
        scada: 'SCADA System', plc: 'PLC Controllers', hmi: 'HMI Interface', sis: 'Safety Systems (SIS)',
    };

    static SCENARIO_NODES = {
        1: ['apigw', 'cicd', 'github', 'vault', 'aws', 'slack', 'jira', 'dbprod'],
        2: ['apigw', 'k8s', 'docker', 'npm', 'cicd', 'github', 'aws', 'dbprod'],
        3: ['apigw', 'aws', 'vault', 'github', 'cicd', 'slack', 'jira', 'dbprod'],
        4: ['apigw', 'k8s', 'dbprod', 'aws', 'slack', 'vault', 'docker'],
        5: ['apigw', 'slack', 'jira', 'vault', 'github', 'dbprod'],
        6: ['apigw', 'k8s', 'docker', 'dbprod', 'dbdev', 'github', 'cicd', 'aws'],
        7: ['apigw', 'k8s', 'aws', 'github', 'cicd', 'docker', 'vault', 'dbprod', 'slack', 'jira'],
        8: ['apigw', 'k8s', 'scada', 'plc', 'hmi', 'sis', 'slack', 'aws', 'dbprod']
    };

    // ── vis-network Infrastructure Map ──────────────────────────
    initNetworkMap() {
        const container = document.getElementById('networkMap');
        if (!container) return;

        const baseColor = { background: '#0f2847', border: '#3a90e8' };
        const secColor  = { background: '#1a2744', border: '#5580aa' };

        const allNodesData = [
            { id: 'internet', label: '🌐 Internet',       shape: 'diamond', color: { background: '#1a2744', border: '#3a90e8' }, font: { color: '#fff', size: 12 }, borderWidth: 2, size: 30 },
            { id: 'apigw',    label: '🛡️ API Gateway',    shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'k8s',      label: '☸️ K8s Cluster',    shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'dbprod',   label: '🗄️ DB Prod',        shape: 'database', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'dbdev',    label: '🧪 DB Dev/Test',    shape: 'database', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'docker',   label: '🐳 Docker Registry', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'cicd',     label: '⚙️ CI/CD Pipeline', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'npm',      label: '📦 npm Registry',   shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'github',   label: '🐙 GitHub Repos',   shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'vault',    label: '🔑 Secrets Vault',  shape: 'box', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'slack',    label: '💬 Slack/Comms',     shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'jira',     label: '📋 Jira/Tickets',   shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'aws',      label: '☁️ AWS Prod',       shape: 'box', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'scada',    label: '🏭 SCADA',          shape: 'box', color: { background: '#2d1f0e', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'plc',      label: '🔧 PLC Controllers', shape: 'box', color: { background: '#2d1f0e', border: '#b45309' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 2, size: 18 },
            { id: 'hmi',      label: '💻 HMI Interface',  shape: 'box', color: { background: '#2d1f0e', border: '#b45309' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'sis',      label: '⚠️ Safety (SIS)',   shape: 'box', color: { background: '#2d1f0e', border: '#dc2626' }, font: { color: '#fca5a5', size: 11 }, borderWidth: 2, size: 20 },
        ];

        let filteredNodes = allNodesData;
        if (this.scenario && CyberBreachGame.SCENARIO_NODES[this.scenario]) {
            const allowed = CyberBreachGame.SCENARIO_NODES[this.scenario];
            filteredNodes = allNodesData.filter(n => allowed.includes(n.id) || n.id === 'internet');
        }
        const nodes = new vis.DataSet(filteredNodes);

        const allEdgesData = [
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
            { from: 'k8s',      to: 'scada',  arrows: 'to', dashes: true, color: { color: '#d97706', opacity: .3 } },
            { from: 'scada',    to: 'plc',    arrows: 'to', color: { color: '#d97706', opacity: .4 } },
            { from: 'scada',    to: 'hmi',    arrows: 'to', color: { color: '#d97706', opacity: .3 } },
            { from: 'plc',      to: 'sis',    arrows: 'to', dashes: true, color: { color: '#dc2626', opacity: .4 } },
        ];

        const nodeIds = new Set(filteredNodes.map(n => n.id));
        const filteredEdges = allEdgesData.filter(e => nodeIds.has(e.from) && nodeIds.has(e.to));
        const edges = new vis.DataSet(filteredEdges);

        this.networkNodes = nodes;

        const options = {
            nodes: { shape: 'box', margin: 10, shadow: { enabled: true, color: 'rgba(0,0,0,.3)', size: 8 } },
            edges: { smooth: { type: 'cubicBezier', forceDirection: 'horizontal' } },
            layout: { hierarchical: { direction: 'LR', sortMethod: 'directed', levelSeparation: 160, nodeSpacing: 70 } },
            physics: false,
            interaction: { hover: true, tooltipDelay: 200, zoomView: true, dragView: true },
        };

        this.network = new vis.Network(container, { nodes, edges }, options);

        // Click node = select as target
        this.network.on('click', (params) => {
            if (params.nodes.length > 0 && this.selectedCard) {
                const nodeId = params.nodes[0];
                if (nodeId === 'internet') return;
                const displayName = CyberBreachGame.NODE_TO_DISPLAY[nodeId] || nodeId;
                this.selectedCard.targetSystem = displayName;
                this.showPlayModal();
            }
        });

        // Hover node = show effectiveness if card selected
        this.network.on('hoverNode', (params) => {
            if (this.selectedCard && this.selectedCard._effectivenessData) {
                const eff = this.selectedCard._effectivenessData[params.node];
                if (eff) {
                    container.title = `${eff.nodeName}: ${eff.effectiveness}% → ${eff.points} pts${eff.isCritical ? ' ★ Critique' : ''}`;
                }
            }
        });
        this.network.on('blurNode', () => { container.title = ''; });
    }

    // Zoom controls
    zoomIn()  { if (this.network) this.network.moveTo({ scale: this.network.getScale() * 1.3 }); }
    zoomOut() { if (this.network) this.network.moveTo({ scale: this.network.getScale() / 1.3 }); }
    zoomFit() { if (this.network) this.network.fit({ animation: { duration: 300 } }); }

    // Update node colors from state
    updateNodeStates(nodeStates) {
        if (!this.networkNodes) return;
        const stateColors = {
            safe:        { background: '#0f2847', border: '#3a90e8' },
            compromised: { background: '#3c0d0d', border: '#e83a3a' },
            defended:    { background: '#0a2e1a', border: '#2d9f4f' },
        };
        // Reset all active nodes (except internet) to safe
        const allIds = this.networkNodes.getIds().filter(id => id !== 'internet');
        allIds.forEach(id => {
            const st = nodeStates[id] || 'safe';
            const c = stateColors[st] || stateColors.safe;
            this.networkNodes.update({ id, color: c });
        });
    }

    // Highlight nodes by effectiveness when card is selected
    async highlightNodeEffectiveness(cardName, cardType, basePoints) {
        const targets = await this.getCardEffectiveness(cardName, cardType, basePoints);
        if (!targets || !this.networkNodes) return;

        this.selectedCard._effectivenessData = targets;

        Object.entries(targets).forEach(([nodeId, info]) => {
            if (info.effectiveness === 0) {
                this.networkNodes.update({ id: nodeId, borderWidth: 1, opacity: 0.3 });
            } else if (info.effectiveness >= 100) {
                const color = info.isCritical ? '#fbbf24' : '#2d9f4f';
                this.networkNodes.update({ id: nodeId, borderWidth: 4, color: { border: color }, opacity: 1 });
            } else if (info.effectiveness >= 80) {
                this.networkNodes.update({ id: nodeId, borderWidth: 3, color: { border: '#6366f1' }, opacity: 1 });
            } else {
                this.networkNodes.update({ id: nodeId, borderWidth: 2, color: { border: '#555' }, opacity: 0.7 });
            }
        });
    }

    // Reset node visuals to current state
    resetNodeHighlights() {
        if (this.state?.nodeStates) {
            this.updateNodeStates(this.state.nodeStates);
        }
        // Reset opacity & border width
        const allIds = this.networkNodes.getIds().filter(id => id !== 'internet');
        allIds.forEach(id => {
            this.networkNodes?.update({ id, borderWidth: 2, opacity: 1 });
        });
    }

    // ── Event Binding ───────────────────────────────────────
    bindEvents() {
        // Card click to select
        document.addEventListener('click', (e) => {
            const cardFlip = e.target.closest('.cb-card-flip[data-team-card-id]');
            if (cardFlip) {
                this.selectCard(cardFlip);
                return;
            }
        });

        // Double-click to flip
        document.addEventListener('dblclick', (e) => {
            const cardFlip = e.target.closest('.cb-card-flip');
            if (cardFlip) cardFlip.classList.toggle('flipped');
        });
    }

    async selectCard(cardEl) {
        document.querySelectorAll('.cb-card-flip.selected').forEach(c => c.classList.remove('selected'));
        cardEl.classList.add('selected');
        this.selectedCard = {
            teamCardId: parseInt(cardEl.dataset.teamCardId),
            cardName: cardEl.dataset.cardName || '',
            cardType: cardEl.dataset.cardType || '',
            cost: parseInt(cardEl.dataset.cost || 0),
            basePoints: parseInt(cardEl.dataset.basePoints || 0),
            targetSystem: null,
            _effectivenessData: null,
        };

        // Show effectiveness on nodes
        await this.highlightNodeEffectiveness(
            this.selectedCard.cardName, this.selectedCard.cardType, this.selectedCard.basePoints
        );

        // Show instruction
        this.showToast('Cliquez sur un noeud de l\'infrastructure pour cibler, ou cliquez "Jouer sans cible"', 'info');
    }

    showPlayModal() {
        if (!this.selectedCard) return;
        const modal = document.getElementById('playCardModal');
        if (!modal) return;
        document.getElementById('playCardName').textContent = this.selectedCard.cardName;
        document.getElementById('playCardCost').textContent = this.selectedCard.cost;

        // Show target & effectiveness
        const targetEl = document.getElementById('playTargetInfo');
        const effEl = document.getElementById('playEffectiveness');
        if (this.selectedCard.targetSystem) {
            const nodeId = Object.entries(CyberBreachGame.NODE_TO_DISPLAY)
                .find(([k,v]) => v === this.selectedCard.targetSystem)?.[0];
            const eff = this.selectedCard._effectivenessData?.[nodeId];
            if (targetEl) targetEl.textContent = this.selectedCard.targetSystem;
            if (effEl && eff) {
                const color = eff.effectiveness >= 100 ? '#2d9f4f' : eff.effectiveness >= 80 ? '#6366f1' : eff.effectiveness >= 50 ? '#d97706' : '#e83a3a';
                effEl.innerHTML = `<span style="color:${color};font-family:'Space Mono',monospace;font-weight:700;font-size:1.2rem;">${eff.effectiveness}%</span>` +
                    `<span class="text-white-50 ms-2">${eff.points} pts</span>` +
                    (eff.isCritical ? '<span class="badge bg-warning text-dark ms-2">★ Chemin critique</span>' : '');
            }
        } else {
            if (targetEl) targetEl.textContent = 'Aucune cible (action globale)';
            if (effEl) effEl.innerHTML = '<span class="text-white-50">50-100% selon le type de carte</span>';
        }

        // Set select value
        const select = document.getElementById('targetSystem');
        if (select && this.selectedCard.targetSystem) {
            select.value = this.selectedCard.targetSystem;
        }

        new bootstrap.Modal(modal).show();
    }

    async confirmPlay() {
        if (!this.selectedCard) return;
        const target = document.getElementById('targetSystem')?.value || this.selectedCard.targetSystem || null;
        const result = await this.playCard(this.selectedCard.teamCardId, target);
        const modal = bootstrap.Modal.getInstance(document.getElementById('playCardModal'));
        if (modal) modal.hide();
        if (result.success) {
            const effMsg = result.effectiveness !== undefined ? ` (${result.effectiveness}% efficacité)` : '';
            const critMsg = result.isCritical ? ' ★ Bonus critique!' : '';
            this.showToast(`Carte jouée ! +${result.points} pts${effMsg}${critMsg}`, 'success');
        } else {
            this.showToast(result.error || 'Erreur', 'danger');
        }
        this.selectedCard = null;
        this.resetNodeHighlights();
    }

    playWithoutTarget() {
        if (!this.selectedCard) return;
        this.selectedCard.targetSystem = null;
        this.showPlayModal();
    }

    // ── Rendering ───────────────────────────────────────────
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
        if (s.nodeStates) this.updateNodeStates(s.nodeStates);
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
        if (el('cbBlueScore')) el('cbBlueScore').textContent = s.blueTeam?.score ?? 0;
        if (el('cbRedScore'))  el('cbRedScore').textContent = s.redTeam?.score ?? 0;
    }

    renderTurnIndicator(s) {
        const phase = s.session.currentPhase;
        const banner = document.getElementById('turnBanner');
        const bluePanel = document.getElementById('bluePanel');
        const redPanel = document.getElementById('redPanel');
        if (!banner || !bluePanel || !redPanel) return;

        bluePanel.classList.remove('active-turn', 'waiting');
        redPanel.classList.remove('active-turn', 'waiting');

        if (phase === 2) {
            banner.className = 'cb-turn-banner red';
            banner.innerHTML = '<i class="bi bi-bug me-2"></i>RED TEAM JOUE <i class="bi bi-bug ms-2"></i>';
            redPanel.classList.add('active-turn');
            bluePanel.classList.add('waiting');
        } else if (phase === 3) {
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

        if (phase !== this.lastPhase && this.lastPhase !== -1) {
            banner.style.animation = 'none';
            banner.offsetHeight;
            banner.style.animation = 'banner-pulse 2s ease-in-out infinite';
        }
        this.lastPhase = phase;
    }

    renderTeam(type, team) {
        if (!team) return;
        const el = id => document.getElementById(id);
        if (el(`${type}Tokens`))     el(`${type}Tokens`).textContent = team.tokens;
        if (el(`${type}ShopTokens`)) el(`${type}ShopTokens`).textContent = team.shopTokens;
        if (el(`${type}Score`))      el(`${type}Score`).textContent = team.score;
        if (el(`${type}HandCount`))  el(`${type}HandCount`).textContent = team.handCount;

        const playersList = el(`${type}Players`);
        if (playersList && team.players) {
            playersList.innerHTML = team.players.map(p =>
                `<div class="d-flex align-items-center gap-2 mb-1">
                    <div class="cb-player-avatar" style="background:${type === 'blue' ? '#3a90e8' : '#e83a3a'};">${p.initials}</div>
                    <span class="small">${p.name}${p.isCaptain ? ' <i class="bi bi-star-fill text-warning" style="font-size:.6rem;"></i>' : ''}</span>
                </div>`
            ).join('');
        }

        const activeList = el(`${type}Active`);
        if (activeList && team.activeCards) {
            activeList.innerHTML = team.activeCards.length > 0 ? team.activeCards.map(tc =>
                `<div class="d-flex align-items-center gap-2 mb-1 p-1 rounded" style="background:rgba(255,255,255,.04);font-size:.75rem;">
                    <span style="width:6px;height:6px;border-radius:50%;background:${type === 'blue' ? '#3a90e8' : '#e83a3a'};box-shadow:0 0 6px ${type === 'blue' ? '#3a90e8' : '#e83a3a'};"></span>
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
            const msg = s.role === 'moderator' ? '<i class="bi bi-star-fill text-warning me-1"></i> Modérateur' : 'Non assigné';
            container.innerHTML = `<div class="text-white-50 small p-3">${msg}</div>`;
            return;
        }

        container.innerHTML = myTeam.hand.map(tc => this.renderCardHTML(tc.card, tc.id)).join('');
    }

    renderCardHTML(card, teamCardId = null) {
        const cssClass = card.cssClass || ('card-' + card.type);
        const dataAttrs = teamCardId
            ? `data-team-card-id="${teamCardId}" data-card-name="${this.escHtml(card.name)}" data-card-type="${card.type}" data-cost="${card.cost}" data-base-points="${card.points}"`
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
