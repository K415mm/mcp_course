class CsNetworkMap {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();
        this.initGraph();
    }

    initGraph() {
        const options = {
            nodes: {
                shape: 'dot',
                size: 25,
                font: { size: 14, color: '#ffffff', face: 'Space Mono' },
                borderWidth: 2,
                shadow: { enabled: true, color: 'rgba(0,0,0,0.5)', size: 10, x: 0, y: 0 }
            },
            edges: {
                width: 2,
                shadow: { enabled: true, color: 'rgba(0,0,0,0.3)', size: 5, x: 0, y: 0 },
                smooth: { type: 'continuous' }
            },
            physics: {
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -60,
                    centralGravity: 0.01,
                    springConstant: 0.08,
                    springLength: 120,
                    damping: 0.4
                }
            },
            interaction: { hover: true, dragNodes: true, zoomView: true, dragView: true }
        };

        this.network = new vis.Network(this.container, { nodes: this.nodes, edges: this.edges }, options);
        
        this.network.on('click', (params) => {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = this.nodes.get(nodeId);
                if (window.swalAlert && node.description) {
                    window.swalAlert(node.description, 'info');
                }
            }
        });
    }

    setPhase(phaseIndex) {
        if (!this.network) return;

        const baseNodes = [
            { id: 'ancs', label: 'ANCS\n(Commandement)', color: { background: '#00b4d8', border: '#0077a8' }, description: 'Agence Nationale de Cybersécurité - Rôle de Coordination' },
            { id: 'cert', label: 'CERT-TN\n(Détection)', color: { background: '#2dc653', border: '#1e8f3a' }, description: 'Équipe de réponse aux urgences informatiques' },
            { id: 'finance', label: 'Finance\n(Secteur)', color: { background: '#f59e0b', border: '#b45309' }, description: 'Secteur Bancaire et Systèmes de Paiements' },
            { id: 'transport', label: 'Transport\n(Secteur)', color: { background: '#f59e0b', border: '#b45309' }, description: 'Infrastructures de Transport Nationales' },
            { id: 'egov', label: 'E-Gov\n(Secteur)', color: { background: '#f59e0b', border: '#b45309' }, description: 'Services Publics et e-Gouvernement' },
            { id: 'comm', label: 'Communication\n(Secteur)', color: { background: '#f59e0b', border: '#b45309' }, description: 'Médias et Communication de Crise' },
            { id: 'phantom', label: 'PHANTOM GRID\n(Menace)', color: { background: '#ef4444', border: '#b91c1c' }, shape: 'diamond', size: 35, description: 'Groupe Cybercriminel Avancé' }
        ];

        const baseEdges = [
            { id: 'e1', from: 'ancs', to: 'cert', dashes: true, color: { color: '#00b4d8' } },
            { id: 'e2', from: 'cert', to: 'finance', color: { color: '#2dc653' } },
            { id: 'e3', from: 'cert', to: 'transport', color: { color: '#2dc653' } },
            { id: 'e4', from: 'cert', to: 'egov', color: { color: '#2dc653' } },
            { id: 'e5', from: 'ancs', to: 'comm', color: { color: '#00b4d8' } }
        ];

        let phaseNodes = JSON.parse(JSON.stringify(baseNodes));
        let phaseEdges = JSON.parse(JSON.stringify(baseEdges));

        switch(phaseIndex) {
            case 0: // Phase 1: Reveil
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#ef4444', border: '#b91c1c' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#ef4444', border: '#b91c1c' } });
                break;
            case 1: // Phase 2: Escalade
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                phaseEdges.push({ id: 'a3', from: 'phantom', to: 'egov', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#ef4444', border: '#b91c1c' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#ef4444', border: '#b91c1c' } });
                this.updateNode(phaseNodes, 'egov', { color: { background: '#ef4444', border: '#b91c1c' } });
                break;
            case 2: // Phase 3: Media
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a3', from: 'phantom', to: 'egov', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a4', from: 'phantom', to: 'comm', color: { color: '#ef4444', dashes: true }, arrows: 'to', width: 4 });
                this.updateNode(phaseNodes, 'comm', { color: { background: '#ef4444', border: '#b91c1c' } });
                break;
            case 3: // Phase 4: Arbitrage
                phaseNodes.push({ id: 'intl', label: 'Coalition INT.', color: { background: '#8b5cf6', border: '#6d28d9' }, description: 'Alliés Internationaux / INTERPOL' });
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to' });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to' });
                phaseEdges.push({ id: 'c1', from: 'intl', to: 'ancs', color: { color: '#8b5cf6' }, arrows: 'to', width: 3 });
                phaseEdges.push({ id: 'c2', from: 'intl', to: 'cert', color: { color: '#8b5cf6' }, arrows: 'to', width: 3 });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#f59e0b', border: '#b45309' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#f59e0b', border: '#b45309' } });
                this.updateNode(phaseNodes, 'egov', { color: { background: '#2dc653', border: '#1e8f3a' } });
                this.updateNode(phaseNodes, 'comm', { color: { background: '#2dc653', border: '#1e8f3a' } });
                break;
            case 4: // Phase 5: Debrief
                phaseNodes = phaseNodes.filter(n => n.id !== 'phantom');
                phaseNodes.forEach(n => {
                    if(n.id !== 'ancs' && n.id !== 'cert') n.color = { background: '#2dc653', border: '#1e8f3a' };
                });
                break;
        }

        this.nodes.clear();
        this.edges.clear();
        this.nodes.add(phaseNodes);
        this.edges.add(phaseEdges);
        
        // Trigger animate.css animation on container
        this.container.classList.remove('animate__animated', 'animate__fadeIn');
        void this.container.offsetWidth; // trigger reflow
        this.container.classList.add('animate__animated', 'animate__fadeIn');
    }

    updateNode(nodes, id, props) {
        const n = nodes.find(n => n.id === id);
        if (n) Object.assign(n, props);
    }
}
window.CsNetworkMap = CsNetworkMap;