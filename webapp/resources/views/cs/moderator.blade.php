@extends('layouts.app')
@section('title', 'CARTHAGE SHIELD — Console Modérateur')

@push('head')
<style>
/* ── Base ──────────────────────────────────────────────── */
.cs-mono{font-family:'Space Mono',monospace}
.cs-title-sm{font-family:'Space Mono',monospace;font-weight:700;font-size:1.4rem;letter-spacing:2px}
.timer-lg{font-family:'Space Mono',monospace;font-size:3rem;font-weight:700;color:var(--bs-theme);text-shadow:0 0 20px rgba(var(--bs-theme-rgb),.4);transition:all .4s;line-height:1}
.timer-lg.warn{color:#f59e0b}.timer-lg.danger{color:#ef4444;animation:tPulse .6s infinite}
@keyframes tPulse{0%,100%{opacity:1}50%{opacity:.4}}

/* ── Team Cards ── */
.team-ctrl{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:14px 12px;border-top:4px solid var(--tc);transition:all .2s}
.team-ctrl:hover{border-color:rgba(255,255,255,.18)}
.team-icon-big{font-size:1.8rem}
.score-val{font-family:'Space Mono',monospace;font-size:2rem;font-weight:700;color:var(--tc);line-height:1}

/* ── Inject Cards ── */
.inject-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;margin-bottom:8px;cursor:pointer;transition:all .2s;border-left:3px solid transparent}
.inject-card:hover{border-color:var(--bs-theme);background:rgba(var(--bs-theme-rgb),.06)}
.inject-card .ic-tag{font-size:.7rem;font-family:'Space Mono',monospace;color:rgba(255,255,255,.4);margin-bottom:4px}
.inject-card.has-target{border-left-color:#f59e0b}

/* ── Atmosphere Buttons ── */
.atmo-btn{padding:8px 14px;border-radius:6px;font-size:.78rem;font-weight:700;letter-spacing:1px;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);transition:all .2s;text-transform:uppercase}
.atmo-btn:hover{border-color:rgba(255,255,255,.3)}.atmo-btn.active{filter:brightness(1.3);transform:scale(1.05)}

/* ── Decision Review ── */
.decision-review{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px;margin-bottom:8px}
.decision-review .dr-type{font-family:'Space Mono',monospace;font-size:.7rem;color:var(--bs-theme);margin-bottom:4px}
.decision-review .dr-team{font-size:.75rem;font-weight:700;padding:2px 6px;border-radius:4px}
.ld-award{font-size:.8rem}
.dec-new{animation:decSlide .4s ease-out}
@keyframes decSlide{from{transform:translateY(-8px);opacity:0}to{transform:translateY(0);opacity:1}}

