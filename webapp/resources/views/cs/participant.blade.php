@extends('layouts.app')
@section('title', 'CARTHAGE SHIELD — ' . $session->name)

@push('head')
<style>
/* ── CS Participant Styles ────────────────────────────────── */
.cs-hero{background:linear-gradient(135deg,#030f1a 0%,#071a2e 60%,#0a1a1a 100%);position:relative;overflow:hidden}
.cs-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(0,180,216,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,180,216,.04) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.cs-title{font-family:'Space Mono',monospace;font-weight:700;font-size:1.6rem;letter-spacing:2px}
.timer-display{font-family:'Space Mono',monospace;font-size:2.8rem;font-weight:700;color:var(--bs-theme);text-shadow:0 0 20px rgba(var(--bs-theme-rgb),.4);transition:color .5s,text-shadow .5s;line-height:1}
.timer-display.warn{color:#f59e0b;text-shadow:0 0 20px rgba(245,158,11,.4)}
.timer-display.danger{color:#ef4444;text-shadow:0 0 20px rgba(239,68,68,.5);animation:tPulse .6s infinite}
@keyframes tPulse{0%,100%{opacity:1}50%{opacity:.3}}
.phase-dots{display:flex;gap:6px;align-items:center}
.pdot{width:28px;height:5px;border-radius:3px;background:rgba(255,255,255,.1);transition:all .4s}
.pdot.done{background:var(--bs-theme);opacity:.6}.pdot.active{background:var(--bs-theme);animation:pPulse 2s infinite}
@keyframes pPulse{0%,100%{box-shadow:0 0 0 rgba(var(--bs-theme-rgb),.5)}50%{box-shadow:0 0 8px rgba(var(--bs-theme-rgb),.8)}}

.team-btn{padding:10px 0;border-radius:8px;cursor:pointer;transition:all .2s;border:2px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);text-align:center;flex:1}
.team-btn:hover{border-color:rgba(255,255,255,.25);transform:translateY(-2px)}
.team-btn.selected{border-color:var(--team-color);background:rgba(var(--team-rgb),.12)}
.team-icon{font-size:1.6rem;display:block;margin-bottom:2px}
.team-nm{font-size:.8rem;font-weight:700;letter-spacing:.5px}
.team-sc{font-family:'Space Mono',monospace;font-size:.85rem;color:var(--bs-theme)}

.alert-toast{position:fixed;top:80px;right:24px;z-index:9999;max-width:360px;animation:toastIn .3s ease}
@keyframes toastIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}

.decision-type-btn{padding:8px 14px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);cursor:pointer;font-size:.82rem;transition:all .2s}
.decision-type-btn:hover{border-color:var(--bs-theme)}.decision-type-btn.active{border-color:var(--bs-theme);background:rgba(var(--bs-theme-rgb),.12);color:var(--bs-theme)}

.broadcast-item{padding:10px 12px;border-radius:6px;border-left:3px solid var(--bs-theme);background:rgba(255,255,255,.04);font-size:.85rem;margin-bottom:6px}
.broadcast-item.alert{border-color:#ef4444;background:rgba(239,68,68,.06)}
.broadcast-item.success{border-color:#22c55e}.broadcast-item.warn{border-color:#f59e0b}

/* PHANTOM MODAL overlay */
.phantom-modal{display:none;position:fixed;inset:0;z-index:1055;background:rgba(0,0,0,.9);align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:24px}
.phantom-modal.show{display:flex;animation:phIn .4s ease}
@keyframes phIn{from{opacity:0}to{opacity:1}}
.phantom-icon{font-size:5rem;margin-bottom:12px;animation:bob 2s infinite}
@keyframes bob{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
.phantom-label{font-family:'Space Mono',monospace;font-size:.75rem;letter-spacing:6px;color:rgba(239,68,68,.5);margin-bottom:8px}
.phantom-msg{font-family:'Space Mono',monospace;font-size:1.2rem;color:#ef4444;max-width:600px;line-height:1.6;text-shadow:0 0 20px rgba(239,68,68,.4)}

.online-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;margin-right:4px}
</style>
@endpush

@section('content')
<div class="container-fluid py-3" id="csParticipant">

{{-- PHANTOM Modal --}}
<div class="phantom-modal" id="phantomModal" onclick="closePhantom()">
    <div class="phantom-label">INTERCEPTED MESSAGE — {{ $scenario['attacker_name'] ?? 'THREAT' }}</div>
    <div class="phantom-icon">{{ $scenario['attacker_icon'] ?? '☠️' }}</div>
    <div class="phantom-msg" id="phantomMsgEl"></div>
    <p class="text-white-50 mt-4 small" style="letter-spacing:3px;font-family:'Space Mono',monospace">CLICK TO CLOSE</p>
</div>

{{-- TOAST container --}}
<div id="toastArea"></div>

{{-- Hero Header --}}
<div class="card mb-3 border-0 cs-hero">
    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
    <div class="card-body p-4" style="position:relative;z-index:1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="mb-0 cs-title"><span style="color:var(--bs-theme)">CARTHAGE</span> SHIELD</h1>
                <p class="text-white-50 mb-0 mt-1" style="font-size:.85rem">
                    {{ $session->name }}
                    &middot; <span class="badge bg-dark text-white-50 fw-normal" style="font-family:'Space Mono',monospace">{{ $session->code }}</span>
                </p>
            </div>
            <div class="text-end">
                <div class="timer-display" id="mainTimer">--:--</div>
                <div class="phase-dots mt-2 justify-content-end" id="phaseDots">
                    @foreach($scenario['phases'] as $p)
                    <div class="pdot" id="pdot-{{ $p['index'] }}"></div>
                    @endforeach
                </div>
                <div class="small text-white-50 mt-1" id="phaseLabel">{{ $scenario['phases'][0]['name'] ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row gx-3">

    {{-- LEFT: Join / Participant info + Decisions --}}
    <div class="col-lg-7 mb-3">

        @if(!$player)
        {{-- JOIN FORM --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-person-plus me-2 text-theme"></i>Join the Exercise</h5>
                <div class="mb-3">
                    <label class="form-label small text-white-50">Your Display Name</label>
                    <input class="form-control" id="displayName" value="{{ Auth::user()->name ?? '' }}" placeholder="Participant name" maxlength="80">
                </div>
                <div class="mb-3">
                    <label class="form-label small text-white-50 d-block mb-2">Choose Your Team</label>
                    <div class="d-flex flex-wrap gap-2" id="teamPicker">
                        @foreach($teams as $t)
                        <div class="team-btn" id="tp-{{ $t->id }}" style="--team-color:{{ $t->color }};--team-rgb:0,180,216" onclick="pickTeam('{{ $t->type }}',this)">
                            <span class="team-icon">{{ $t->icon }}</span>
                            <div class="team-nm">{{ $t->name }}</div>
                            <div class="small text-white-50" style="font-size:.7rem">{{ $t->role_label }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button onclick="joinSession()" class="btn btn-theme w-100 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>JOIN
                </button>
            </div>
        </div>
        @else
        {{-- PLAYER INFO --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:2.5rem">{{ $player->team->icon ?? '🛡️' }}</div>
                    <div>
                        <div class="fw-bold">{{ $player->display_name }}</div>
                        <div class="small text-white-50">{{ $player->team->name ?? '—' }} &middot; {{ $player->team->role_label ?? '' }}</div>
                    </div>
                    <div class="ms-auto text-end">
                        <span class="small"><span class="online-dot"></span><span class="text-white-50">Online</span></span>
                        <div class="small text-white-50 mt-1">
                            @if($player && !$player->team->is_scored)
                                Role: <strong class="text-warning">Mentor (non-scorable)</strong>
                            @else
                                Score: <strong class="text-theme" id="myScore">{{ $player->team->score ?? 0 }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-collection-play me-2 text-info"></i>National Operations Map</h5>
                <div id="phaseSituationMedia" class="small text-white-50">No visual content for the current phase.</div>
            </div>
        </div>

        <div class="card mb-3" id="quizCard" style="display:none!important">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-1"><i class="bi bi-patch-question me-2 text-info"></i>Quiz Question</h5>
                <p class="small text-white-50 mb-2" id="quizQuestion"></p>
                <div id="quizOptions" class="d-flex flex-wrap gap-2"></div>
                <div id="quizMeta" class="small text-white-50 mt-2"></div>
            </div>
        </div>

        {{-- DECISION SUBMISSION --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-send me-2 text-theme"></i>Submit an Action</h5>
                <div class="d-flex flex-wrap gap-2 mb-3" id="decisionTypes">
                    @foreach([['decision','Decision','danger'],['escalade','Escalation','warning'],['communication','Communication','theme'],['question','Question','info']] as [$v,$l,$c])
                    <button class="decision-type-btn {{ $loop->first ? 'active' : '' }}" onclick="setDType('{{ $v }}',this)">
                        {{ $l }}
                    </button>
                    @endforeach
                </div>
                <textarea class="form-control mb-3" id="decisionContent" rows="4" placeholder="Describe your decision, escalation, or question..."></textarea>
                <button onclick="submitDecision()" class="btn btn-theme fw-bold" id="submitDecBtn" @if(!$player) disabled @endif>
                    <i class="bi bi-send me-2"></i>Submit
                </button>
                @if(!$player)
                <span class="text-white-50 small ms-2">Join a team first</span>
                @endif
            </div>
        </div>

        {{-- VOTE --}}
        <div class="card" id="voteCard" style="display:none!important">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-1"><i class="bi bi-hand-thumbs-up me-2 text-warning"></i>Active Vote</h5>
                <p class="small text-white-50 mb-3" id="voteQuestion"></p>
                <div id="voteOptions" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>

    </div>

    {{-- RIGHT: Live feed + Teams --}}
    <div class="col-lg-5 mb-3">

        {{-- LIVE BROADCASTS --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-broadcast me-2 text-theme"></i>Communications</h5>
                <div id="broadcastFeed" style="max-height:220px;overflow-y:auto">
                    <div class="text-white-50 text-center py-3 small">Waiting for communications...</div>
                </div>
            </div>
        </div>

        {{-- TEAMS SCOREBOARD --}}
        <div class="card">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-bar-chart me-2 text-theme"></i>Scoreboard</h5>
                <div id="teamsScore">
                    @foreach($teams as $t)
                    <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)" id="tr-{{ $t->id }}">
                        <span style="font-size:1.3rem">{{ $t->icon }}</span>
                        <div class="flex-grow-1">
                            <div class="small fw-bold">{{ $t->name }}</div>
                            <div class="small text-white-50">{{ $t->role_label }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="font-family:'Space Mono',monospace;color:{{ $t->color }}" id="sc-{{ $t->id }}">{{ $t->is_scored ? $t->score : 'MENTOR' }}</div>
                            <div class="small text-white-50" id="online-{{ $t->id }}">
                                <span class="online-dot"></span>0
                            </div>
                        </div>
                    </div>
                    @endforeach
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
const PLAYER_ID = {{ $player?->id ?? 'null' }};
const TEAM_ID   = {{ $player?->cs_team_id ?? 'null' }};

let selectedTeam = null, decisionType = 'decision';
let lastBcId = 0, lastInjectId = 0, lastAtmo = '';
const teamStateById = {};
let currentBroadcasts = [];
let currentPhaseMessages = [];
let currentInjects = [];
let isPolledOnce = false;

// ── API helper ─────────────────────────────────────────────
async function api(path, method='GET', body=null) {
    const opts = { method, headers: {'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} };
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/cs/${CODE}/api/${path}`, opts);
    return r.json();
}

// ── JOIN ───────────────────────────────────────────────────
function pickTeam(type, el) {
    document.querySelectorAll('.team-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    selectedTeam = type;
}
async function joinSession() {
    const name = document.getElementById('displayName').value.trim();
    if (!name) { toast('warn','Enter your display name'); return; }
    if (!selectedTeam) { toast('warn','Choose a team'); return; }
    const d = await api('join','POST',{team_type:selectedTeam, display_name:name});
    if (d.success) location.reload();
    else toast('danger', d.message ?? 'Error');
}

// ── DECISION ───────────────────────────────────────────────
function setDType(type, el) {
    document.querySelectorAll('.decision-type-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    decisionType = type;
}
async function submitDecision() {
    if (!PLAYER_ID) return;
    const content = document.getElementById('decisionContent').value.trim();
    if (!content) { toast('warn','Write your decision first'); return; }

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const d = await api('decision','POST',{type:decisionType, content, player_id:PLAYER_ID});
    if (d.ok) { document.getElementById('decisionContent').value=''; toast('success','Decision submitted!'); }
    else toast('danger', d.error??'Error');
}

// ── VOTE ───────────────────────────────────────────────────
async function castVote(key) {
    if (!TEAM_ID) return;

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, vote',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await api('vote/submit','POST',{choice:key, team_id:TEAM_ID});
    if (res.ok) {
        toast('success','Vote cast');
    } else {
        toast('warn', res.error || 'Vote rejected');
    }
}

let currentQuizType = 'single_choice';
let multiChoiceSelection = [];
let orderSelection = [];
let currentQuizState = null;

function normalizeQuizType(type) {
    const v = String(type || '').trim().toLowerCase();
    if (['multi_choice', 'multi choice', 'multichoice', 'multiple_choice', 'multiple choice', 'multi_chice', 'multi chice', 'multi-choise'].includes(v)) return 'multi_choice';
    if (['short_answer', 'short answer', 'shortanswer', 'text', 'open'].includes(v)) return 'short_answer';
    if (['order', 'sort_order', 'sort order', 'ordering', 'rank', 'ranking'].includes(v)) return 'order';
    return 'single_choice';
}

async function castQuiz(key) {
    if (!TEAM_ID) return;

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await api('quiz/submit','POST',{choice:key, team_id:TEAM_ID});
    if (res.ok) toast('success','Quiz response submitted');
    else toast('warn', res.error || 'Quiz response rejected');
}

function toggleMultiChoice(key) {
    if (multiChoiceSelection.includes(key)) {
        multiChoiceSelection = multiChoiceSelection.filter(k => k !== key);
    } else {
        multiChoiceSelection.push(key);
    }
    redrawCurrentQuiz();
}

async function submitMultiChoice() {
    if (!TEAM_ID) return;
    if (multiChoiceSelection.length === 0) {
        toast('warn', 'Please select at least one response');
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await api('quiz/submit','POST',{choice: multiChoiceSelection, team_id:TEAM_ID});
    if (res.ok) {
        toast('success','Quiz responses submitted');
        multiChoiceSelection = [];
    }
    else toast('warn', res.error || 'Quiz responses rejected');
}

function toggleOrderChoice(key) {
    if (orderSelection.includes(key)) {
        orderSelection = orderSelection.filter(k => k !== key);
    } else {
        orderSelection.push(key);
    }
    redrawCurrentQuiz();
}

function resetOrderChoice() {
    orderSelection = [];
    redrawCurrentQuiz();
}

async function submitOrderChoice() {
    if (!TEAM_ID) return;
    if (orderSelection.length < 2) {
        toast('warn', 'Define an order with at least 2 choices');
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await api('quiz/submit', 'POST', {choice: orderSelection, team_id: TEAM_ID});
    if (res.ok) {
        toast('success', 'Quiz order submitted');
        orderSelection = [];
    } else {
        toast('warn', res.error || 'Quiz order rejected');
    }
}

async function submitShortAnswer() {
    if (!TEAM_ID) return;
    const field = document.getElementById('quizShortAnswer');
    if (!field) return;
    const text = field.value.trim();
    if (!text) {
        toast('warn', 'Please enter your response');
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        background: '#1a2a3a',
        color: '#fff'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await api('quiz/submit', 'POST', {answer_text: text, team_id: TEAM_ID});
    if (res.ok) toast('success', 'Text answer submitted');
    else toast('warn', res.error || 'Quiz response rejected');
}

function redrawCurrentQuiz() {
    if (currentQuizState) updateQuiz(currentQuizState);
}

// ── POLL ───────────────────────────────────────────────────
async function poll() {
    try {
        const d = await api('state');
        updateTimer(d.timer, d.session);
        updatePhase(d.session);
        updateTeams(d.teams);
        updateBroadcasts(d.broadcasts);
        updateInjects(d.injects);
        renderPhaseContent(d.phaseContent);
        updateVote(d.vote);
        updateQuiz(d.quiz);
        handleAtmo(d.session?.atmosphere);
        isPolledOnce = true;
    } catch(e) {}
}

function renderPhaseContent(content) {
    const mediaRoot = document.getElementById('phaseSituationMedia');
    if (!mediaRoot) return;
    const data = content || {};
    let media = Array.isArray(data.media) ? data.media : [];
    
    // Only show media if it's injected by moderator or if it's the national map
    media = media.filter(m => m && (m.isLive || (m.title && m.title.includes('Carte des Opérations'))));
    
    const messages = Array.isArray(data.messages) ? data.messages : [];
    currentPhaseMessages = messages;
    renderCommunications();

    const mediaHtml = media.map((m) => {
        if (m.type === 'video') {
            return `<div class="mb-2"><div class="fw-bold">${m.title || 'Video'}</div><video src="${m.url}" class="w-100 rounded mt-1" controls ${m.muted ? 'muted' : ''} ${m.loop ? 'loop' : ''}></video><div class="text-white-50 small">${m.caption || ''}</div></div>`;
        }
        if (m.type === 'animation') {
            return `<div class="mb-2"><div class="fw-bold">${m.title || 'Animation'}</div><img src="${m.url}" class="w-100 rounded mt-1" alt="${m.title || 'animation'}"><div class="text-white-50 small">${m.caption || ''}</div></div>`;
        }
        return `<div class="mb-2"><div class="fw-bold">${m.title || 'Image'}</div><img src="${m.url}" class="w-100 rounded mt-1" alt="${m.title || 'image'}"><div class="text-white-50 small">${m.caption || ''}</div></div>`;
    }).join('');
    mediaRoot.innerHTML = mediaHtml || '<div class="text-white-50">No visual content for the current phase.</div>';
}
setInterval(poll, 2000); poll();

if (PLAYER_ID) setInterval(() => api('heartbeat','POST'), 15000);

// ── TIMER ──────────────────────────────────────────────────
function updateTimer(timer, s) {
    let secs = 0;
    if (timer.isRunning && timer.endsAt) secs = Math.max(0, Math.round((new Date(timer.endsAt) - Date.now()) / 1000));
    else secs = timer.remainingSeconds ?? 0;
    const el = document.getElementById('mainTimer');
    el.textContent = `${String(Math.floor(secs/60)).padStart(2,'0')}:${String(secs%60).padStart(2,'0')}`;
    el.className = 'timer-display' + (secs<=60?' danger':secs<=180?' warn':'');
}

// ── PHASE ──────────────────────────────────────────────────
function updatePhase(s) {
    const idx = s.currentPhaseIndex ?? 0;
    document.getElementById('phaseLabel').textContent = s.currentPhase?.name ?? '—';
    for (let i=0;i<PHASES;i++) {
        const d = document.getElementById('pdot-'+i);
        if (!d) continue;
        d.className = 'pdot' + (i<idx?' done':i===idx?' active':'');
    }
}

// ── TEAMS ──────────────────────────────────────────────────
function updateTeams(teams) {
    if (!teams) return;
    teams.forEach(t => {
        teamStateById[t.id] = t;
        const sc = document.getElementById('sc-'+t.id);
        const ol = document.getElementById('online-'+t.id);
        if (sc) sc.textContent = t.isScored ? t.score : 'MENTOR';
        if (ol) ol.innerHTML = `<span class="online-dot"></span>${t.onlineCount}`;
        if (t.id == (TEAM_ID||0) && document.getElementById('myScore') && t.isScored) document.getElementById('myScore').textContent = t.score;
    });
}

// ── BROADCASTS ─────────────────────────────────────────────
function updateBroadcasts(bcs) {
    if (!Array.isArray(bcs)) return;
    const latest = bcs[0];
    if (latest && latest.id > lastBcId) {
        if (isPolledOnce) {
            if (latest.isPhantom) {
                showPhantom(latest.message);
            } else {
                Swal.fire({
                    title: 'New Broadcast Alert',
                    html: `<div style="font-size:1.1rem; line-height:1.5;">${latest.message}</div>`,
                    icon: 'info',
                    confirmButtonText: 'Acknowledge',
                    confirmButtonColor: 'var(--bs-theme)',
                    background: '#1a2a3a',
                    color: '#fff'
                });
            }
        }
        lastBcId = latest.id;
    }
    currentBroadcasts = bcs.filter(b => !b.isPhantom);
    renderCommunications();
}

function updateInjects(injects) {
    if (!Array.isArray(injects)) return;
    const latest = injects[0];
    if (latest && latest.id > lastInjectId) {
        if (isPolledOnce) {
            Swal.fire({
                title: 'CRITICAL INJECT RECEIVED',
                html: `<div style="font-size:1.05rem; line-height:1.5; color:#ef4444; font-family:'Space Mono',monospace;" class="mb-2"><strong>[${latest.tag}]</strong></div>
                       <div style="font-size:0.95rem; line-height:1.5; text-align:left;">${latest.content}</div>`,
                icon: 'warning',
                confirmButtonText: 'Acknowledge Threat',
                confirmButtonColor: '#ef4444',
                background: '#1a2a3a',
                color: '#fff'
            });
        }
        lastInjectId = latest.id;
    }
    currentInjects = injects;
    renderCommunications();
}

function renderCommunications() {
    const feed = document.getElementById('broadcastFeed');
    if (!feed) return;

    const phaseItems = currentPhaseMessages
        .filter(m => ['info', 'warn', 'warning', 'alert'].includes(String(m.type || 'info').toLowerCase()))
        .map(m => ({
            at: null,
            type: (m.type || 'info').toLowerCase() === 'warning' ? 'warn' : (m.type || 'info').toLowerCase(),
            message: m.content || '',
            source: 'Phase Intel',
        }));

    const mentorItems = currentBroadcasts.map(b => ({
        at: b.at || null,
        type: (b.type || 'info').toLowerCase(),
        message: b.message || '',
        source: 'Mentor Injector',
    }));

    const injectItems = currentInjects.map(inj => ({
        at: inj.at || null,
        type: 'warn',
        message: `<strong>[${inj.tag}]</strong> ${inj.content}`,
        source: 'Operational Inject',
    }));

    const merged = [...mentorItems, ...phaseItems, ...injectItems];
    merged.sort((a, b) => {
        if (!a.at && !b.at) return 0;
        if (!a.at) return 1;
        if (!b.at) return -1;
        return new Date(b.at) - new Date(a.at);
    });

    if (!merged.length) {
        feed.innerHTML = '<div class="text-white-50 text-center py-3 small">Waiting for communications...</div>';
        return;
    }

    feed.innerHTML = merged.slice(0, 30).map(item => {
        const when = item.at ? new Date(item.at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'}) : 'PHASE';
        const type = item.type || 'info';
        return `<div class="broadcast-item ${type}">
            <div class="small text-white-50 mb-1">${when} · ${item.source}</div>
            ${item.message}
        </div>`;
    }).join('');
}

// ── VOTE ───────────────────────────────────────────────────
function updateVote(vote) {
    const card = document.getElementById('voteCard');
    if (!vote) { card.style.setProperty('display','none','important'); return; }
    card.style.removeProperty('display');
    const teamInfo = teamStateById[TEAM_ID] || null;
    const canVote = !!(teamInfo && teamInfo.canVote);
    const isSecretOpen = !!(vote.isSecret && (vote.is_open ?? vote.isOpen));
    document.getElementById('voteQuestion').textContent = `${vote.question ?? ''}${isSecretOpen ? ' (secret vote)' : ''}`;
    const myChoice = vote.myChoice ?? null;
    const opts = document.getElementById('voteOptions');
    opts.innerHTML = (vote.options||[]).map(o => {
        const showPct = !isSecretOpen;
        const pct = showPct ? Math.round((vote.tally?.[o.key]||0) / Math.max(1, Object.values(vote.tally||{}).reduce((a,b)=>a+b,0)) * 100) : null;
        const selected = myChoice === o.key;
        const border = o.color || 'var(--bs-theme)';
        const disabled = !canVote ? 'disabled' : '';
        const pctBadge = showPct ? `<span class="badge bg-dark ms-1">${pct}%</span>` : '';
        return `<button onclick="castVote('${o.key}')" class="btn btn-sm ${selected ? 'btn-theme' : 'btn-outline-theme'}"
            ${disabled}
            style="min-width:80px;border-color:${border};color:${selected ? '#001018' : border}">
            ${o.label} ${pctBadge}
        </button>`;
    }).join('');
    if (!canVote) {
        opts.innerHTML += `<div class="small text-warning mt-2 w-100">Your team has a mentor role and does not vote.</div>`;
    }
}

function updateQuiz(quiz) {
    currentQuizState = quiz || null;
    const card = document.getElementById('quizCard');
    if (!card) return;
    if (!quiz || !quiz.isOpen) { card.style.setProperty('display','none','important'); return; }
    card.style.removeProperty('display');

    document.getElementById('quizQuestion').textContent = quiz.question ?? '';
    const myAnswer = quiz.myAnswer ?? null;
    const myAnswerText = quiz.myAnswerText ?? null;
    currentQuizType = normalizeQuizType(quiz.type);
    const opts = document.getElementById('quizOptions');
    
    const alreadyAnswered = (myAnswer !== null && myAnswer !== '') || (myAnswerText !== null && myAnswerText !== '');
    let html = '';

    if (currentQuizType === 'multi_choice') {
        const answeredKeys = alreadyAnswered ? myAnswer.split(',') : multiChoiceSelection;
        html = (quiz.options || []).map(o => {
            const selected = answeredKeys.includes(o.key);
            const border = o.color || '#60a5fa';
            return `<button onclick="toggleMultiChoice('${o.key}')" class="btn btn-sm ${selected ? 'btn-info text-dark' : 'btn-outline-info'}"
                style="min-width:110px;border-color:${border};color:${selected ? '#001018' : border}" ${alreadyAnswered ? 'disabled' : ''} id="mc-btn-${o.key}">
                ${o.key} · ${o.label}
            </button>`;
        }).join('');
        
        if (!alreadyAnswered) {
            html += `<div class="w-100 mt-2"><button onclick="submitMultiChoice()" class="btn btn-sm btn-primary w-100 fw-bold">Submit multiple choices</button></div>`;
        }
    } else if (currentQuizType === 'order') {
        const answeredOrder = alreadyAnswered ? myAnswer.split(',').filter(Boolean) : orderSelection;
        html = `<div class="small text-white-50 w-100 mb-2">Current order: ${answeredOrder.length ? answeredOrder.join(' > ') : '—'}</div>`;
        html += (quiz.options || []).map(o => {
            const selectedIdx = answeredOrder.indexOf(o.key);
            const selected = selectedIdx !== -1;
            const border = o.color || '#60a5fa';
            const orderBadge = selected ? `<span class="badge bg-dark ms-1">#${selectedIdx + 1}</span>` : '';
            return `<button onclick="toggleOrderChoice('${o.key}')" class="btn btn-sm ${selected ? 'btn-info text-dark' : 'btn-outline-info'}"
                style="min-width:120px;border-color:${border};color:${selected ? '#001018' : border}" ${alreadyAnswered ? 'disabled' : ''}>
                ${o.key} · ${o.label} ${orderBadge}
            </button>`;
        }).join('');
        if (!alreadyAnswered) {
            html += `<div class="w-100 mt-2 d-flex gap-2">
                <button onclick="submitOrderChoice()" class="btn btn-sm btn-primary flex-fill fw-bold">Submit order</button>
                <button onclick="resetOrderChoice()" class="btn btn-sm btn-outline-light">Reset</button>
            </div>`;
        }
    } else if (currentQuizType === 'short_answer') {
        const safeText = (alreadyAnswered ? (myAnswerText || '') : '');
        html = `<textarea id="quizShortAnswer" class="form-control form-control-sm mb-2" rows="3" placeholder="Enter your response..." ${alreadyAnswered ? 'disabled' : ''}>${safeText}</textarea>`;
        if (!alreadyAnswered) {
            html += `<button onclick="submitShortAnswer()" class="btn btn-sm btn-primary w-100 fw-bold">Submit text answer</button>`;
        }
    } else {
        html = (quiz.options || []).map(o => {
            const selected = myAnswer === o.key;
            const border = o.color || '#60a5fa';
            return `<button onclick="castQuiz('${o.key}')" class="btn btn-sm ${selected ? 'btn-info text-dark' : 'btn-outline-info'}"
                style="min-width:110px;border-color:${border};color:${selected ? '#001018' : border}" ${alreadyAnswered ? 'disabled' : ''}>
                ${o.key} · ${o.label}
            </button>`;
        }).join('');
    }

    opts.innerHTML = html;
    const prompt = (quiz.prompt || '').trim();
    document.getElementById('quizMeta').textContent = `Type: ${currentQuizType.replace('_',' ')} · Answers received: ${quiz.answerCount || 0}${prompt ? ' · ' + prompt : ''}`;
}

// ── ATMOSPHERE ─────────────────────────────────────────────
function handleAtmo(atmo) {
    if (atmo === lastAtmo) return;
    lastAtmo = atmo;
    if (atmo === 'victory') toast('success', '🏆 Exercise complete — Results incoming...');
    if (atmo === 'crisis') toast('danger', '🚨 STATE OF CRISIS DECLARED');
}

// ── PHANTOM ────────────────────────────────────────────────
function showPhantom(msg) {
    document.getElementById('phantomMsgEl').textContent = msg;
    document.getElementById('phantomModal').classList.add('show');
    setTimeout(closePhantom, 12000);
}
function closePhantom() { document.getElementById('phantomModal').classList.remove('show'); }

// ── TOAST ──────────────────────────────────────────────────
function toast(type, msg) {
    const colors = {success:'#22c55e',danger:'#ef4444',warn:'#f59e0b',info:'var(--bs-theme)'};
    const div = document.createElement('div');
    div.className = 'alert-toast';
    div.style.cssText = `position:fixed;top:80px;right:24px;z-index:9998;max-width:340px;padding:12px 16px;border-radius:8px;background:#1a2a3a;border:1px solid ${colors[type]||'rgba(255,255,255,.1)'};color:#fff;font-size:.85rem;box-shadow:0 4px 20px rgba(0,0,0,.5)`;
    div.innerHTML = `<i class="bi bi-${type==='success'?'check-circle':type==='danger'?'exclamation-triangle':type==='warn'?'exclamation':'info-circle'} me-2" style="color:${colors[type]}"></i>${msg}`;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}
</script>
@endpush
