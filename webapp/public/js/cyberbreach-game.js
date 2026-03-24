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
        dns_ext: '🌍 DNS External', github: '☁️ GitHub Cloud', o365: '☁️ Office 365', bank: '🏦 Bank Portal', supplier: '🏭 Supplier Portal', dns_c2: '📡 DNS C2',
        apigw: '🛡️ API Gateway', apigw2: '🛡️ API Gateway v2', apigw_legacy: '🛡️ Legacy API', vpn: '🔒 VPN', waf: '🧱 WAF', seg: '📧 Secure Email', rdp_jump: '🖥️ RDP Jump', web_front: '🌐 Web Front', fw_it_ot: '🧱 IT/OT FW', linux_jump: '🐧 Linux Jump', ot_jump: '🏭 OT Jump',
        ad_primary: '🏠 Primary AD', ad_sec: '🏠 Secondary AD', file_svr: '📁 File Server', nas: '💽 NAS', erp: '📊 ERP', intranet: '🏢 Intranet', wsus: '🔄 WSUS', siem: '👁️ Splunk SIEM', edr: '🛡️ EDR', pam: '🔑 PAM', vcenter: '☁️ vCenter', admin_portal: '⚙️ Admin Portal', email_svr: '📧 Email Server',
        laptop_dev_lead: '💻 Lead Dev Laptop', laptops_dev: '💻 Dev Laptops', laptop_pm: '💻 PM Laptop', laptops_ops: '💻 Ops Laptops', laptop_admin: '💻 Admin Laptop', laptop_ex: '💻 Ex-Emp Laptop', laptop_ceo: '💻 CEO Laptop', mobile_ceo: '📱 CEO Mobile', laptop_cfo: '💻 CFO Laptop', laptops_finance: '💻 Finance Laptops', laptops_hr: '💻 HR Laptops', laptops_sales: '💻 Sales Laptops', laptops_staff: '💻 Staff Laptops', laptops_eng: '💻 Eng Laptops', ot_eng_ws: '💻 OT Eng WS',
        jenkins_master: '⚙️ Jenkins Master', jenkins_workers: '⚙️ Jenkins Workers', gitlab: '⚙️ GitLab CI', github_ent: '🐙 GitHub Ent', npm: '📦 npm', docker_hub: '🐳 Docker Hub', docker_reg: '🐳 Docker Reg', vault: '🔑 Vault', nexus: '📦 Nexus Repo', sonar: '🔎 SonarQube',
        k8s_control: '☸️ K8s Control', k8s_workers: '☸️ K8s Workers', aws_ec2: '☁️ AWS EC2', aws_iam: '🔑 AWS IAM', aws_s3: '🪣 AWS S3', aws_glacier: '❄️ Glacier',
        db_prod: '🗄️ DB Prod', db_dev: '🗄️ DB Dev', elastic: '🔍 Elastic', data_lake: '🌊 Data Lake', redis: '⚡ Redis Cache', veeam: '💾 Veeam Backup', offline_backup: '📼 Offline Backup',
        slack: '💬 Slack', jira: '📋 Jira',
        historian: '📊 Data Historian', scada_master: '🏭 SCADA Master', scada_standby: '🏭 SCADA Standby', plc_assembly: '🔧 PLC Assembly', plc_cooling: '🔧 PLC Cooling', hmi_main: '💻 HMI Main', sis: '⚠️ SIS'
    };

    static NODE_TO_DISPLAY = {
        internet: 'Internet',
        dns_ext: 'DNS External', github: 'GitHub Cloud', o365: 'Office 365 Cloud', bank: 'Bank Portal API', supplier: 'Supplier Portal', dns_c2: 'Malicious C2 (DNS)',
        apigw: 'API Gateway', apigw2: 'API Gateway v2', apigw_legacy: 'Legacy API Gateway', vpn: 'VPN Gateway', waf: 'WAF', seg: 'Secure Email Gateway', rdp_jump: 'RDP Jump Host', web_front: 'Public Website', fw_it_ot: 'IT/OT Firewall', linux_jump: 'Linux Jump Host', ot_jump: 'OT Jump Host',
        ad_primary: 'Primary Active Directory', ad_sec: 'Secondary AD', file_svr: 'File Server', nas: 'NAS Storage', erp: 'ERP System', intranet: 'Intranet Portal', wsus: 'WSUS Server', siem: 'Splunk SIEM', edr: 'EDR Console', pam: 'PAM Solution', vcenter: 'vCenter Server', admin_portal: 'Admin Portal', email_svr: 'Email Server',
        laptop_dev_lead: 'Lead Dev Laptop', laptops_dev: 'Dev Laptops', laptop_pm: 'PM Laptop', laptops_ops: 'Ops Laptops', laptop_admin: 'Admin Laptop', laptop_ex: 'Ex-Employee Laptop', laptop_ceo: 'CEO Laptop', mobile_ceo: 'CEO Mobile Device', laptop_cfo: 'CFO Laptop', laptops_finance: 'Finance Laptops', laptops_hr: 'HR Laptops', laptops_sales: 'Sales Laptops', laptops_staff: 'Staff Laptops', laptops_eng: 'Engineering Laptops', ot_eng_ws: 'OT Engineering WorkStation',
        jenkins_master: 'Jenkins Master', jenkins_workers: 'Jenkins Workers', gitlab: 'GitLab CI', github_ent: 'GitHub Enterprise', npm: 'npm Registry', docker_hub: 'Docker Hub', docker_reg: 'Internal Docker Registry', vault: 'HashiCorp Vault', nexus: 'Nexus Repo', sonar: 'SonarQube',
        k8s_control: 'K8s Control Plane', k8s_workers: 'K8s Worker Nodes', aws_ec2: 'AWS EC2 Fleet', aws_iam: 'AWS IAM', aws_s3: 'AWS S3 Buckets', aws_glacier: 'AWS Glacier Backups',
        db_prod: 'Production DB', db_dev: 'Dev/Test DB', elastic: 'Elasticsearch Cluster', data_lake: 'Data Lake', redis: 'Redis Cache', veeam: 'Veeam Backup Server', offline_backup: 'Offline Backup Infrastructure',
        slack: 'Slack', jira: 'Jira',
        historian: 'Data Historian', scada_master: 'SCADA Master', scada_standby: 'SCADA Standby', plc_assembly: 'Assembly PLC', plc_cooling: 'Cooling PLC', hmi_main: 'Main HMI Panel', sis: 'Safety Instrumented System (SIS)'
    };

    static SCENARIO_NODES = {
        1: ['dns_ext', 'apigw', 'vpn', 'waf', 'ad_primary', 'slack', 'jira', 'file_svr', 'email_svr', 'laptop_dev_lead', 'laptops_dev', 'laptop_pm', 'jenkins_master', 'jenkins_workers', 'github_ent', 'vault', 'nexus', 'aws_ec2', 'aws_s3', 'aws_iam', 'db_prod'],
        2: ['apigw', 'waf', 'vpn', 'ad_primary', 'slack', 'jira', 'laptops_dev', 'laptops_ops', 'npm', 'docker_hub', 'gitlab', 'github_ent', 'vault', 'sonar', 'k8s_control', 'k8s_workers', 'aws_ec2', 'db_prod', 'redis'],
        3: ['vpn', 'apigw', 'ad_primary', 'ad_sec', 'file_svr', 'slack', 'jira', 'siem', 'laptop_ex', 'laptop_admin', 'laptops_staff', 'github_ent', 'vault', 'jenkins_master', 'aws_iam', 'aws_ec2', 'aws_s3', 'db_prod', 'db_dev', 'admin_portal'],
        4: ['dns_ext', 'apigw2', 'apigw_legacy', 'waf', 'vpn', 'ad_primary', 'slack', 'siem', 'pam', 'laptops_ops', 'laptops_dev', 'docker_reg', 'jenkins_master', 'vault', 'k8s_control', 'k8s_workers', 'db_prod', 'elastic', 'aws_ec2'],
        5: ['o365', 'bank', 'supplier', 'seg', 'vpn', 'ad_primary', 'file_svr', 'erp', 'intranet', 'slack', 'jira', 'laptop_ceo', 'laptop_cfo', 'laptops_finance', 'laptops_sales', 'laptops_hr', 'mobile_ceo', 'siem', 'pam'],
        6: ['o365', 'rdp_jump', 'apigw', 'vpn', 'ad_primary', 'ad_sec', 'vcenter', 'nas', 'db_prod', 'db_dev', 'erp', 'intranet', 'veeam', 'offline_backup', 'aws_glacier', 'laptops_hr', 'laptops_finance', 'laptops_dev', 'laptops_sales', 'siem', 'edr'],
        7: ['dns_c2', 'github', 'apigw', 'vpn', 'waf', 'linux_jump', 'jenkins_master', 'vault', 'nexus', 'k8s_control', 'k8s_workers', 'aws_ec2', 'aws_s3', 'aws_iam', 'db_prod', 'elastic', 'data_lake', 'laptop_dev_lead', 'slack', 'siem'],
        8: ['apigw', 'vpn', 'web_front', 'ad_primary', 'erp', 'laptops_eng', 'wsus', 'siem', 'fw_it_ot', 'ot_jump', 'historian', 'scada_master', 'scada_standby', 'ot_eng_ws', 'plc_assembly', 'plc_cooling', 'hmi_main', 'sis']
    };

    // ── vis-network Infrastructure Map ──────────────────────────
    initNetworkMap() {
        const container = document.getElementById('networkMap');
        if (!container) return;

        const baseColor = { background: '#0f2847', border: '#3a90e8' };
        const secColor  = { background: '#1a2744', border: '#5580aa' };
        const cloudColor = { background: '#0f2847', border: '#d97706' };
        const badColor = { background: '#3c0d0d', border: '#e83a3a' };
        const otColor = { background: '#2d1f0e', border: '#b45309' };

        const allNodesData = Object.keys(CyberBreachGame.NODE_MAP).map(id => {
            const label = CyberBreachGame.NODE_MAP[id];
            let shape = 'box'; let color = secColor; let size = 18; let bw = 1; let fc = '#aaa';

            if (id === 'internet') { shape = 'diamond'; color = secColor; fc = '#fff'; bw = 2; size = 30; }
            else if (id.includes('apigw') || id === 'vpn' || id === 'ad_primary' || id === 'jenkins_master' || id === 'gitlab' || id === 'github_ent') { shape = 'box'; color = baseColor; fc = '#c5dcf5'; bw = 2; size = 20; }
            else if (id.includes('db') || id.includes('aws_s3') || id === 'nas' || id === 'veeam' || id === 'offline_backup') { shape = 'database'; color = baseColor; fc = '#c5dcf5'; bw = 2; size = 20; if(id==='aws_s3') color=cloudColor; }
            else if (id.includes('aws') || id === 'vault' || id.includes('k8s')) { shape = 'box'; color = cloudColor; fc = '#fcd1a6'; bw = 2; size = 20; }
            else if (id === 'dns_c2') { shape = 'diamond'; color = badColor; fc = '#fff'; bw = 2; size = 30; }
            else if (id.includes('scada') || id.includes('plc') || id === 'sis' || id === 'fw_it_ot') { shape = 'box'; color = otColor; fc = '#fcd1a6'; bw = 2; size = 20; if(id==='sis') color={ background: '#2d1f0e', border: '#dc2626' }; }
            else if (id.includes('laptop') || id === 'mobile_ceo') { shape = 'box'; color = secColor; fc = '#aaa'; bw = 1; size = 18; }
            else if (id === 'siem' || id === 'pam' || id === 'edr') { shape = 'hexagon'; color = baseColor; fc = '#c5dcf5'; bw = 2; size = 20; }

            return { id, label, shape, color, font: { color: fc, size: 11 }, borderWidth: bw, size };
        });

        let filteredNodes = allNodesData;
        if (this.scenario && CyberBreachGame.SCENARIO_NODES[this.scenario]) {
            const allowed = CyberBreachGame.SCENARIO_NODES[this.scenario];
            filteredNodes = allNodesData.filter(n => allowed.includes(n.id) || n.id === 'internet');
        }
        const nodes = new vis.DataSet(filteredNodes);

        const buildEdge = (from, to, dashes=false, color='#3a90e8') => ({ from, to, arrows: 'to', dashes, color: { color, opacity: 0.4 } });
        
        const allEdgesData = [
            // External
            buildEdge('internet', 'dns_ext'), buildEdge('internet', 'github', false, '#555'), buildEdge('internet', 'o365', false, '#555'),
            buildEdge('internet', 'bank', true, '#555'), buildEdge('internet', 'supplier', true, '#555'),
            buildEdge('internet', 'waf'), buildEdge('internet', 'seg'), buildEdge('internet', 'web_front'),
            buildEdge('internet', 'apigw'), buildEdge('internet', 'apigw2'), buildEdge('internet', 'apigw_legacy'), buildEdge('internet', 'vpn'), buildEdge('internet', 'rdp_jump'),
            buildEdge('dns_c2', 'internet', true, '#e83a3a'),
            // DMZ
            buildEdge('waf', 'apigw'), buildEdge('waf', 'apigw2'), buildEdge('waf', 'apigw_legacy'),
            buildEdge('vpn', 'linux_jump'), buildEdge('vpn', 'ot_jump'), buildEdge('vpn', 'ad_primary'), buildEdge('rdp_jump', 'ad_primary'),
            buildEdge('seg', 'email_svr'), buildEdge('apigw', 'ad_primary'),
            // IT & Endpoints
            buildEdge('ad_primary', 'ad_sec', true), buildEdge('ad_primary', 'wsus'), buildEdge('ad_primary', 'siem'), buildEdge('ad_primary', 'edr'), buildEdge('ad_primary', 'pam'),
            buildEdge('ad_primary', 'file_svr'), buildEdge('ad_primary', 'nas'), buildEdge('ad_primary', 'erp'), buildEdge('ad_primary', 'intranet'), buildEdge('ad_primary', 'vcenter'),
            buildEdge('ad_primary', 'laptop_dev_lead'), buildEdge('ad_primary', 'laptops_dev'), buildEdge('ad_primary', 'laptop_pm'), buildEdge('ad_primary', 'laptops_ops'),
            buildEdge('ad_primary', 'laptop_admin'), buildEdge('ad_primary', 'laptop_ceo'), buildEdge('ad_primary', 'laptop_cfo'), buildEdge('ad_primary', 'laptops_staff'),
            buildEdge('ad_primary', 'laptops_hr'), buildEdge('ad_primary', 'laptops_finance'), buildEdge('ad_primary', 'laptops_eng'), buildEdge('ad_primary', 'laptops_sales'),
            buildEdge('laptop_ex', 'vpn', true), buildEdge('mobile_ceo', 'o365', true), buildEdge('laptop_ceo', 'mobile_ceo', true),
            buildEdge('file_svr', 'nas'), buildEdge('slack', 'jira', true, '#555'), buildEdge('ad_primary', 'slack', true, '#555'),
            // DevOps
            buildEdge('laptop_dev_lead', 'jenkins_master'), buildEdge('laptops_dev', 'github_ent'), buildEdge('laptops_dev', 'gitlab'),
            buildEdge('jenkins_master', 'jenkins_workers'), buildEdge('jenkins_master', 'vault'), buildEdge('jenkins_master', 'nexus'), buildEdge('jenkins_master', 'sonar'),
            buildEdge('gitlab', 'docker_reg'), buildEdge('github_ent', 'jenkins_master'), buildEdge('jenkins_master', 'k8s_control'),
            buildEdge('npm', 'nexus', true), buildEdge('docker_hub', 'docker_reg', true),
            // Cloud & Data
            buildEdge('k8s_control', 'k8s_workers'), buildEdge('k8s_workers', 'aws_ec2'), buildEdge('k8s_workers', 'db_prod'), buildEdge('k8s_workers', 'redis'),
            buildEdge('aws_iam', 'aws_ec2', false, '#d97706'), buildEdge('aws_ec2', 'aws_s3', false, '#d97706'), buildEdge('aws_iam', 'aws_s3', false, '#d97706'),
            buildEdge('db_prod', 'elastic'), buildEdge('db_prod', 'data_lake', true), buildEdge('db_dev', 'db_prod', true),
            // Backup
            buildEdge('veeam', 'db_prod'), buildEdge('veeam', 'file_svr'), buildEdge('veeam', 'nas'), buildEdge('veeam', 'vcenter'),
            buildEdge('veeam', 'aws_glacier', true), buildEdge('veeam', 'offline_backup'),
            // OT
            buildEdge('fw_it_ot', 'scada_master', false, '#d97706'), buildEdge('ot_jump', 'scada_master', false, '#d97706'), buildEdge('scada_master', 'scada_standby', true, '#d97706'),
            buildEdge('scada_master', 'historian', false, '#d97706'), buildEdge('scada_master', 'plc_assembly', false, '#d97706'), buildEdge('scada_master', 'plc_cooling', false, '#d97706'),
            buildEdge('plc_assembly', 'hmi_main', true, '#d97706'), buildEdge('plc_cooling', 'hmi_main', true, '#d97706'),
            buildEdge('plc_assembly', 'sis', true, '#dc2626'), buildEdge('plc_cooling', 'sis', true, '#dc2626'),
            // Threat
            buildEdge('dns_c2', 'k8s_workers', true, '#e83a3a'), buildEdge('dns_c2', 'aws_ec2', true, '#e83a3a')
        ];

        const nodeIds = new Set(filteredNodes.map(n => n.id));
        const filteredEdges = allEdgesData.filter(e => nodeIds.has(e.from) && nodeIds.has(e.to));
        const edges = new vis.DataSet(filteredEdges);

        this.networkNodes = nodes;

        const options = {
            nodes: { shape: 'box', margin: 10, shadow: { enabled: true, color: 'rgba(0,0,0,.3)', size: 8 } },
            edges: { smooth: { type: 'cubicBezier', forceDirection: 'vertical' } },
            layout: { hierarchical: { direction: 'UD', sortMethod: 'directed', levelSeparation: 130, nodeSpacing: 85 } },
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
                    forceAtlas2Based: { gravitationalConstant: -180, centralGravity: 0.04, springLength: 120, springConstant: 0.08 },
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
