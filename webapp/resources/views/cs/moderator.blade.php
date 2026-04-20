@extends('layouts.app')
@section('title', 'CARTHAGE SHIELD — Console Modérateur')

@push('head')
<style>
.cs-title-sm{font-family:'Space Mono',monospace;font-weight:700;font-size:1.4rem;letter-spacing:2px}
.timer-lg{font-family:'Space Mono',monospace;font-size:3rem;font-weight:700;color:var(--bs-theme);text-shadow:0 0 20px rgba(var(--bs-theme-rgb),.4);transition:all .4s;line-height:1}
.timer-lg.warn{color:#f59e0b}.timer-lg.danger{color:#ef4444;animation:tPulse .6s infinite}
@keyframes tPulse{0%,100%{opacity:1}50%{opacity:.4}}

.team-ctrl{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:14px 12px;border-top:4px solid var(--tc);transition:all .2s}
.team-ctrl:hover{border-color:rgba(255,255,255,.18)}
.team-icon-big{font-size:1.8rem}
.score-val{font-family:'Space Mono',monospace;font-size:2rem;font-weight:700;color:var(--tc);line-height:1}

.inject-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;margin-bottom:8px;cursor:pointer;transition:all .2s;border-left:3px solid transparent}
.inject-card:hover{border-color:var(--bs-theme);background:rgba(var(--bs-theme-rgb),.06)}
.inject-card .ic-tag{font-size:.7rem;font-family:'Space Mono',monospace;color:rgba(255,255,255,.4);margin-bottom:4px}

.atmo-btn{padding:8px 14px;border-radius:6px;font-size:.78rem;font-weight:700;letter-spacing:1px;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);transition:all .2s;text-transform:uppercase}
.atmo-btn:hover{border-color:rgba(255,255,255,.3)}.atmo-btn.active{filter:brightness(1.3);transform:scale(1.05)}

.decision-review{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;margin-bottom:8px}
.decision-review .dr-type{font-family:'Space Mono',monospace;font-size:.7rem;color:var(--bs-theme);margin-bottom:4px}
.ld-award{font-size:.8rem}
</style>
@endpush

@section('content')
<div class="container-fluid py-3" id="csMod">

