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
                shape: 'circularImage',
                size: 28,
                font: { size: 14, color: '#ffffff', face: 'Space Mono', bold: true, background: 'rgba(0,0,0,0.6)' },
                borderWidth: 3,
                shadow: { enabled: true, color: 'rgba(0,0,0,0.8)', size: 12, x: 0, y: 0 }
            },
            edges: {
                width: 2,
                shadow: { enabled: true, color: 'rgba(0,0,0,0.5)', size: 5, x: 0, y: 0 },
                smooth: { type: 'continuous' }
            },
            physics: {
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -70,
                    centralGravity: 0.015,
                    springConstant: 0.08,
                    springLength: 140,
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

        // SVG Icons encoded as Data URLs
        const icons = {
            ancs: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>',
            cert: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>',
            finance: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M4 10h3v7H4zm6.5 0h3v7h-3zM2 19h20v3H2zm15-9h3v7h-3zm-5-9L2 6v2h20V6z"/></svg>',
            transport: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M12 2c-4 0-8 .5-8 4v9.5C4 17.43 5.57 19 7.5 19L6 20.5v.5h2.23l2-2H14l2 2h2.23v-.5L16.5 19c1.93 0 3.5-1.57 3.5-3.5V6c0-3.5-3.58-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm3.5-7H6V6h5v4zm2 0V6h5v4h-5zm3.5 7c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
            egov: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
            comm: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1h16v-1c0-2.66-5.33-4-8-4z"/></svg>',
            phantom: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ef4444"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.5 6c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm3 8h-3v-2h3v2zm1-4h-5V9.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v2.5z"/></svg>',
            intl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23ffffff"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.09 13.36 4 12.69 4 12s.09-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2s.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2s.07-1.34.16-2h4.68c.09.66.16 1.32.16 2s-.07 1.34-.16 2zm1.63 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zm1.38-7.56c.08-.66.14-1.32.14-2s-.06-1.34-.14-2h3.38c.17.64.26 1.31.26 2s-.09 1.36-.26 2h-3.38z"/></svg>'
        };

        const baseNodes = [
            { id: 'ancs', label: 'ANCS', image: icons.ancs, color: { background: '#00b4d8', border: '#0077a8' }, description: 'Agence Nationale de Cybersécurité - Rôle de Coordination' },
            { id: 'cert', label: 'CERT-TN', image: icons.cert, color: { background: '#2dc653', border: '#1e8f3a' }, description: 'Équipe de réponse aux urgences informatiques' },
            { id: 'finance', label: 'Finance', image: icons.finance, color: { background: '#0f2847', border: '#3a90e8' }, description: 'Secteur Bancaire et Systèmes de Paiements' },
            { id: 'transport', label: 'Transport', image: icons.transport, color: { background: '#0f2847', border: '#3a90e8' }, description: 'Infrastructures de Transport Nationales' },
            { id: 'egov', label: 'E-Gov', image: icons.egov, color: { background: '#0f2847', border: '#3a90e8' }, description: 'Services Publics et e-Gouvernement' },
            { id: 'comm', label: 'Comms', image: icons.comm, color: { background: '#0f2847', border: '#3a90e8' }, description: 'Médias et Communication de Crise' },
            { id: 'phantom', label: 'PHANTOM', image: icons.phantom, color: { background: '#3b0a0a', border: '#ef4444' }, size: 35, description: 'Groupe Cybercriminel Avancé' }
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
                this.updateNode(phaseNodes, 'finance', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#9b0e20', border: '#ef4444' } });
                break;
            case 1: // Phase 2: Escalade
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                phaseEdges.push({ id: 'a3', from: 'phantom', to: 'egov', color: { color: '#ef4444' }, arrows: 'to', width: 4 });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'egov', { color: { background: '#9b0e20', border: '#ef4444' } });
                break;
            case 2: // Phase 3: Media
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a3', from: 'phantom', to: 'egov', color: { color: '#ef4444' }, arrows: 'to', width: 2 });
                phaseEdges.push({ id: 'a4', from: 'phantom', to: 'comm', color: { color: '#ef4444', dashes: true }, arrows: 'to', width: 4 });
                this.updateNode(phaseNodes, 'comm', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#9b0e20', border: '#ef4444' } });
                this.updateNode(phaseNodes, 'egov', { color: { background: '#9b0e20', border: '#ef4444' } });
                break;
            case 3: // Phase 4: Arbitrage
                phaseNodes.push({ id: 'intl', label: 'INTL', image: icons.intl, shape: 'circularImage', color: { background: '#8b5cf6', border: '#6d28d9' }, description: 'Alliés Internationaux / INTERPOL' });
                phaseEdges.push({ id: 'a1', from: 'phantom', to: 'finance', color: { color: '#ef4444' }, arrows: 'to' });
                phaseEdges.push({ id: 'a2', from: 'phantom', to: 'transport', color: { color: '#ef4444' }, arrows: 'to' });
                phaseEdges.push({ id: 'c1', from: 'intl', to: 'ancs', color: { color: '#8b5cf6' }, arrows: 'to', width: 3 });
                phaseEdges.push({ id: 'c2', from: 'intl', to: 'cert', color: { color: '#8b5cf6' }, arrows: 'to', width: 3 });
                this.updateNode(phaseNodes, 'finance', { color: { background: '#b45309', border: '#f59e0b' } });
                this.updateNode(phaseNodes, 'transport', { color: { background: '#b45309', border: '#f59e0b' } });
                this.updateNode(phaseNodes, 'egov', { color: { background: '#1e8f3a', border: '#2dc653' } });
                this.updateNode(phaseNodes, 'comm', { color: { background: '#1e8f3a', border: '#2dc653' } });
                break;
            case 4: // Phase 5: Debrief
                phaseNodes = phaseNodes.filter(n => n.id !== 'phantom');
                phaseNodes.forEach(n => {
                    if(n.id !== 'ancs' && n.id !== 'cert') n.color = { background: '#1e8f3a', border: '#2dc653' };
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