/* ── Decision Matrix Panel ── */
.matrix-panel{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:10px;overflow:hidden}
.matrix-header{background:rgba(var(--bs-theme-rgb),.12);padding:10px 14px;font-size:.8rem;font-weight:700;font-family:'Space Mono',monospace;letter-spacing:1px}
.matrix-context{padding:10px 14px;font-size:.82rem;color:rgba(255,255,255,.6);font-style:italic;border-bottom:1px solid rgba(255,255,255,.06)}
.matrix-option{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.05);transition:background .15s}
.matrix-option:last-child{border-bottom:none}
.matrix-option:hover{background:rgba(255,255,255,.03)}
.option-key{width:28px;height:28px;border-radius:6px;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Space Mono',monospace}
.option-pts{font-family:'Space Mono',monospace;font-weight:700;font-size:1.1rem;min-width:52px;text-align:right}
.option-note{font-size:.78rem;color:rgba(255,255,255,.5);margin-top:3px}
.matrix-hint{padding:8px 14px;background:rgba(251,191,36,.06);border-top:1px solid rgba(251,191,36,.15);font-size:.78rem;color:#fbbf24}

/* ── Bonus Badges ── */
.badge-award-row{padding:6px 0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.badge-pill{padding:5px 10px;border-radius:20px;font-size:.75rem;font-weight:700;cursor:pointer;border:1px solid;transition:all .2s;white-space:nowrap}
.badge-pill:hover{filter:brightness(1.2);transform:scale(1.04)}

/* ── Vote Tally ── */
.tally-bar-wrap{margin-top:4px}
.tally-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.06);overflow:hidden;margin-bottom:2px}
.tally-bar-fill{height:100%;border-radius:3px;transition:width .5s ease}

/* ── Tabs ── */
.mod-tab{padding:6px 14px;font-size:.78rem;font-weight:700;cursor:pointer;border-radius:6px;transition:all .15s;color:rgba(255,255,255,.5);border:1px solid transparent}
.mod-tab.active{background:rgba(var(--bs-theme-rgb),.15);color:var(--bs-theme);border-color:rgba(var(--bs-theme-rgb),.3)}
.mod-tab:hover:not(.active){color:#fff}
.tab-pane{display:none}.tab-pane.show{display:block}

/* ── Badge Log ── */
.badge-log-entry{display:flex;align-items:center;gap:8px;padding:8px 10px;background:rgba(255,255,255,.03);border-radius:7px;margin-bottom:6px;border:1px solid rgba(255,255,255,.06)}
.bank-block{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:10px}
.bank-item{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:8px;margin-bottom:8px}
.bank-item:last-child{margin-bottom:0}
.bank-item p{margin:0;font-size:.8rem}
.bank-note{font-size:.74rem;color:rgba(255,255,255,.6)}
</style>
@endpush

@section('content')
<div class="container-fluid py-3" id="csMod">
<div class="row gx-3">

    {{-- ══ TOP BAR ══ --}}
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div>
                        <div class="cs-title-sm text-theme">🛡️ CONSOLE MODÉRATEUR</div>
                        <div class="small text-white-50 mt-1">
                            {{ $session->name }}
                            &middot; <span class="cs-mono">{{ $session->code }}</span>
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
                <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                    @foreach($scenario['phases'] as $p)
                    <button onclick="api('phase/goto','POST',{index:{{ $p['index'] }}})" class="btn btn-sm" id="ph-btn-{{ $p['index'] }}"
                        style="font-size:.75rem;padding:3px 10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04)">
                        {{ $p['name'] }}
                    </button>
                    @endforeach
                    <span class="small text-white-50 ms-2" id="phaseLabel">—</span>
                    <span class="badge bg-dark ms-1 small" id="onlineCount">0 en ligne</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ LEFT COLUMN: Scores + Broadcast + Vote ══ --}}
    <div class="col-lg-5 mb-3">

        {{-- Team Score Grid --}}
        <div class="row gx-2 mb-3" id="teamGrid">
            @foreach($teams as $t)
            <div class="col-md-6 mb-2">
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
                    {{-- Score buttons --}}
                    <div class="d-flex gap-1 mb-2">
                        @foreach([['-20','danger'],['-10','secondary'],['+5','secondary'],['+10','theme'],['+20','theme'],['+25','success']] as [$d,$c])
                        <button onclick="adjustScore({{ $t->id }},{{$d}})" class="btn btn-sm btn-{{ $c }} fw-bold flex-fill" style="padding:3px 0;font-size:.75rem">{{ $d }}</button>
                        @endforeach
                    </div>
                    {{-- Badge status & bonus badges --}}
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-white-50">Badge: <span id="mbadge-{{ $t->id }}">{{ $t->badge_icon ?? '🛡️' }}</span></span>
                        <div class="ms-auto d-flex gap-1 flex-wrap" id="bonusBadges-{{ $t->id }}">
                            {{-- filled by JS --}}
                        </div>
                    </div>
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
                        <textarea class="form-control form-control-sm mb-2 cs-mono" id="vOpt"
                                  placeholder="Options manuelles (une ligne = A|Label|#00b4d8|20|Note)"
                                  rows="3"></textarea>
                        <div id="preparedVoteInfo" class="bank-note mb-2">Aucune question préchargée depuis la bibliothèque.</div>
                        <button onclick="openVote()" class="btn btn-sm btn-warning text-dark fw-bold w-100 mb-2">Ouvrir</button>
                        <button onclick="closeVoteWithScore()" class="btn btn-sm btn-danger fw-bold w-100">Fermer & Scorer</button>
                        <div class="mt-2" id="voteTally"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ MIDDLE COLUMN: Decision Matrix + Bonus Badges ══ --}}
    <div class="col-lg-4 mb-3">

        {{-- Scenario Library --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h6 class="card-title mb-2"><i class="bi bi-journal-text me-2 text-info"></i>Bibliothèque de scénario</h6>
                <div class="d-flex gap-2 mb-2">
                    <select id="bankPhaseSelect" class="form-select form-select-sm" onchange="loadBankForPhase(this.value)">
                        @foreach($scenario['phases'] as $p)
                            <option value="{{ $p['index'] }}">Phase {{ $p['index'] }} - {{ $p['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-theme" onclick="refreshBank()">Rafraichir</button>
                </div>
                <div class="bank-block mb-2">
                    <div class="small fw-bold text-theme mb-2">Messages</div>
                    <div id="bankMessages" class="small text-white-50">Chargement...</div>
                </div>
                <div class="bank-block">
                    <div class="small fw-bold text-theme mb-2">Questions stratégiques</div>
                    <div id="bankQuestions" class="small text-white-50">Chargement...</div>
                </div>
            </div>
        </div>

        {{-- Decision Matrix (active phase) --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body p-0">
                <div id="matrixPanel">
                    <div class="text-white-50 text-center py-4 small">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Sélectionnez une phase pour voir la matrice
                    </div>
                </div>
            </div>
        </div>

        {{-- Bonus Badges Award --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="bi bi-award me-2 text-warning"></i>Badges Bonus (+5 pts)</h6>
                <div class="mb-2">
                    <select class="form-select form-select-sm mb-2" id="badgeTeamSelect">
                        <option value="">— Équipe —</option>
                        @foreach($teams as $t)
                        <option value="{{ $t->id }}" data-color="{{ $t->color }}">{{ $t->icon }} {{ $t->name }}</option>
                        @endforeach
                    </select>
                    <div class="d-flex flex-wrap gap-2">
                        <button onclick="awardBadge('first_responder')" class="badge-pill d-flex align-items-center gap-1" style="border-color:#22c55e;color:#22c55e">
                            <img src="/cs-assets/badges/observateur.png" style="width:22px;height:22px;object-fit:contain" alt="">
                            First Responder
                        </button>
                        <button onclick="awardBadge('crisis_communicator')" class="badge-pill d-flex align-items-center gap-1" style="border-color:#f59e0b;color:#f59e0b">
                            <img src="/cs-assets/badges/analyst.png" style="width:22px;height:22px;object-fit:contain" alt="">
                            Crisis Comm.
                        </button>
                        <button onclick="awardBadge('zero_silo')" class="badge-pill d-flex align-items-center gap-1" style="border-color:#00b4d8;color:#00b4d8">
                            <img src="/cs-assets/badges/stratege.png" style="width:22px;height:22px;object-fit:contain" alt="">
                            Zero Silo
                        </button>
                        <button onclick="awardBadge('innovation')" class="badge-pill d-flex align-items-center gap-1" style="border-color:#8b5cf6;color:#8b5cf6">
                            <img src="/cs-assets/badges/cyber_hero.png" style="width:22px;height:22px;object-fit:contain" alt="">
                            Innovation
                        </button>
                    </div>
                </div>
                <div id="badgeLog" style="max-height:160px;overflow-y:auto;margin-top:10px">
                    <div class="text-white-50 text-center small py-2">Aucun badge attribué</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ RIGHT COLUMN: Injects + Decisions ══ --}}
    <div class="col-lg-3 mb-3">

        {{-- Tabs --}}
        <div class="d-flex gap-1 mb-2">
            <button class="mod-tab active" id="tab-injects" onclick="switchTab('injects')"><i class="bi bi-lightning me-1"></i>Injects <span id="injectCount" class="badge bg-dark ms-1">{{ $injects->count() }}</span></button>
            <button class="mod-tab" id="tab-decisions" onclick="switchTab('decisions')"><i class="bi bi-clipboard-check me-1"></i>Décisions <span id="decCount" class="badge bg-dark ms-1">0</span></button>
        </div>

        {{-- Injects Tab --}}
        <div class="tab-pane show" id="pane-injects">
            <div class="card">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body p-2">
                    {{-- Inject target filter --}}
                    <div class="mb-2">
                        <select class="form-select form-select-sm" id="injectTargetFilter" onchange="filterInjects()">
                            <option value="">Tous les injects</option>
                            @foreach($teams as $t)
                            <option value="{{ $t->type }}">{{ $t->icon }} {{ $t->name }}</option>
                            @endforeach
                            <option value="__global">🌐 Global uniquement</option>
                        </select>
                    </div>
                    <div style="max-height:calc(100vh - 300px);overflow-y:auto" id="injectsContainer">
                        @forelse($injects as $inj)
                        <div class="inject-card {{ $inj->target_team_type ? 'has-target' : '' }}" onclick="triggerInject({{ $inj->id }})"
                             data-target="{{ $inj->target_team_type ?? '' }}" data-phase="{{ $inj->phase_hint ?? '' }}">
                            <div class="ic-tag">{{ $inj->tag }} @if($inj->phase_hint) &middot; Phase {{ $inj->phase_hint }} @endif
                                @if($inj->target_team_type)<span class="ms-1 text-warning">→ {{ strtoupper($inj->target_team_type) }}</span>@endif
                            </div>
                            <div style="font-size:.85rem;font-weight:600">{{ Str::limit($inj->content, 70) }}</div>
                            <div class="text-white-50 mt-1" style="font-size:.72rem">
                                {{ $inj->color === 'red' ? '🔴' : ($inj->is_surprise ? '⚡' : '🟡') }}
                                {{ $inj->is_surprise ? 'Surprise' : 'Standard' }}
                                @if($inj->target_team_type) &middot; <span class="text-warning">Ciblé</span> @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-white-50 text-center py-3 small">Aucun inject disponible</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Decisions Tab --}}
        <div class="tab-pane" id="pane-decisions">
            <div class="card">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body p-2">
                    <div style="max-height:calc(100vh - 240px);overflow-y:auto" id="decisionsArea">
                        <div class="text-white-50 text-center py-3 small">En attente de décisions...</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const CODE  = '{{ $session->code }}';
const CSRF  = '{{ csrf_token() }}';
const PHASES = {{ count($scenario['phases']) }};

// Pre-load the scenario phases for the matrix (passed from server)
const SCENARIO_PHASES = @json($scenario['phases']);
const INITIAL_BANK_BY_PHASE = @json($initialBankByPhase ?? []);

let lastDecId = 0, lastBadgeId = 0, lastDecCount = 0;
let currentPhaseIndex = null;
let currentBank = { messages: [], questions: [] };

async function api(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/cs/${CODE}/api/${path}`, opts);
    return r.json();
}

// ── POLL ───────────────────────────────────────────────────
async function poll() {
    try {
        const d = await api('state');
        updateTimer(d.timer);
        updatePhase(d.session);
        updateTeams(d.teams);
        updateVoteTally(d.vote);
        updateDecisions(d.decisions ?? []);
        updateBadgeLog(d.badges ?? []);
        updateMatrix(d.decisionMatrix);
        updateOnlineCount(d.onlinePlayers ?? []);
    } catch(e) { console.warn('Poll error', e); }
}
setInterval(poll, 2000); poll();

// ── TIMER ───────────────────────────────────────────────────
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

// ── PHASE ───────────────────────────────────────────────────
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

    // Update matrix from local phases data (always current)
    const phase = SCENARIO_PHASES[idx] ?? null;
    updateMatrix(phase?.decision_matrix ?? null);

    if (currentPhaseIndex !== idx) {
        currentPhaseIndex = idx;
        const phaseSelect = document.getElementById('bankPhaseSelect');
        if (phaseSelect) phaseSelect.value = String(idx);
        loadBankForPhase(idx);
    }
}

async function refreshBank() {
    const phaseValue = document.getElementById('bankPhaseSelect')?.value ?? currentPhaseIndex ?? 0;
    await loadBankForPhase(phaseValue);
}

async function loadBankForPhase(phaseIndex) {
    const idx = parseInt(phaseIndex, 10) || 0;
    try {
        const data = await api(`bank?phase_index=${idx}`);
        if (data?.ok) {
            currentBank = {
                messages: data.messages ?? [],
                questions: data.questions ?? [],
            };
        } else {
            throw new Error('bank endpoint not ok');
        }
    } catch (e) {
        // Fallback server-rendered payload, avoids hard failure if API bank is blocked in prod.
        currentBank = INITIAL_BANK_BY_PHASE?.[idx] ?? { messages: [], questions: [] };
        showNotif('Bibliotheque chargee via fallback local', 'warn');
    }

    renderBankMessages();
    renderBankQuestions();
}

function renderBankMessages() {
    const root = document.getElementById('bankMessages');
    if (!root) return;
    if (!currentBank.messages.length) {
        root.innerHTML = '<div class="text-white-50">Aucun message disponible pour cette phase.</div>';
        return;
    }

    root.innerHTML = currentBank.messages.map((m, idx) => `
        <div class="bank-item">
            <p>${m.content || ''}</p>
            <div class="mt-2 d-flex justify-content-between align-items-center">
                <span class="badge bg-dark">${(m.type || 'info').toUpperCase()}</span>
                <button class="btn btn-sm btn-outline-theme" onclick="sendBankMessage(${idx})">Envoyer au live feed</button>
            </div>
        </div>
    `).join('');
}

function renderBankQuestions() {
    const root = document.getElementById('bankQuestions');
    if (!root) return;
    if (!currentBank.questions.length) {
        root.innerHTML = '<div class="text-white-50">Aucune question disponible pour cette phase.</div>';
        return;
    }

    root.innerHTML = currentBank.questions.map((q, idx) => `
        <div class="bank-item">
            <p class="fw-bold">${idx + 1}. ${q.question || 'Question sans titre'}</p>
            <div class="bank-note mt-1">${(q.options || []).map(o => `${o.key}: ${o.label} (${o.points ?? 0} pts)`).join(' | ')}</div>
            <div class="mt-2 d-flex justify-content-end">
                <button class="btn btn-sm btn-warning text-dark" onclick="prefillVoteFromBank(${idx})">Pre-remplir le vote</button>
            </div>
        </div>
    `).join('');
}

async function sendBankMessage(index) {
    const msg = currentBank.messages[index];
    if (!msg?.content) return;
    await api('broadcast', 'POST', { message: msg.content, type: msg.type || 'info' });
    showNotif('Message de la bibliotheque diffuse', 'success');
}

function prefillVoteFromBank(index) {
    const question = currentBank.questions[index];
    if (!question) return;

    document.getElementById('voteQ').value = question.question || '';
    
    const opts = Array.isArray(question.options) ? question.options : [];
    document.getElementById('vOpt').value = opts.map(opt => `${opt.key}|${opt.label}|${opt.color||'#00b4d8'}|${opt.points||0}|${opt.note||''}`).join('\n');

    const info = document.getElementById('preparedVoteInfo');
    const notes = opts.map(opt => `${opt.key}: ${opt.note || 'pas de note'}`).join(' | ');
    info.textContent = `Question préchargée (${opts.length} options). Guide: ${notes}`;
}

// ── ONLINE COUNT ────────────────────────────────────────────
function updateOnlineCount(players) {
    document.getElementById('onlineCount').textContent = `${players.length} en ligne`;
}

// ── TEAMS ───────────────────────────────────────────────────
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

async function adjustScore(teamId, delta) {
    await api(`score/${teamId}`, 'POST', {delta: parseInt(delta)});
}

// ── DECISION MATRIX ─────────────────────────────────────────
function updateMatrix(matrix) {
    const panel = document.getElementById('matrixPanel');
    if (!matrix) {
        panel.innerHTML = '<div class="text-white-50 text-center py-4 small"><i class="bi bi-grid-3x3-gap me-2"></i>Pas de matrice pour cette phase</div>';
        return;
    }
    const optColors = {A:'#ef4444', B:'#22c55e', C:'#f59e0b'};
    const optsHtml = (matrix.options ?? []).map(o => `
        <div class="matrix-option">
            <div class="d-flex align-items-center gap-2">
                <div class="option-key" style="background:${optColors[o.key]||'#555'}26;color:${optColors[o.key]||'#aaa'}">${o.key}</div>
                <div class="flex-fill">
                    <div style="font-size:.83rem;font-weight:600">${o.label}</div>
                    <div class="option-note">${o.note||''}</div>
                </div>
                <div class="option-pts" style="color:${optColors[o.key]||'#fbbf24'}">${o.points}<small class="text-white-50" style="font-size:.65rem"> pts</small></div>
            </div>
        </div>`).join('');
    const injectsHtml = (matrix.injects ?? []).map(i => `<li style="font-size:.78rem;color:rgba(255,255,255,.55)">${i}</li>`).join('');
    panel.innerHTML = `
        <div class="matrix-panel">
            <div class="matrix-header text-theme"><i class="bi bi-grid-3x3-gap me-2"></i>MATRICE DE DÉCISION</div>
            ${matrix.context ? `<div class="matrix-context">${matrix.context}</div>` : ''}
            ${injectsHtml ? `<ul class="mb-0 ps-3 py-2" style="border-bottom:1px solid rgba(255,255,255,.06)">${injectsHtml}</ul>` : ''}
            ${optsHtml}
            ${matrix.hint ? `<div class="matrix-hint"><i class="bi bi-lightbulb me-1"></i>${matrix.hint}</div>` : ''}
        </div>`;
}

// ── BROADCAST ───────────────────────────────────────────────
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

// ── VOTE ────────────────────────────────────────────────────
async function openVote() {
    const q = document.getElementById('voteQ').value.trim();
    if (!q) {
        showNotif('Question requise', 'danger');
        return;
    }

    const options = parseManualOptions(document.getElementById('vOpt').value);

    if (!options || options.length < 2) {
        showNotif('Au moins 2 options sont requises', 'danger');
        return;
    }

    const response = await api('vote/open', 'POST', { question: q, options });
    if (!response?.ok) {
        showNotif('Impossible d ouvrir le vote', 'danger');
        return;
    }
    showNotif('Vote ouvert');
    document.getElementById('preparedVoteInfo').textContent = 'Vote en cours...';
}

function parseManualOptions(raw) {
    const normalized = (raw || '').trim();
    if (!normalized) return [];

    // Backward compatibility: old input style "A, B, C"
    if (!normalized.includes('|') && !normalized.includes('\n') && normalized.includes(',')) {
        return normalized.split(',')
            .map(x => x.trim())
            .filter(Boolean)
            .map((label, index) => {
                const key = String.fromCharCode(65 + index);
                return {
                    key,
                    label,
                    color: '#00b4d8',
                    points: 0,
                    note: '',
                };
            });
    }

    const lines = normalized.split('\n').map(l => l.trim()).filter(Boolean);
    return lines.map((line, index) => {
        const [key, label, color, points, note] = line.split('|').map(p => (p || '').trim());
        const fallbackKey = String.fromCharCode(65 + index);
        return {
            key: (key || fallbackKey).toUpperCase(),
            label: label || key || `Option ${fallbackKey}`,
            color: color || '#00b4d8',
            points: Number.isFinite(parseInt(points, 10)) ? parseInt(points, 10) : 0,
            note: note || '',
        };
    });
}

async function closeVoteWithScore() {
    const d = await api('vote/close','POST');
    if (d.ok) {
        const msg = d.isTie
            ? `Vote fermé — Egalite (${(d.tiedKeys || []).join(', ')}) — aucun point attribue`
            : (d.awardedPoints > 0
                ? `Vote fermé — Vainqueur: ${d.winnerLabel || d.winner} — ${d.awardedPoints} pts attribues`
                : `Vote fermé — Vainqueur: ${d.winnerLabel || d.winner}`);
        showNotif(msg, 'success');
    } else {
        showNotif(d.error || 'Erreur', 'danger');
    }
}
function updateVoteTally(vote) {
    const el = document.getElementById('voteTally');
    if (!vote) { el.innerHTML = ''; return; }
    const tally = vote.tally || {};
    const total = Object.values(tally).reduce((a,b)=>a+b,0)||1;
    const colorMap = {};
    const labelMap = {};
    (vote.options || []).forEach(opt => {
        if (opt?.key) colorMap[opt.key] = opt.color || '#aaa';
        if (opt?.key) labelMap[opt.key] = opt.label || opt.key;
    });
    const rows = Object.entries(tally).map(([k,v]) => `
        <div class="mb-1">
            <div class="d-flex justify-content-between" style="font-size:.75rem">
                <span class="fw-bold" style="color:${colorMap[k]||'#aaa'}">${k} - ${labelMap[k] || k}</span>
                <span class="cs-mono">${v} vote${v>1?'s':''}</span>
            </div>
            <div class="tally-bar"><div class="tally-bar-fill" style="width:${Math.round(v/total*100)}%;background:${colorMap[k]||'#aaa'}"></div></div>
        </div>`).join('');
    el.innerHTML = `<div class="small text-theme fw-bold mb-2">📊 ${vote.question||'Vote en cours'}</div>${rows}`;
}

// ── INJECT ──────────────────────────────────────────────────
async function triggerInject(id) {
    if (!confirm('Déclencher cet inject ?')) return;
    await api(`inject/${id}`,'POST');
    showNotif('Inject déclenché','success');
}
function filterInjects() {
    const val = document.getElementById('injectTargetFilter').value;
    document.querySelectorAll('.inject-card').forEach(card => {
        const t = card.dataset.target;
        if (!val) { card.style.display=''; return; }
        if (val === '__global') { card.style.display = t ? 'none' : ''; return; }
        card.style.display = (t === val || t === '') ? '' : 'none';
    });
}

// ── ATMOSPHERE ──────────────────────────────────────────────
async function setAtmo(mode) {
    await api('atmosphere','POST',{mode});
    document.querySelectorAll('.atmo-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('atmo-'+mode)?.classList.add('active');
}

// ── DECISIONS FEED ──────────────────────────────────────────
const teamColorMap = {};
const teamNameMap  = {};
@foreach($teams as $t)
teamColorMap['{{ $t->type }}'] = '{{ $t->color }}';
teamNameMap['{{ $t->type }}']  = '{{ $t->name }}';
@endforeach

function updateDecisions(decisions) {
    if (!Array.isArray(decisions)) return;

    const area = document.getElementById('decisionsArea');
    const count = decisions.length;

    // Update tab badge
    document.getElementById('decCount').textContent = count;

    if (count === 0) return;

    // Check for new decisions since last poll
    const latest = decisions[0];
    if (latest.id <= lastDecId) return;

    // Rebuild the decisions area (keep last 20)
    lastDecId = latest.id;
    lastDecCount = count;

    if (area.querySelector('.text-white-50.text-center')) area.innerHTML = '';

    // Only prepend new ones
    decisions.filter(d => d.id > (lastDecId - 1)).forEach(d => {
        if (document.getElementById('dec-'+d.id)) return;
        const typeIcons = {decision:'🎯', escalade:'📡', communication:'📢', question:'❓'};
        const color = teamColorMap[d.teamType] || '#aaa';
        const div = document.createElement('div');
        div.id = 'dec-'+d.id;
        div.className = 'decision-review dec-new';
        const alreadyAwarded = d.scoreAwarded > 0;
        div.innerHTML = `
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="dr-team" style="background:${color}22;color:${color}">${d.teamName}</span>
                <span class="dr-type">${typeIcons[d.type]||'📋'} ${(d.type||'').toUpperCase()}</span>
                <span class="ms-auto small text-white-50" style="font-size:.7rem">${new Date(d.at).toLocaleTimeString('fr',{hour:'2-digit',minute:'2-digit'})}</span>
            </div>
            <div style="font-size:.83rem">${d.content}</div>
            <div class="d-flex gap-1 mt-2 align-items-center" ${alreadyAwarded ? 'style="opacity:.5"' : ''}>
                <span class="small text-white-50">Score:</span>
                <input type="number" id="award-${d.id}" value="${alreadyAwarded ? d.scoreAwarded : 10}" min="0" max="100" class="form-control form-control-sm" style="width:70px" ${alreadyAwarded?'disabled':''}>
                <button onclick="awardScore(${d.id})" class="btn btn-sm btn-success ld-award" ${alreadyAwarded?'disabled':''}>
                    ${alreadyAwarded ? '✓ '+d.scoreAwarded+' pts' : 'Attribuer'}
                </button>
            </div>`;
        area.insertBefore(div, area.firstChild);
    });
}

async function awardScore(id) {
    const pts = parseInt(document.getElementById('award-'+id).value);
    await api(`decision/${id}/award`,'POST',{points:pts});
    showNotif(`+${pts} pts attribués`,'success');
    // disable the button
    const btn = document.querySelector(`#dec-${id} button`);
    if (btn) { btn.textContent = '✓ '+pts+' pts'; btn.disabled = true; }
}

// ── BONUS BADGES ────────────────────────────────────────────
async function awardBadge(type) {
    const teamId = document.getElementById('badgeTeamSelect').value;
    if (!teamId) { showNotif('Sélectionnez une équipe d\'abord','danger'); return; }
    const d = await api(`badge/${teamId}`,'POST',{badge_type: type});
    if (d.ok) showNotif(`${d.badge} → +${d.points} pts`,'success');
    else showNotif('Erreur badge','danger');
}

function updateBadgeLog(badges) {
    if (!badges?.length) return;
    const newest = badges[0];
    if (newest.id <= lastBadgeId) return;
    lastBadgeId = newest.id;

    const log = document.getElementById('badgeLog');
    if (log.querySelector('.text-white-50')) log.innerHTML = '';

    badges.slice(0,8).forEach(b => {
        if (document.getElementById('badge-'+b.id)) return;
        const color = teamColorMap[b.teamType] || '#aaa';
        const imgOrIcon = b.image
            ? `<img src="${b.image}" alt="${b.label}" style="width:36px;height:36px;object-fit:contain;filter:drop-shadow(0 0 4px rgba(220,160,30,.5))">`
            : `<span style="font-size:1.3rem">${b.icon}</span>`;
        const div = document.createElement('div');
        div.id = 'badge-'+b.id;
        div.className = 'badge-log-entry';
        div.innerHTML = `
            ${imgOrIcon}
            <div class="flex-fill">
                <div style="font-size:.8rem;font-weight:700">${b.label}</div>
                <div style="font-size:.72rem;color:${color}">${b.teamName}</div>
            </div>
            <span class="cs-mono text-success" style="font-size:.78rem">+${b.points}</span>`;
        log.insertBefore(div, log.firstChild);
    });
}

// ── TABS ────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.mod-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show'));
    document.getElementById('tab-'+name).classList.add('active');
    document.getElementById('pane-'+name).classList.add('show');
}

// ── END ─────────────────────────────────────────────────────
async function confirmEnd() {
    if (!confirm('Terminer l\'exercice ? Cette action est irréversible.')) return;
    await api('end','POST');
    showNotif('Exercice terminé !','success');
}

// ── NOTIF ───────────────────────────────────────────────────
function showNotif(msg, type='success') {
    const colors = {success:'#22c55e',danger:'#ef4444',warn:'#f59e0b'};
    const div = document.createElement('div');
    div.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;padding:10px 18px;border-radius:8px;background:#0d1b2a;border:1px solid ${colors[type]||colors.success};color:#fff;font-size:.85rem;box-shadow:0 4px 20px rgba(0,0,0,.5);transition:opacity .4s;max-width:380px`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity='0'; setTimeout(()=>div.remove(),400); }, 4000);
}
</script>
@endpush