<div class="row gx-3">

    {{-- TOP BAR --}}
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div>
                        <div class="cs-title-sm text-theme">🛡️ CONSOLE MODÉRATEUR</div>
                        <div class="small text-white-50 mt-1">
                            {{ $session->name }}
                            &middot; <span style="font-family:'Space Mono',monospace">{{ $session->code }}</span>
                            &middot; <span id="statusPill" class="badge bg-dark">{{ strtoupper($session->status) }}</span>
                        </div>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="timer-lg" id="modTimer">--:--</div>
                        <div>
                            <div class="d-flex gap-2">
                                <button onclick="api('timer/start','POST')" class="btn btn-sm btn-success fw-bold"><i class="bi bi-play-fill"></i> START</button>
                                <button onclick="api('timer/pause','POST')" class="btn btn-sm btn-warning text-dark fw-bold"><i class="bi bi-pause-fill"></i></button>
                                <button onclick="api('timer/reset','POST')" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-counterclockwise"></i></button>
                            </div>
                            <div class="d-flex gap-2 mt-1 align-items-center">
                                <input type="number" id="timerMinInput" class="form-control form-control-sm" style="width:70px" value="20" min="1" max="120">
                                <button onclick="setTimer()" class="btn btn-sm btn-outline-theme">Set (min)</button>
                                <button onclick="api('phase/advance','POST')" class="btn btn-sm btn-outline-warning fw-bold">Phase ▶</button>
                                <button onclick="confirmEnd()" class="btn btn-sm btn-outline-danger">⏹ FIN</button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap ms-2">
                        <span class="small text-white-50">Atmosphère:</span>
                        @foreach([['calm','#22c55e'],['tension','#f59e0b'],['crisis','#ef4444'],['hacked','#7c3aed'],['victory','#fbbf24']] as [$m,$cl])
                        <button class="atmo-btn" id="atmo-{{ $m }}" style="border-color:{{ $cl }};color:{{ $cl }}" onclick="setAtmo('{{ $m }}')">{{ strtoupper($m) }}</button>
                        @endforeach
                    </div>
                </div>
                {{-- Phase bar --}}
                <div class="d-flex align-items-center gap-2 mt-3">
                    @foreach($scenario['phases'] as $p)
                    <button onclick="api('phase/goto','POST',{index:{{ $p['index'] }}})" class="btn btn-sm" id="ph-btn-{{ $p['index'] }}"
                        style="font-size:.75rem;padding:3px 10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04)">
                        {{ $p['name'] }}
                    </button>
                    @endforeach
                    <span class="small text-white-50 ms-2" id="phaseLabel">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- LEFT: Teams Score Control --}}
    <div class="col-lg-8 mb-3">

        {{-- Team Grid --}}
        <div class="row gx-2 mb-3" id="teamGrid">
            @foreach($teams as $t)
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="team-ctrl" style="--tc:{{ $t->color }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="team-icon-big">{{ $t->icon }}</span>
                        <div>
                            <div class="fw-bold" style="font-size:.9rem">{{ $t->name }}</div>
                            <div class="small text-white-50" style="font-size:.72rem">{{ $t->role_label }}</div>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="score-val" id="msc-{{ $t->id }}">{{ $t->score }}</div>
                            <div class="small text-white-50" id="mon-{{ $t->id }}">
                                <i class="bi bi-person-fill text-success"></i> 0
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        @foreach([['-50','danger'],['-20','secondary'],['-10','secondary'],['+10','theme'],['+20','theme'],['+50','success']] as [$d,$c])
                        <button onclick="adjustScore({{ $t->id }},{{$d}})" class="btn btn-sm btn-{{ $c }} fw-bold flex-fill" style="padding:3px 0;font-size:.75rem">{{ $d }}</button>
                        @endforeach
                    </div>
                    <div class="small text-white-50 mt-2">Badge: <span id="mbadge-{{ $t->id }}">{{ $t->badge_icon ?? '🛡️' }}</span></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Broadcast + Vote --}}
        <div class="row gx-2">
            <div class="col-md-6 mb-2">
                <div class="card h-100">
                    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    <div class="card-body">
                        <h6 class="card-title mb-2"><i class="bi bi-broadcast me-2 text-theme"></i>Diffuser un message</h6>
                        <select class="form-select form-select-sm mb-2" id="bcType">
                            <option value="info">Info</option>
                            <option value="warn">Avertissement</option>
                            <option value="alert">Alerte</option>
                            <option value="success">Succès</option>
                        </select>
                        <textarea class="form-control form-control-sm mb-2" id="bcMsg" rows="3" placeholder="Message..."></textarea>
                        <div class="d-flex gap-2">
                            <button onclick="broadcast()" class="btn btn-sm btn-theme flex-fill fw-bold">Diffuser</button>
                            <button onclick="phantom()" class="btn btn-sm btn-danger fw-bold" title="Message PHANTOM">☠️</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="card h-100">
                    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    <div class="card-body">
                        <h6 class="card-title mb-2"><i class="bi bi-hand-thumbs-up me-2 text-warning"></i>Vote stratégique</h6>
                        <input class="form-control form-control-sm mb-2" id="voteQ" placeholder="Question...">
                        <div class="d-flex gap-1 mb-2">
                            <input class="form-control form-control-sm" id="vOpt" placeholder="Option (ex: A, B, C)">
                            <button onclick="openVote()" class="btn btn-sm btn-warning text-dark fw-bold">Ouvrir</button>
                        </div>
                        <button onclick="api('vote/close','POST')" class="btn btn-sm btn-danger fw-bold w-100">Fermer le vote</button>
                        <div class="mt-2 small text-white-50" id="voteTally">—</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT: Injects + Decisions --}}
    <div class="col-lg-4 mb-3">

        {{-- Injects --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h6 class="card-title mb-2"><i class="bi bi-lightning me-2 text-warning"></i>Injectons
                    <span class="badge bg-dark ms-1" style="font-size:.7rem">{{ $injects->count() }}</span>
                </h6>
                <div style="max-height:380px;overflow-y:auto">
                    @forelse($injects as $inj)
                    <div class="inject-card" onclick="triggerInject({{ $inj->id }})">
                        <div class="ic-tag">{{ $inj->tag }} @if($inj->phase_hint) &middot; Phase {{ $inj->phase_hint }} @endif</div>
                        <div style="font-size:.85rem;font-weight:600">{{ Str::limit($inj->content, 60) }}</div>
                        <div class="text-white-50 mt-1" style="font-size:.72rem">{{ $inj->color === 'red' ? '🔴' : ($inj->is_surprise ? '⚡' : '🟡') }} {{ $inj->is_surprise ? 'Surprise' : 'Standard' }}</div>
                    </div>
                    @empty
                    <div class="text-white-50 text-center py-3 small">Aucun inject disponible</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pending Decisions --}}
        <div class="card">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h6 class="card-title mb-2"><i class="bi bi-clipboard-check me-2 text-theme"></i>Décisions reçues</h6>
                <div id="decisionsArea" style="max-height:300px;overflow-y:auto">
                    <div class="text-white-50 text-center py-3 small">En attente...</div>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const CODE = '{{ $session->code }}';
