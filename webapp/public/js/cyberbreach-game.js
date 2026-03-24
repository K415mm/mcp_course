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
        internet: '🌐 Internet',
        laptop_dev: '💻 Dev Laptop', laptop_ex: '💻 Ex-Emp Laptop', laptop_ceo: '💻 CEO Laptop', laptop_finance: '💻 Finance Laptop', laptops: '💻 Laptops',
        apigw: '🛡️ API Gateway', apigw2: '🛡️ API Gateway v2', aws_ec2: '☁️ AWS EC2', aws_s3: '🪣 AWS S3', aws_iam: '🔑 AWS IAM', vpn: '🔒 VPN', rdp: '🖥️ RDP', firewall_it_ot: '🧱 IT/OT FW',
        dbprod: '🗄️ DB Prod', o365: '✉️ Office 365', bank: '🏦 Bank', slack: '💬 Slack', jira: '📋 Jira', vault: '🔑 Vault', backup: '💾 Veeam', dns_c2: '📡 DNS C2', ad: '🏠 Domain Ctrl',
        jenkins: '⚙️ Jenkins', gitlab: '⚙️ GitLab CI', github: '🐙 GitHub', npm: '📦 npm', docker: '🐳 Docker', k8s: '☸️ K8s',
        scada: '🏭 SCADA', plc: '🔧 PLC', hmi: '💻 HMI', sis: '⚠️ SIS',
    };

    static NODE_TO_DISPLAY = {
        internet: 'Internet',
        laptop_dev: 'Developer Laptop', laptop_ex: 'Ex-Employee Laptop', laptop_ceo: 'CEO Laptop', laptop_finance: 'Finance Laptop', laptops: 'Employee Laptops',
        apigw: 'API Gateway', apigw2: 'API Gateway v2', aws_ec2: 'AWS EC2', aws_s3: 'AWS S3', aws_iam: 'AWS IAM', vpn: 'VPN Gateway', rdp: 'RDP Gateway', firewall_it_ot: 'IT/OT Firewall',
        dbprod: 'DB Prod', o365: 'Office 365', bank: 'Bank Portal', slack: 'Slack', jira: 'Jira', vault: 'HashiCorp Vault', backup: 'Veeam Backup', dns_c2: 'DNS C2', ad: 'Domain Controller',
        jenkins: 'Jenkins CI', gitlab: 'GitLab CI', github: 'GitHub Enterprise', npm: 'npm Registry', docker: 'Docker Hub', k8s: 'K8s Cluster',
        scada: 'SCADA Server', plc: 'PLC Controllers', hmi: 'HMI Panel', sis: 'Safety System (SIS)',
    };

    static SCENARIO_NODES = {
        1: ['apigw', 'jenkins', 'github', 'vault', 'aws_ec2', 'aws_s3', 'slack', 'jira', 'laptop_dev'],
        2: ['apigw', 'npm', 'docker', 'gitlab', 'k8s', 'aws_ec2', 'dbprod'],
        3: ['vpn', 'laptop_ex', 'vault', 'github', 'aws_iam', 'aws_ec2', 'dbprod'],
        4: ['apigw2', 'k8s', 'dbprod', 'aws_ec2', 'docker', 'slack', 'vault'],
        5: ['o365', 'laptop_ceo', 'laptop_finance', 'bank', 'slack'],
        6: ['rdp', 'ad', 'dbprod', 'backup', 'laptops'],
        7: ['dns_c2', 'apigw', 'k8s', 'aws_ec2', 'vault', 'dbprod', 'slack', 'github'],
        8: ['apigw', 'firewall_it_ot', 'scada', 'plc', 'hmi', 'sis', 'dbprod']
    };

    // ── vis-network Infrastructure Map ──────────────────────────
    initNetworkMap() {
        const container = document.getElementById('networkMap');
        if (!container) return;

        const baseColor = { background: '#0f2847', border: '#3a90e8' };
        const secColor  = { background: '#1a2744', border: '#5580aa' };

        const allNodesData = [
            { id: 'internet', label: '🌐 Internet', shape: 'diamond', color: { background: '#1a2744', border: '#3a90e8' }, font: { color: '#fff', size: 12 }, borderWidth: 2, size: 30 },
            // Endpoints
            { id: 'laptop_dev', label: '💻 Dev Laptop', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'laptop_ex', label: '💻 Ex-Emp Laptop', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'laptop_ceo', label: '💻 CEO Laptop', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'laptop_finance', label: '💻 Finance Laptop', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'laptops', label: '💻 Laptops', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            // Infra & Cloud
            { id: 'apigw', label: '🛡️ API Gateway', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'apigw2', label: '🛡️ API Gateway v2', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'aws_ec2', label: '☁️ AWS EC2', shape: 'box', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'aws_s3', label: '🪣 AWS S3', shape: 'database', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'aws_iam', label: '🔑 AWS IAM', shape: 'box', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'vpn', label: '🔒 VPN', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'rdp', label: '🖥️ RDP', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'firewall_it_ot', label: '🧱 IT/OT FW', shape: 'box', color: { background: '#2d1f0e', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            // Data & Apps
            { id: 'dbprod', label: '🗄️ DB Prod', shape: 'database', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'o365', label: '✉️ Office 365', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'bank', label: '🏦 Bank', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'slack', label: '💬 Slack', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'jira', label: '📋 Jira', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'vault', label: '🔑 Vault', shape: 'box', color: { background: '#0f2847', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'backup', label: '💾 Veeam', shape: 'database', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'dns_c2', label: '📡 DNS C2', shape: 'diamond', color: { background: '#3c0d0d', border: '#e83a3a' }, font: { color: '#fff', size: 12 }, borderWidth: 2, size: 30 },
            { id: 'ad', label: '🏠 Domain Ctrl', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            // DevOps
            { id: 'jenkins', label: '⚙️ Jenkins', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'gitlab', label: '⚙️ GitLab', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'github', label: '🐙 GitHub', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'npm', label: '📦 npm', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'docker', label: '🐳 Docker', shape: 'box', color: secColor, font: { color: '#aaa', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'k8s', label: '☸️ K8s', shape: 'box', color: baseColor, font: { color: '#c5dcf5', size: 11 }, borderWidth: 2, size: 20 },
            // OT
            { id: 'scada', label: '🏭 SCADA', shape: 'box', color: { background: '#2d1f0e', border: '#d97706' }, font: { color: '#fcd1a6', size: 11 }, borderWidth: 2, size: 20 },
            { id: 'plc', label: '🔧 PLC', shape: 'box', color: { background: '#2d1f0e', border: '#b45309' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 2, size: 18 },
            { id: 'hmi', label: '💻 HMI', shape: 'box', color: { background: '#2d1f0e', border: '#b45309' }, font: { color: '#fcd1a6', size: 10 }, borderWidth: 1, size: 18 },
            { id: 'sis', label: '⚠️ SIS', shape: 'box', color: { background: '#2d1f0e', border: '#dc2626' }, font: { color: '#fca5a5', size: 11 }, borderWidth: 2, size: 20 },
        ];

        let filteredNodes = allNodesData;
        if (this.scenario && CyberBreachGame.SCENARIO_NODES[this.scenario]) {
            const allowed = CyberBreachGame.SCENARIO_NODES[this.scenario];
            filteredNodes = allNodesData.filter(n => allowed.includes(n.id) || n.id === 'internet');
        }
        const nodes = new vis.DataSet(filteredNodes);

        const allEdgesData = [
            { from: 'internet', to: 'apigw',  arrows: 'to', color: { color: '#3a90e8', opacity: .5 }, width: 2 },
            { from: 'internet', to: 'apigw2', arrows: 'to', color: { color: '#3a90e8', opacity: .5 }, width: 2 },
            { from: 'internet', to: 'vpn', arrows: 'to', color: { color: '#3a90e8', opacity: .5 }, width: 2 },
            { from: 'internet', to: 'rdp', arrows: 'to', color: { color: '#3a90e8', opacity: .5 }, width: 2 },
            { from: 'internet', to: 'o365', arrows: 'to', color: { color: '#555', opacity: .5 }, width: 2 },
            { from: 'apigw', to: 'slack', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'apigw2', to: 'slack', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'slack', to: 'jira', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'vault', to: 'slack', dashes: true, color: { color: '#555', opacity: .2 } },
            { from: 'laptop_dev', to: 'github', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'github', to: 'jenkins', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'github', to: 'gitlab', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'jenkins', to: 'aws_ec2', arrows: 'to', color: { color: '#d97706', opacity: .3 } },
            { from: 'gitlab', to: 'k8s', arrows: 'to', color: { color: '#3a90e8', opacity: .3 } },
            { from: 'npm', to: 'gitlab', arrows: 'to', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'docker', to: 'gitlab', arrows: 'to', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'apigw', to: 'vault', dashes: true, color: { color: '#d97706', opacity: .3 } },
            { from: 'vault', to: 'aws_ec2', dashes: true, color: { color: '#d97706', opacity: .3 } },
            { from: 'aws_ec2', to: 'dbprod', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'k8s', to: 'dbprod', arrows: 'to', color: { color: '#3a90e8', opacity: .4 } },
            { from: 'aws_ec2', to: 'aws_s3', arrows: 'to', color: { color: '#d97706', opacity: .4 } },
            { from: 'aws_iam', to: 'aws_ec2', arrows: 'to', dashes: true, color: { color: '#d97706', opacity: .4 } },
            { from: 'vpn', to: 'ad', arrows: 'to', color: { color: '#555', opacity: .4 } },
            { from: 'rdp', to: 'ad', arrows: 'to', color: { color: '#555', opacity: .4 } },
            { from: 'ad', to: 'laptops', arrows: 'to', color: { color: '#555', opacity: .4 } },
            { from: 'laptops', to: 'dbprod', arrows: 'to', color: { color: '#3a90e8', opacity: .3 } },
            { from: 'ad', to: 'backup', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'o365', to: 'laptop_ceo', arrows: 'to', color: { color: '#555', opacity: .4 } },
            { from: 'laptop_ceo', to: 'laptop_finance', arrows: 'to', dashes: true, color: { color: '#d97706', opacity: .4 } },
            { from: 'laptop_finance', to: 'bank', arrows: 'to', color: { color: '#2d9f4f', opacity: .4 } },
            { from: 'laptop_ex', to: 'vpn', arrows: 'to', color: { color: '#555', opacity: .4 } },
            { from: 'internet', to: 'dns_c2', arrows: 'from', color: { color: '#e83a3a', opacity: .5 }, width: 2 },
            { from: 'dns_c2', to: 'k8s', arrows: 'to', dashes: true, color: { color: '#e83a3a', opacity: .3 } },
            { from: 'apigw', to: 'firewall_it_ot', arrows: 'to', color: { color: '#d97706', opacity: .4 } },
            { from: 'dbprod', to: 'firewall_it_ot', arrows: 'to', dashes: true, color: { color: '#555', opacity: .3 } },
            { from: 'firewall_it_ot', to: 'scada', arrows: 'to', color: { color: '#d97706', opacity: .4 } },
            { from: 'scada', to: 'plc', arrows: 'to', color: { color: '#d97706', opacity: .4 } },
            { from: 'scada', to: 'hmi', arrows: 'to', color: { color: '#d97706', opacity: .3 } },
            { from: 'plc', to: 'sis', arrows: 'to', dashes: true, color: { color: '#dc2626', opacity: .4 } },
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

        // Moderator only: Hover node to show effectiveness if card selected
        this.network.on('hoverNode', (params) => {
            if (this.role === 'moderator' && this.selectedCard && this.selectedCard._effectivenessData) {
                const eff = this.selectedCard._effectivenessData[params.node];
                if (eff) {
                    container.title = `${eff.nodeName}: ${eff.effectiveness}% → ${eff.points} pts${eff.isCritical ? ' ★ Critique' : ''}`;
                }
            }
        });
        this.network.on('blurNode', () => { container.title = ''; });

        // ── HTML5 Drag & Drop onto Map ──
        container.addEventListener('dragover', (e) => {
            e.preventDefault(); 
            e.dataTransfer.dropEffect = 'copy';
            
            if (this.role === 'moderator' && this.selectedCard && this.selectedCard._effectivenessData) {
                const rect = container.getBoundingClientRect();
                const pos = this.network.DOMtoCanvas({ x: e.clientX - rect.left, y: e.clientY - rect.top });
                const nodeId = this.network.getNodeAt(pos);
                if (nodeId) {
                    const eff = this.selectedCard._effectivenessData[nodeId];
                    if (eff) container.title = `${eff.nodeName}: ${eff.effectiveness}% → ${eff.points} pts${eff.isCritical ? ' ★ Critique' : ''}`;
                    else container.title = '';
                } else container.title = '';
            }
        });

        container.addEventListener('dragleave', () => { container.title = ''; });

        container.addEventListener('drop', (e) => {
            e.preventDefault();
            this.network.unselectAll();
            container.title = '';
            
            const cardDataStr = e.dataTransfer.getData('application/json');
            if (!cardDataStr) return;
            
            const rect = container.getBoundingClientRect();
            const pos = this.network.DOMtoCanvas({ x: e.clientX - rect.left, y: e.clientY - rect.top });
            const nodeId = this.network.getNodeAt(pos);

            // Inherit the Selected Card initialized in dragstart
            if (nodeId && nodeId !== 'internet') {
                const displayName = CyberBreachGame.NODE_TO_DISPLAY[nodeId] || nodeId;
                this.selectedCard.targetSystem = displayName;
            } else {
                this.selectedCard.targetSystem = null; 
            }
            
            this.showPlayModal();
        });
    }

    // Zoom controls
    zoomIn()  { if (this.network) this.network.moveTo({ scale: this.network.getScale() * 1.3 }); }
    zoomOut() { if (this.network) this.network.moveTo({ scale: this.network.getScale() / 1.3 }); }
    zoomFit() { if (this.network) this.network.fit({ animation: { duration: 300 } }); }

    // Toggle Network Layout
    toggleNetworkLayout() {
        if (!this.network) return;
        this.isFlatView = this.isFlatView === undefined ? false : !this.isFlatView; // defaults to true in init, so first click goes to false (organic)
        
        if (this.isFlatView) {
            this.network.setOptions({
                layout: { hierarchical: { direction: 'LR', sortMethod: 'directed', levelSeparation: 160, nodeSpacing: 70 } },
                physics: false
            });
        } else {
            this.network.setOptions({
                layout: { hierarchical: false },
                physics: { 
                    enabled: true, 
                    solver: 'forceAtlas2Based',
                    forceAtlas2Based: { gravitationalConstant: -100, centralGravity: 0.01, springLength: 100, springConstant: 0.08 }
                }
            });
        }
        setTimeout(() => this.network.fit({ animation: { duration: 500 } }), 100);
        
        const btn = document.getElementById('btnLayoutToggle');
        if (btn) {
            btn.innerHTML = this.isFlatView ? '<i class="bi bi-diagram-3"></i>' : '<i class="bi bi-diagram-2"></i>';
            btn.title = this.isFlatView ? 'Vue Network (Organique)' : 'Vue Flat (Hiérarchique)';
            btn.className = this.isFlatView ? 'btn btn-xs btn-outline-info me-2' : 'btn btn-xs btn-info text-dark me-2';
        }
        this.showToast(`Vue modifiée : ${this.isFlatView ? 'Hiérarchique' : 'Organique'}`, 'info');
    }

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
        // Double-click to flip
        document.addEventListener('dblclick', (e) => {
            const cardFlip = e.target.closest('.cb-card-flip');
            if (cardFlip) cardFlip.classList.toggle('flipped');
        });

        // HTML5 Drag Start
        document.addEventListener('dragstart', (e) => {
            const cardEl = e.target.closest('.cb-card-flip[data-team-card-id]');
            if (cardEl) {
                this.selectCard(cardEl); 
                const cardData = {
                    teamCardId: parseInt(cardEl.dataset.teamCardId),
                    cardName: cardEl.dataset.cardName || '',
                    cardType: cardEl.dataset.cardType || '',
                    cost: parseInt(cardEl.dataset.cost || 0),
                    basePoints: parseInt(cardEl.dataset.basePoints || 0),
                    targetSystem: null,
                };
                e.dataTransfer.setData('application/json', JSON.stringify(cardData));
                e.dataTransfer.effectAllowed = 'copyMove';
            }
        });

        // HTML5 Drag End
        document.addEventListener('dragend', (e) => {
            this.resetNodeHighlights();
            const container = document.getElementById('networkMap');
            if (container) container.title = '';
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

        // Modérateur uniquement: Afficher visuellement l'efficacité
        if (this.role === 'moderator') {
            await this.highlightNodeEffectiveness(
                this.selectedCard.cardName, this.selectedCard.cardType, this.selectedCard.basePoints
            );
        }
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
        <div class="cb-card-flip" ${dataAttrs} draggable="true">
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
                    <div class="cb-card-back-logo pb-2"><span class="blue">CYBER</span><span class="red">BREACH</span><br><span style="font-size:.6rem;opacity:.4;">DevCo v2</span></div>
                    ${card.mitre_id ? `
                    <div class="cb-mitre-info mt-3" style="font-size:0.6rem; color:#aaa; text-align:center; padding: 0 5px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                        <strong class="text-white">${card.mitre_id}</strong><br>
                        <span style="font-size:0.6rem; color:#c5dcf5">${card.mitre_name}</span><br>
                        <em style="font-size:0.55rem;">${card.mitre_description}</em>
                    </div>` : ''}
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
