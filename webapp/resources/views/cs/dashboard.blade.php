<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $scenario['title'] ?? 'CARTHAGE SHIELD' }} — Grand Écran</title>

{{-- HUD theme assets (same as app layout) --}}
<link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
<link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ── Override HUD body for full-screen dashboard ──────────────── */
html, body { height: 100%; overflow: hidden; margin: 0; padding: 0; }
body { background: #03070d !important; font-family: 'Barlow Condensed', var(--bs-font-sans-serif) !important; }

/* Grid background */
body::before {
    content: '';
    position: fixed; inset: 0;
    background-image:
        linear-gradient(rgba(0,180,216,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,180,216,.03) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none; z-index: 0;
}

/* Atmosphere transitions */
body { transition: background 2s ease; }
body.atmo-tension  { background: #070800 !important; }
body.atmo-crisis   { background: #0d0303 !important; }
body.atmo-hacked   { background: #000    !important; }
body.atmo-victory  { background: #030c05 !important; }
body.scanlines::after {
    content: '';
    position: fixed; inset: 0;
    background: repeating-linear-gradient(0deg, rgba(0,0,0,.12) 0px, rgba(0,0,0,.12) 1px, transparent 1px, transparent 4px);
    pointer-events: none; z-index: 999;
}

/* ── Layout ─────────────────────────────────────────────────────── */
.cs-layout {
    display: grid;
    grid-template-rows: 76px 1fr 190px;
    height: 100vh;
    padding: 12px;
    gap: 10px;
    position: relative; z-index: 1;
}

/* ── Header bar ─────────────────────────────────────────────────── */
.cs-header {
    display: flex; align-items: center; gap: 16px;
    background: rgba(13,27,46,.9);
    border: 1px solid rgba(0,180,216,.2);
    border-top: 3px solid var(--bs-theme);
    border-radius: 10px;
    padding: 0 20px;
    backdrop-filter: blur(8px);
}
.cs-header .card-corner { display: none; }

.logo-txt {
    font-family: 'Space Mono', monospace;
    font-weight: 700; font-size: 1.5rem; letter-spacing: 3px;
}
.logo-txt span { color: var(--bs-theme); }
.scenario-sub { font-size: .8rem; color: rgba(255,255,255,.45); margin-top: 1px; }

.phase-bar { display: flex; align-items: center; gap: 7px; margin-left: 20px; }
.ph-seg {
    width: 30px; height: 5px; border-radius: 3px;
    background: rgba(255,255,255,.08); transition: all .5s;
}
.ph-seg.done  { background: var(--bs-theme); opacity: .7; }
.ph-seg.active {
    background: var(--bs-theme);
    animation: segPulse 2s infinite;
    box-shadow: 0 0 8px var(--bs-theme);
}
@keyframes segPulse { 0%,100% { opacity:1 } 50% { opacity:.5 } }
.phase-label {
    font-family: 'Space Mono', monospace;
    font-size: .75rem; color: var(--bs-theme);
    letter-spacing: 1px; margin-left: 4px;
}

.timer-big {
    font-family: 'Space Mono', monospace;
    font-size: 3.2rem; font-weight: 700;
    color: var(--bs-theme);
    text-shadow: 0 0 30px rgba(var(--bs-theme-rgb), .5);
    line-height: 1; transition: color .5s, text-shadow .5s;
}
.timer-big.warn   { color: #f59e0b; text-shadow: 0 0 30px rgba(245,158,11,.5); }
.timer-big.danger { color: #ef4444; text-shadow: 0 0 30px rgba(239,68,68,.6); animation: tPulse .5s infinite; }
@keyframes tPulse { 0%,100% { opacity:1 } 50% { opacity:.3 } }

.status-badge {
    font-family: 'Space Mono', monospace;
    font-size: .65rem; padding: 4px 12px;
    border-radius: 99px; letter-spacing: 2px;
    border: 1px solid rgba(122,156,192,.3);
    color: rgba(255,255,255,.5);
}
.status-badge.running {
    border-color: #2dc653; color: #2dc653;
    background: rgba(45,198,83,.08);
}
.clock-sm { font-family: 'Space Mono', monospace; font-size: .85rem; color: rgba(255,255,255,.35); }

/* ── Teams grid ─────────────────────────────────────────────────── */
.teams-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px; align-items: start;
}
.team-card {
    background: rgba(13,27,46,.8);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    padding: 14px 10px;
    text-align: center; position: relative; overflow: hidden;
    transition: all .5s;
}
.team-card::after {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: var(--tc);
}
.card-arrow { display: none; } /* HUD card-arrow not needed here */

.t-icon { font-size: 2.2rem; display: block; margin-bottom: 4px; }
.t-name { font-size: 1.1rem; font-weight: 900; letter-spacing: 1px; }
.t-role { font-size: .7rem; color: rgba(255,255,255,.4); margin: 2px 0 8px; }
.t-score {
    font-family: 'Space Mono', monospace;
    font-size: 3.2rem; font-weight: 700;
    color: var(--tc); line-height: 1;
    transition: all .4s;
}
.t-delta {
    display: none; position: absolute;
    top: 50%; right: 10px; transform: translateY(-50%);
    font-family: 'Space Mono', monospace; font-size: 1.3rem; font-weight: 700;
    animation: deltaUp .8s ease forwards;
}
@keyframes deltaUp {
    0%   { opacity: 1; transform: translateY(-50%); }
    100% { opacity: 0; transform: translateY(-110%); }
}
.t-badge { margin-top: 6px; font-size: 1.2rem; }
.t-badge-nm { font-family: 'Space Mono', monospace; font-size: .6rem; color: rgba(255,255,255,.3); margin-top: 2px; }
.t-online {
    font-family: 'Space Mono', monospace; font-size: .65rem;
    color: rgba(255,255,255,.35); margin-top: 4px;
    display: flex; align-items: center; gap: 4px; justify-content: center;
}
.dot-on { width: 6px; height: 6px; border-radius: 50%; background: #2dc653; display: inline-block; }

/* ── Bottom widgets ─────────────────────────────────────────────── */
.widgets-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

.widget {
    background: rgba(13,27,46,.8);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 10px; padding: 12px 14px; overflow: hidden;
}
.widget-hdr {
    font-family: 'Space Mono', monospace;
    font-size: .65rem; letter-spacing: 3px; color: rgba(255,255,255,.4);
    text-transform: uppercase; margin-bottom: 8px;
    padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex; align-items: center; gap: 6px;
}
.widget-hdr i { color: var(--bs-theme); font-size: .8rem; }

.feed-list { display: flex; flex-direction: column; gap: 4px; max-height: 126px; overflow-y: auto; }
.feed-item {
    padding: 6px 9px; border-radius: 5px;
    border-left: 3px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.03);
    font-size: .72rem; line-height: 1.4;
}
.feed-item.info    { border-color: var(--bs-theme); }
.feed-item.warn    { border-color: #f59e0b; }
.feed-item.alert   { border-color: #ef4444; background: rgba(239,68,68,.06); }
.feed-item.success { border-color: #2dc653; }
.fi-ts { font-family: 'Space Mono', monospace; font-size: .6rem; color: rgba(255,255,255,.3); margin-bottom: 2px; }

/* Vote bars */
.vote-bars { display: flex; flex-direction: column; gap: 7px; }
.vote-bar-row { display: flex; align-items: center; gap: 8px; }
.vb-lbl { font-family: 'Space Mono', monospace; font-size: 1.2rem; font-weight: 700; width: 28px; }
.vb-track {
    flex: 1; height: 18px;
    background: rgba(255,255,255,.07); border-radius: 4px; overflow: hidden;
}
.vb-fill {
    height: 100%; border-radius: 4px;
    transition: width .5s ease;
    background: var(--bs-theme);
}
.vb-count { font-family: 'Space Mono', monospace; font-size: .75rem; width: 18px; text-align: right; }
.vote-q { font-family: 'Space Mono', monospace; font-size: .65rem; color: rgba(255,255,255,.4); margin-bottom: 8px; }

/* ── PHANTOM overlay ─────────────────────────────────────────────── */
.phantom-ov {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.95); z-index: 900;
    align-items: center; justify-content: center; flex-direction: column;
    text-align: center; padding: 24px;
}
.phantom-ov.show { display: flex; animation: phIn .4s ease; }
@keyframes phIn { from { opacity: 0 } to { opacity: 1 } }
.ph-label { font-family: 'Space Mono', monospace; font-size: .7rem; letter-spacing: 6px; color: rgba(239,68,68,.5); margin-bottom: 12px; }
.ph-skull { font-size: 7rem; animation: bob 2s infinite; }
@keyframes bob { 0%,100% { transform: scale(1) } 50% { transform: scale(1.06) } }
.ph-msg {
    font-family: 'Space Mono', monospace;
    font-size: 1.6rem; color: #ef4444; max-width: 680px;
    line-height: 1.5; text-shadow: 0 0 30px rgba(239,68,68,.5);
    position: relative;
}
.ph-msg::before, .ph-msg::after {
    content: attr(data-txt); position: absolute; top: 0; left: 0; right: 0;
}
.ph-msg::before { color: var(--bs-theme); clip-path: polygon(0 0,100% 0,100% 33%,0 33%); transform: translate(-3px,0); animation: gA .8s infinite; }
.ph-msg::after  { color: #ef4444; clip-path: polygon(0 67%,100% 67%,100% 100%,0 100%); transform: translate(3px,0); animation: gB .8s infinite; }
@keyframes gA { 0%,100% { transform: translate(-3px,0) } 50% { transform: translate(3px,-1px) } }
@keyframes gB { 0%,100% { transform: translate(3px,0) } 50% { transform: translate(-3px,1px) } }
.ph-dismiss { font-family: 'Space Mono', monospace; font-size: .65rem; color: rgba(239,68,68,.35); letter-spacing: 4px; margin-top: 22px; animation: blink 2s infinite; }
@keyframes blink { 0%,100% { opacity:.3 } 50% { opacity:1 } }

/* ── ENDGAME overlay ─────────────────────────────────────────────── */
.endgame-ov {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.96); z-index: 950;
    align-items: center; justify-content: center; flex-direction: column;
}
.endgame-ov.show { display: flex; animation: endIn .8s ease; }
@keyframes endIn { from { opacity: 0; transform: scale(.96) } to { opacity: 1; transform: scale(1) } }
.eg-label { font-family: 'Space Mono', monospace; font-size: .65rem; letter-spacing: 8px; color: #f59e0b; margin-bottom: 6px; }
.eg-title { font-size: 2rem; font-weight: 900; margin-bottom: 28px; }

.podium { display: flex; align-items: flex-end; gap: 12px; justify-content: center; }
.podium-slot { text-align: center; width: 140px; }
.pod-bar {
    border-radius: 8px 8px 0 0;
    display: flex; align-items: center; justify-content: center; flex-direction: column;
    padding: 14px 8px;
}
.p1 .pod-bar { border: 1px solid #f59e0b; background: linear-gradient(180deg,rgba(245,158,11,.25),rgba(245,158,11,.06)); height: 200px; }
.p2 .pod-bar { border: 1px solid var(--bs-theme); background: linear-gradient(180deg,rgba(var(--bs-theme-rgb),.2),rgba(var(--bs-theme-rgb),.04)); height: 152px; }
.p3 .pod-bar { border: 1px solid #8b5cf6; background: linear-gradient(180deg,rgba(139,92,246,.2),rgba(139,92,246,.04)); height: 118px; }
.pod-icon { font-size: 2rem; }
.pod-name { font-size: 1.1rem; font-weight: 900; margin-top: 4px; }
.pod-score { font-family: 'Space Mono', monospace; font-size: 1.8rem; color: var(--bs-theme); font-weight: 700; }
.pod-badge { font-size: 1.6rem; margin-top: 4px; }
.pod-base {
    background: rgba(255,255,255,.05); padding: 5px 8px;
    border-radius: 0 0 6px 6px;
    font-family: 'Space Mono', monospace; font-size: .7rem; color: rgba(255,255,255,.4);
}
.others-row { display: flex; gap: 14px; margin-top: 20px; justify-content: center; }
.other-tile {
    text-align: center; padding: 8px 16px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 8px;
}
.ot-rank { font-family: 'Space Mono', monospace; font-size: .65rem; color: rgba(255,255,255,.35); }
.ot-name { font-size: .9rem; font-weight: 700; }
.ot-score { font-family: 'Space Mono', monospace; font-size: 1rem; color: var(--bs-theme); }

#confettiCanvas { position: fixed; inset: 0; pointer-events: none; z-index: 960; }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); }
</style>
</head>
<body>

{{-- PHANTOM overlay --}}
<div class="phantom-ov" id="phantomOv" onclick="dismissPhantom()">
    <div class="ph-label">MESSAGE INTERCEPTÉ — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="ph-skull">{{ $scenario['attacker_icon'] ?? '☠️' }}</div>
    <div class="ph-msg" id="phMsg" data-txt=""></div>
    <div class="ph-dismiss">CLIQUER POUR FERMER</div>
</div>

{{-- ENDGAME overlay --}}
<div class="endgame-ov" id="endgameOv">
    <canvas id="confettiCanvas"></canvas>
    <div class="eg-label">FIN DE L'EXERCICE — {{ $scenario['attacker_name'] ?? 'PHANTOM GRID' }}</div>
    <div class="eg-title">{{ $scenario['title'] ?? 'CARTHAGE SHIELD' }}</div>
    <div class="podium" id="podiumEl"></div>
    <div class="others-row" id="othersEl"></div>
</div>

{{-- MAIN LAYOUT --}}
<div class="cs-layout">

    {{-- HEADER --}}
    <div class="cs-header">
        <div>
            <div class="logo-txt">CARTHAGE <span>SHIELD</span></div>
            <div class="scenario-sub">{{ $scenario['title'] ?? '' }} &mdash; {{ $session->name }}</div>
        </div>
        <div class="phase-bar">
            @foreach($scenario['phases'] as $p)
            <div class="ph-seg" id="ph-seg-{{ $p['index'] }}"></div>
            @endforeach
            <span class="phase-label" id="phaseLabel">{{ $scenario['phases'][0]['name'] ?? '—' }}</span>
        </div>
        <div class="flex-grow-1"></div>
        <div class="d-flex flex-column align-items-end gap-1 me-3">
            <div class="status-badge" id="statusBadge">EN ATTENTE</div>
            <div class="clock-sm" id="clockSm">--:--:--</div>
        </div>
        <div class="timer-big" id="mainTimer">--:--</div>
    </div>

    {{-- TEAMS GRID --}}
    <div class="teams-grid" id="teamsGrid"></div>

    {{-- BOTTOM WIDGETS --}}
    <div class="widgets-row">
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-activity"></i>Activité Temps Réel</div>
            <div class="feed-list" id="feedList"></div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-hand-thumbs-up"></i>Vote Stratégique</div>
            <div id="voteWidget">
                <div class="vote-q fst-italic">Aucun vote en cours</div>
            </div>
        </div>
        <div class="widget">
            <div class="widget-hdr"><i class="bi bi-lightning-charge"></i>Injections Actives</div>
            <div class="feed-list" id="injectLog"></div>
        </div>
    </div>

</div>

{{-- HUD JS (theme, Bootstrap) --}}
<script src="{{ asset('hud/js/vendor.min.js') }}"></script>
<script src="{{ asset('hud/js/app.min.js') }}"></script>

<script>
const SESSION_CODE = '{{ $session->code }}';
const TOTAL_PHASES = {{ count($scenario['phases']) }};

let lastBcId = 0, lastInjectId = 0, lastAtmo = '', endgameFired = false;
let prevScores = {};

// ── Clock ─────────────────────────────────────────────────────────
setInterval(() => {
    const n = new Date();
    document.getElementById('clockSm').textContent =
        [n.getHours(), n.getMinutes(), n.getSeconds()].map(x => String(x).padStart(2,'0')).join(':');
}, 1000);

// ── Poll ──────────────────────────────────────────────────────────
async function poll() {
    try {
        const d = await fetch(`/cs/${SESSION_CODE}/api/state`).then(r => r.json());
        updateTimer(d.timer, d.session);
        updatePhase(d.session);
        updateAtmo(d.session.atmosphere);
        updateTeams(d.teams);
        handleActivity(d.broadcasts, d.injects);
        handleVote(d.vote);
        if (d.session.status === 'finished' && !endgameFired) fireEndgame(d.teams, d.session);
    } catch(e) {}
}
setInterval(poll, 1000); poll();

// ── Timer ─────────────────────────────────────────────────────────
function updateTimer(timer, session) {
    let secs;
    if (timer.isRunning && timer.endsAt)
        secs = Math.max(0, Math.round((new Date(timer.endsAt) - Date.now()) / 1000));
    else
        secs = timer.remainingSeconds ?? 0;

    const el = document.getElementById('mainTimer');
    const sb = document.getElementById('statusBadge');
    el.textContent = fmt(secs);
    el.className = 'timer-big' + (secs <= 60 ? ' danger' : secs <= 180 ? ' warn' : '');

    if (timer.isRunning) {
        sb.textContent = 'EN COURS'; sb.className = 'status-badge running';
    } else {
        sb.textContent = session.status === 'finished' ? 'TERMINÉ' : 'EN ATTENTE';
        sb.className = 'status-badge';
    }
}

// ── Phase ─────────────────────────────────────────────────────────
function updatePhase(session) {
    const idx = session.currentPhaseIndex;
    document.getElementById('phaseLabel').textContent = session.currentPhase?.name ?? '—';
    for (let i = 0; i < TOTAL_PHASES; i++) {
        const el = document.getElementById('ph-seg-' + i);
        if (!el) continue;
        el.className = 'ph-seg' + (i < idx ? ' done' : i === idx ? ' active' : '');
    }
}

// ── Atmosphere ────────────────────────────────────────────────────
function updateAtmo(mode) {
    if (mode === lastAtmo) return;
    lastAtmo = mode;
    document.body.className = '';
    if (mode && mode !== 'calm' && mode !== 'neutral') document.body.classList.add('atmo-' + mode);
    if (mode === 'crisis' || mode === 'hacked') document.body.classList.add('scanlines');
    addFeed(mode === 'crisis' ? 'alert' : mode === 'victory' ? 'success' : 'info',
        `ATMOSPHÈRE → ${mode.toUpperCase()}`);
}

// ── Teams ─────────────────────────────────────────────────────────
function updateTeams(teams) {
    if (!teams) return;
    const grid = document.getElementById('teamsGrid');

    teams.forEach(t => {
        let el = document.getElementById('tc-' + t.id);
        if (!el) {
            el = document.createElement('div');
            el.id = 'tc-' + t.id;
            el.className = 'team-card';
            el.style.cssText = `--tc:${t.color}`;
            el.innerHTML = `
                <span class="t-icon">${t.icon}</span>
                <div class="t-name">${t.name}</div>
                <div class="t-role">${t.roleLabel}</div>
                <div class="t-score" id="ts-${t.id}">${t.score}</div>
                <div class="t-delta" id="td-${t.id}"></div>
                <div class="t-badge" id="tb-${t.id}">${t.badge.icon}</div>
                <div class="t-badge-nm" id="tbn-${t.id}">${t.badge.name}</div>
                <div class="t-online"><span class="dot-on"></span><span id="ton-${t.id}">${t.onlineCount}</span></div>`;
            grid.appendChild(el);
            prevScores[t.id] = t.score;
        } else {
            const prev = prevScores[t.id] ?? t.score;
            if (t.score !== prev) {
                const delta = t.score - prev;
                const dEl = document.getElementById('td-' + t.id);
                dEl.textContent = (delta > 0 ? '+' : '') + delta;
                dEl.style.color = delta > 0 ? '#2dc653' : '#ef4444';
                dEl.style.display = 'block';
                dEl.style.animation = 'none'; void dEl.offsetWidth;
                dEl.style.animation = 'deltaUp .8s ease forwards';
                setTimeout(() => dEl.style.display = 'none', 860);
                prevScores[t.id] = t.score;
                playTone(delta > 0 ? 880 : 220, .18, 'sine', .12);
            }
            document.getElementById('ts-' + t.id).textContent = t.score;
            document.getElementById('tb-' + t.id).textContent = t.badge.icon;
            document.getElementById('tbn-' + t.id).textContent = t.badge.name;
            document.getElementById('ton-' + t.id).textContent = t.onlineCount;
        }
    });
}

// ── Activity feed ─────────────────────────────────────────────────
function handleActivity(broadcasts, injects) {
    if (broadcasts?.length) {
        const b = broadcasts[0];
        if (b.id > lastBcId) {
            lastBcId = b.id;
            if (b.isPhantom) showPhantom(b.message);
            else addFeed(b.type, b.message);
        }
    }
    if (injects?.length) {
        const inj = injects[0];
        if (inj.id > lastInjectId) {
            lastInjectId = inj.id;
            const d = document.createElement('div');
            d.className = 'feed-item alert';
            d.innerHTML = `<div class="fi-ts">${inj.tag}</div>${inj.content}`;
            const log = document.getElementById('injectLog');
            log.insertBefore(d, log.firstChild);
            playTone(220, .3, 'square', .15);
        }
    }
}

function addFeed(type, msg) {
    const fc = document.getElementById('feedList');
    const n = new Date();
    const ts = [n.getHours(), n.getMinutes()].map(x => String(x).padStart(2,'0')).join(':');
    const d = document.createElement('div');
    d.className = `feed-item ${type}`;
    d.innerHTML = `<div class="fi-ts">${ts}</div>${msg}`;
    fc.insertBefore(d, fc.firstChild);
    while (fc.children.length > 15) fc.removeChild(fc.lastChild);
}

// ── Vote ──────────────────────────────────────────────────────────
function handleVote(vote) {
    const el = document.getElementById('voteWidget');
    if (!vote) {
        el.innerHTML = '<div class="vote-q fst-italic">Aucun vote en cours</div>';
        return;
    }
    const total = Object.values(vote.tally).reduce((a,b) => a+b, 0) || 1;
    el.innerHTML = `<div class="vote-q">${vote.question ?? 'Vote en cours'}</div>
    <div class="vote-bars">${(vote.options||[]).map(o => {
        const pct = Math.round((vote.tally[o.key]??0) / total * 100);
        return `<div class="vote-bar-row">
            <span class="vb-lbl" style="color:${o.color||'var(--bs-theme)'}">${o.key}</span>
            <div class="vb-track"><div class="vb-fill" style="width:${pct}%;background:${o.color||'var(--bs-theme)'}"></div></div>
            <span class="vb-count">${vote.tally[o.key]??0}</span>
        </div>`;
    }).join('')}</div>`;
}

// ── Phantom ───────────────────────────────────────────────────────
function showPhantom(msg) {
    const el = document.getElementById('phMsg');
    el.textContent = msg; el.dataset.txt = msg;
    document.getElementById('phantomOv').classList.add('show');
    playLocalSound('tension');
    setTimeout(dismissPhantom, 12000);
}
function dismissPhantom() {
    document.getElementById('phantomOv').classList.remove('show');
}

// ── Endgame ───────────────────────────────────────────────────────
function fireEndgame(teams, session) {
    endgameFired = true;
    const sorted = [...teams].sort((a,b) => b.score - a.score);
    const top3 = sorted.slice(0, 3);
    const rest = sorted.slice(3);

    // Podium order: silver, gold, bronze
    const order = [1, 0, 2];
    const classes = ['p2', 'p1', 'p3'];
    const ranks = ['🥇 1ÈRE', '🥈 2ÈME', '🥉 3ÈME'];

    document.getElementById('podiumEl').innerHTML = order.map((ri, ci) => {
        const t = top3[ri]; if (!t) return '';
        return `<div class="podium-slot ${classes[ci]}">
            <div class="pod-bar">
                <div class="pod-icon">${t.icon}</div>
                <div class="pod-name">${t.name}</div>
                <div class="pod-score">${t.score}</div>
                <div class="pod-badge">${t.badge.icon}</div>
            </div>
            <div class="pod-base">${ranks[ri]}</div>
        </div>`;
    }).join('');

    if (rest.length) {
        document.getElementById('othersEl').innerHTML = rest.map((t,i) =>
            `<div class="other-tile">
                <div class="ot-rank">${i+4}ÈME</div>
                <div class="ot-name">${t.icon} ${t.name}</div>
                <div class="ot-score">${t.score} pts</div>
            </div>`).join('');
    }

    document.getElementById('endgameOv').classList.add('show');
    launchConfetti();
    playLocalSound('victory');
}

// ── Confetti ──────────────────────────────────────────────────────
function launchConfetti() {
    const canvas = document.getElementById('confettiCanvas');
    canvas.width = window.innerWidth; canvas.height = window.innerHeight;
    const ctx = canvas.getContext('2d');
    const parts = Array.from({length:200}, () => ({
        x: Math.random() * canvas.width, y: -20,
        w: 4 + Math.random() * 8, h: 8 + Math.random() * 14,
        vx: (Math.random()-.5)*3, vy: 2 + Math.random()*4,
        r: Math.random()*Math.PI*2, vr: (Math.random()-.5)*.15,
        c: ['var(--bs-theme)','#f59e0b','#2dc653','#8b5cf6','#ef4444','#f4a261']
            [Math.floor(Math.random()*6)],
    }));
    (function loop() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        parts.forEach(p => {
            p.x += p.vx; p.y += p.vy; p.r += p.vr; p.vy += .05;
            ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.r);
            ctx.fillStyle = p.c; ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h);
            ctx.restore();
        });
        if (parts.some(p => p.y < canvas.height)) requestAnimationFrame(loop);
    })();
}

// ── Audio ─────────────────────────────────────────────────────────
let audioCtx = null;
function getAudio() { if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)(); return audioCtx; }
function playTone(f, d, type='sine', vol=.2) {
    try {
        const c=getAudio(), o=c.createOscillator(), g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type=type; o.frequency.value=f;
        g.gain.setValueAtTime(vol,c.currentTime);
        g.gain.exponentialRampToValueAtTime(.001, c.currentTime+d);
        o.start(c.currentTime); o.stop(c.currentTime+d);
    } catch(e) {}
}
function playLocalSound(t) {
    if (t === 'tension') {
        const c=getAudio(),o=c.createOscillator(),g=c.createGain();
        o.connect(g); g.connect(c.destination);
        o.type='sawtooth'; o.frequency.value=55;
        g.gain.setValueAtTime(0,c.currentTime);
        g.gain.linearRampToValueAtTime(.18, c.currentTime+.6);
        g.gain.linearRampToValueAtTime(0, c.currentTime+3);
        o.start(c.currentTime); o.stop(c.currentTime+3);
    }
    if (t === 'victory') {
        [523,659,784,1047,1319].forEach((f,i) => setTimeout(() => playTone(f,.3,'triangle',.28), i*120));
    }
}

function fmt(s) {
    return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
}
</script>
</body>
</html>