const CSRF = '{{ csrf_token() }}';
const PHASES = {{ count($scenario['phases']) }};

let lastDecId = 0, lastBcId = 0;

async function api(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/cs/${CODE}/api/${path}`, opts);
    return r.json();
}

// ── POLL ──────────────────────────────────────────────────
async function poll() {
    try {
        const d = await api('state');
        updateTimer(d.timer);
        updatePhase(d.session);
        updateTeams(d.teams);
        updateVoteTally(d.vote);
        updateDecisions(d.decisions);
    } catch(e) {}
}
setInterval(poll, 2000); poll();

// ── TIMER ──────────────────────────────────────────────────
function updateTimer(timer) {
    let secs = 0;
    if (timer.isRunning && timer.endsAt) secs = Math.max(0, Math.round((new Date(timer.endsAt) - Date.now()) / 1000));
    else secs = timer.remainingSeconds ?? 0;
    const el = document.getElementById('modTimer');
    el.textContent = `${String(Math.floor(secs/60)).padStart(2,'0')}:${String(secs%60).padStart(2,'0')}`;
    el.className = 'timer-lg' + (secs<=60?' danger':secs<=180?' warn':'');
}

function setTimer() {
    const mins = parseInt(document.getElementById('timerMinInput').value) || 20;
    api('timer/set','POST',{seconds: mins*60});
}

// ── PHASE ──────────────────────────────────────────────────
function updatePhase(s) {
    const idx = s.currentPhaseIndex ?? 0;
    document.getElementById('phaseLabel').textContent = s.currentPhase?.name ?? '—';
    document.getElementById('statusPill').textContent = (s.status||'').toUpperCase();
    for (let i=0; i<PHASES; i++) {
        const b = document.getElementById('ph-btn-'+i);
        if (b) {
            b.style.background = i===idx ? 'rgba(var(--bs-theme-rgb),.2)' : 'rgba(255,255,255,.04)';
            b.style.borderColor = i===idx ? 'var(--bs-theme)' : 'rgba(255,255,255,.12)';
            b.style.color = i===idx ? 'var(--bs-theme)' : '#fff';
        }
    }
}

// ── TEAMS ──────────────────────────────────────────────────
function updateTeams(teams) {
    if (!teams) return;
    teams.forEach(t => {
        const sc = document.getElementById('msc-'+t.id);
        const on = document.getElementById('mon-'+t.id);
        const bg = document.getElementById('mbadge-'+t.id);
        if (sc) sc.textContent = t.score;
        if (on) on.innerHTML = `<i class="bi bi-person-fill text-success"></i> ${t.onlineCount}`;
        if (bg) bg.textContent = t.badge.icon;
    });
}

// ── SCORE ──────────────────────────────────────────────────
async function adjustScore(teamId, delta) {
    const d = parseInt(delta);
    await api(`score/${teamId}`, 'POST', {delta:d});
}

// ── BROADCAST ──────────────────────────────────────────────
async function broadcast() {
    const msg  = document.getElementById('bcMsg').value.trim();
    const type = document.getElementById('bcType').value;
    if (!msg) return;
    await api('broadcast','POST',{message:msg, type});
    document.getElementById('bcMsg').value = '';
    showNotif('Diffusé !');
}

async function phantom() {
    const msg = document.getElementById('bcMsg').value.trim() || 'PHANTOM GRID HAS BREACHED YOUR NETWORK.';
    await api('phantom','POST',{message:msg});
    document.getElementById('bcMsg').value = '';
    showNotif('☠️ Message PHANTOM envoyé');
}

// ── VOTE ───────────────────────────────────────────────────
async function openVote() {
    const q = document.getElementById('voteQ').value.trim();
    const rawOpts = document.getElementById('vOpt').value.split(',').map(s=>s.trim()).filter(Boolean);
    if (!q || !rawOpts.length) { showNotif('Question et options requises','danger'); return; }
    const options = rawOpts.map(o => ({key:o.toUpperCase()[0], label:o}));
    await api('vote/open','POST',{question:q, options});
    showNotif('Vote ouvert');
}

function updateVoteTally(vote) {
    const el = document.getElementById('voteTally');
    if (!vote) { el.textContent = '—'; return; }
    el.textContent = Object.entries(vote.tally||{}).map(([k,v])=>`${k}: ${v}`).join(' | ');
}

// ── INJECT ─────────────────────────────────────────────────
async function triggerInject(id) {
    if (!confirm('Déclencher cet inject ?')) return;
    await api(`inject/${id}`,'POST');
    showNotif('Inject déclenché','success');
}

// ── ATMOSPHERE ─────────────────────────────────────────────
async function setAtmo(mode) {
    await api('atmosphere','POST',{mode});
    document.querySelectorAll('.atmo-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('atmo-'+mode)?.classList.add('active');
}

// ── DECISIONS ──────────────────────────────────────────────
function updateDecisions(decisions) {
    if (!decisions?.length) return;
    const latest = decisions[0];
    if (latest.id <= lastDecId) return;
    lastDecId = latest.id;

    const area = document.getElementById('decisionsArea');
    if (area.querySelector('.text-white-50')) area.innerHTML = '';

    const div = document.createElement('div');
    div.className = 'decision-review';
    div.innerHTML = `
        <div class="dr-type">${latest.type?.toUpperCase()} &middot; ${latest.teamName}</div>
        <div style="font-size:.83rem">${latest.content}</div>
        <div class="d-flex gap-1 mt-2 align-items-center">
            <span class="small text-white-50">Score:</span>
            <input type="number" id="award-${latest.id}" value="10" min="0" max="100" class="form-control form-control-sm" style="width:70px">
            <button onclick="awardScore(${latest.id})" class="btn btn-sm btn-success ld-award">Attribuer</button>
        </div>`;
    area.insertBefore(div, area.firstChild);
}

async function awardScore(id) {
    const pts = parseInt(document.getElementById('award-'+id).value);
    await api(`decision/${id}/award`,'POST',{points:pts});
    showNotif(`+${pts} pts attribués`,'success');
}

// ── END ────────────────────────────────────────────────────
async function confirmEnd() {
    if (!confirm('Terminer l\'exercice ? Cette action est irréversible.')) return;
    await api('end','POST');
    showNotif('Exercice terminé !','success');
}

// ── NOTIF ──────────────────────────────────────────────────
function showNotif(msg, type='success') {
    const colors = {success:'#22c55e',danger:'#ef4444',warn:'#f59e0b'};
    const div = document.createElement('div');
    div.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;padding:10px 18px;border-radius:8px;background:#1a2a3a;border:1px solid ${colors[type]||colors.success};color:#fff;font-size:.85rem;transition:opacity .4s`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity='0'; setTimeout(()=>div.remove(),400); }, 3000);
}
</script>
@endpush
