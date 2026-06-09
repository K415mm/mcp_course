<!DOCTYPE html>
@php
    $isEn = true;
@endphp
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>NEPTUNE STRIKE — Moderator Console</title>
{{-- HUD theme assets --}}
<link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
<link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
/* ── Neptune Strike Standalone Page ── */
body { padding-top: 0 !important; }
.sidebar, nav.app-sidebar, .page-header, footer { display: none !important; }
.page-wrapper { margin-left: 0 !important; padding: 0 !important; }
.content-wrapper { margin: 0 !important; }
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
.decision-toolbar{position:sticky;top:0;z-index:2;background:rgba(13,27,42,.92);backdrop-filter:blur(2px);padding:8px;border:1px solid rgba(255,255,255,.08);border-radius:8px;margin-bottom:8px}
.decision-team-item{border:1px solid rgba(255,255,255,.08);border-radius:8px;overflow:hidden;margin-bottom:8px;background:rgba(255,255,255,.03)}
.decision-team-header{padding:0;background:transparent}
.decision-team-btn{width:100%;text-align:left;background:rgba(255,255,255,.03);color:#fff;border:0;padding:10px 12px;font-size:.8rem;display:flex;align-items:center;gap:8px}
.decision-team-btn:not(.collapsed){background:rgba(var(--bs-theme-rgb),.12)}
.decision-team-body{padding:10px 10px 2px 10px}
.decision-meta{font-size:.72rem;color:rgba(255,255,255,.58)}
.quiz-answer-chip{display:inline-block;padding:2px 7px;border-radius:10px;font-size:.7rem;font-family:'Space Mono',monospace;background:rgba(var(--bs-theme-rgb),.18);color:var(--bs-theme)}

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
.user-admin-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:10px;margin-bottom:10px}
.user-roster-row{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:10px;margin-bottom:8px}
.user-roster-row.banned{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.06)}
.user-mini-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:999px;font-size:.68rem;font-family:'Space Mono',monospace;border:1px solid rgba(255,255,255,.12)}
.user-meta-line{font-size:.72rem;color:rgba(255,255,255,.55)}
.user-roster-actions{display:grid;grid-template-columns:minmax(0,1fr) 150px auto auto auto;gap:8px;align-items:center}
@media (max-width: 1200px){.user-roster-actions{grid-template-columns:1fr 1fr}.user-roster-actions .btn{width:100%}}
/* ── Neptune Strike Theme Overrides ───────────────────────── */
body {
    --bs-theme: #00ffcc !important;
    --bs-theme-rgb: 0, 255, 204 !important;
}
.text-theme {
    color: #00ffcc !important;
}
.btn-outline-theme {
    border-color: #00ffcc !important;
    color: #00ffcc !important;
}
.btn-outline-theme:hover {
    background: #00ffcc !important;
    color: #000000 !important;
}
.btn-theme {
    background: rgba(0, 255, 204, 0.15) !important;
    border-color: #00ffcc !important;
    color: #00ffcc !important;
}
.btn-theme:hover {
    background: #00ffcc !important;
    color: #000000 !important;
}
</style>
</head>
<body>
<div class="container-fluid py-3" id="csMod">
<div class="row gx-3">

    {{-- ══ TOP BAR ══ --}}
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <div>
                        <div class="cs-title-sm text-theme">🛡️ MODERATOR CONSOLE</div>
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
                        <span class="small text-white-50">Atmosphere:</span>
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
                    <span class="badge bg-dark ms-1 small" id="onlineCount">0 online</span>
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
                            @if(!empty($t->role_label))
                            <div class="small text-white-50" style="font-size:.72rem">{{ $t->role_label }}</div>
                            @endif
                        </div>
                        <div class="ms-auto text-end">
                            <div class="score-val" id="msc-{{ $t->id }}">{{ $t->is_scored ? $t->score : 'MENTOR' }}</div>
                            <div class="small text-white-50" id="mon-{{ $t->id }}">
                                <i class="bi bi-person-fill text-success"></i> <span id="monv-{{ $t->id }}">0</span>
                                <span class="mx-1">·</span>
                                <i class="bi bi-people-fill"></i> <span id="mpc-{{ $t->id }}">0</span>
                            </div>
                        </div>
                    </div>
                    {{-- Score buttons --}}
                    <div class="d-flex gap-1 mb-2">
                        @foreach([['-20','danger'],['-10','secondary'],['+5','secondary'],['+10','theme'],['+20','theme'],['+25','success']] as [$d,$c])
                        <button onclick="adjustScore({{ $t->id }},{{$d}})" class="btn btn-sm btn-{{ $c }} fw-bold flex-fill" style="padding:3px 0;font-size:.75rem" @if(!$t->is_scored) disabled @endif>{{ $d }}</button>
                        @endforeach
                    </div>
                    {{-- Badge status & bonus badges --}}
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-white-50">Badge: <span id="mbadge-{{ $t->id }}">{{ $t->badge_icon ?? '🛡️' }}</span></span>
                        @if(!$t->badge_eligible)
                        <span class="badge bg-dark text-warning">Mentor</span>
                        @else
                        <div class="ms-auto d-flex gap-1 flex-wrap" id="bonusBadges-{{ $t->id }}"></div>
                        @endif
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
                        <h6 class="card-title mb-2"><i class="bi bi-broadcast me-2 text-theme"></i>Announcement</h6>
                        <select class="form-select form-select-sm mb-2" id="bcType">
                            <option value="info">Info</option>
                            <option value="warn">Warning</option>
                            <option value="alert">Alert</option>
                            <option value="success">Success</option>
                        </select>
                        <textarea class="form-control form-control-sm mb-2" id="bcMsg" rows="3" placeholder="Message..."></textarea>
                        <div class="d-flex gap-2">
                            <button onclick="broadcast()" class="btn btn-sm btn-theme flex-fill fw-bold">Broadcast</button>
                            <button onclick="phantom()" class="btn btn-sm btn-danger fw-bold" title="Message PHANTOM">☠️</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="card h-100">
                    <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    <div class="card-body">
                        <h6 class="card-title mb-2"><i class="bi bi-hand-thumbs-up me-2 text-warning"></i>Strategic Vote</h6>
                        <input class="form-control form-control-sm mb-2" id="voteQ" placeholder="Question...">
                        <textarea class="form-control form-control-sm mb-2 cs-mono" id="vOpt"
                                  placeholder="Manual options (one line = A|Label|#00b4d8|20|Note)"
                                  rows="3"></textarea>
                        <div id="preparedVoteInfo" class="bank-note mb-2">No preloaded question from library.</div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="voteSecretSwitch">
                            <label class="form-check-label small text-white-50" for="voteSecretSwitch">Secret Vote</label>
                        </div>
                        <button onclick="openVote()" class="btn btn-sm btn-warning text-dark fw-bold w-100 mb-2">Open</button>
                        <button onclick="closeVoteWithScore()" class="btn btn-sm btn-danger fw-bold w-100">Close & Score</button>
                        <div class="mt-2" id="voteTally"></div>
                        <hr class="my-3" style="border-color:rgba(255,255,255,.1)">
                        <h6 class="card-title mb-2"><i class="bi bi-patch-question me-2 text-info"></i>Quiz Question (Evaluation)</h6>
                        <input class="form-control form-control-sm mb-2" id="quizQ" placeholder="Quiz question...">
                        <select class="form-select form-select-sm mb-2" id="quizType">
                            <option value="single_choice">Single choice</option>
                            <option value="multi_choice">Multi choice</option>
                            <option value="order">Sort order</option>
                            <option value="short_answer">Short response</option>
                        </select>
                        <textarea class="form-control form-control-sm mb-2 cs-mono" id="quizOpt"
                                  placeholder="Choices required (one line = A|Label|#00b4d8|20)"
                                  rows="3"></textarea>
                        <input class="form-control form-control-sm mb-2 cs-mono" id="quizCorrect" placeholder="Correct answer(s): e.g. A or A,C">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text">Base points</span>
                            <input type="number" min="0" max="100" value="10" class="form-control" id="quizBasePoints">
                        </div>
                        <button onclick="openQuiz()" class="btn btn-sm btn-info text-dark fw-bold w-100 mb-2">Open Quiz</button>
                        <button onclick="closeQuizWithScore()" class="btn btn-sm btn-danger fw-bold w-100">Close Quiz & Score</button>
                        <div class="mt-2" id="quizTally"></div>
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
                <h6 class="card-title mb-2"><i class="bi bi-journal-text me-2 text-info"></i>Scenario Library</h6>
                <div class="d-flex gap-2 mb-2">
                    <select id="bankPhaseSelect" class="form-select form-select-sm" onchange="loadBankForPhase(this.value)">
                        @foreach($scenario['phases'] as $p)
                            <option value="{{ $p['index'] }}">Phase {{ $p['index'] }} - {{ $p['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-theme" onclick="refreshBank()">Refresh</button>
                </div>
                <div class="bank-block mb-2">
                    <div class="small fw-bold text-theme mb-2">Messages</div>
                    <div id="bankMessages" class="small text-white-50">Loading...</div>
                </div>
                <div class="bank-block mb-2">
                    <div class="small fw-bold text-theme mb-2">Phase Media</div>
                    <div id="bankMedia" class="small text-white-50">Loading...</div>
                </div>
                <div class="bank-block mb-2">
                    <div class="small fw-bold text-theme mb-2">Live Media Control</div>
                    <div class="row g-2">
                        <div class="col-4">
                            <select id="mediaType" class="form-select form-select-sm">
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="animation">Animation</option>
                            </select>
                        </div>
                        <div class="col-8">
                            <input id="mediaTitle" class="form-control form-control-sm" placeholder="Media title">
                        </div>
                        <div class="col-12">
                            <input id="mediaUrl" class="form-control form-control-sm" placeholder="Media URL (https://...)">
                        </div>
                        <div class="col-12">
                            <textarea id="mediaCaption" class="form-control form-control-sm" rows="2" placeholder="Description / Caption"></textarea>
                        </div>
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <label class="form-check form-check-inline small">
                                <input class="form-check-input" type="checkbox" id="mediaAutoplay">
                                <span class="form-check-label">Autoplay</span>
                            </label>
                            <label class="form-check form-check-inline small">
                                <input class="form-check-input" type="checkbox" id="mediaLoop">
                                <span class="form-check-label">Loop</span>
                            </label>
                            <label class="form-check form-check-inline small">
                                <input class="form-check-input" type="checkbox" id="mediaMuted" checked>
                                <span class="form-check-label">Muted</span>
                            </label>
                        </div>
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-theme" onclick="saveMediaToPhase()">Add to phase</button>
                            <button class="btn btn-sm btn-theme" onclick="saveMediaToLive()">Inject live</button>
                        </div>
                        <div class="col-12">
                            <input type="file" id="mediaFile" class="form-control form-control-sm" accept="image/*,video/*,.gif,.webp">
                        </div>
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-info" onclick="uploadMediaToPhase()">Upload to phase</button>
                            <button class="btn btn-sm btn-info text-dark" onclick="uploadMediaToLive()">Upload & Inject live</button>
                        </div>
                    </div>
                </div>
                <div class="bank-block">
                    <div class="small fw-bold text-theme mb-2">Questions / Quiz</div>
                    <div id="bankQuestions" class="small text-white-50">Loading...</div>
                </div>
            </div>
        </div>

        {{-- Decision Matrix (active phase) --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body p-0">
                <div id="matrixPanel">
                    <div class="text-white-50 text-center py-4 small">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Select a phase to view the matrix
                    </div>
                </div>
            </div>
        </div>

        {{-- Bonus Badges Award --}}
        <div class="card mb-3">
            <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="bi bi-award me-2 text-warning"></i>Bonus Badges (+5 pts)</h6>
                <div class="mb-2">
                    <select class="form-select form-select-sm mb-2" id="badgeTeamSelect">
                        <option value="">— Team —</option>
                        @foreach($teams as $t)
                        @if($t->badge_eligible)
                        <option value="{{ $t->id }}" data-color="{{ $t->color }}">{{ $t->icon }} {{ $t->name }}</option>
                        @endif
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
                    <div class="text-white-50 text-center small py-2">No badges awarded</div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ RIGHT COLUMN: Injects + Decisions ══ --}}
    <div class="col-lg-3 mb-3">

        {{-- Tabs --}}
        <div class="d-flex gap-1 mb-2">
            <button class="mod-tab active" id="tab-injects" onclick="switchTab('injects')"><i class="bi bi-lightning me-1"></i>Injects <span id="injectCount" class="badge bg-dark ms-1">{{ $injects->count() }}</span></button>
            <button class="mod-tab" id="tab-decisions" onclick="switchTab('decisions')"><i class="bi bi-clipboard-check me-1"></i>Decisions <span id="decCount" class="badge bg-dark ms-1">0</span></button>
        </div>

        <div class="mb-3">
            <a href="{{ route('neptune.managePlayers', $session->code) }}" class="btn btn-outline-info w-100 mb-2" target="_blank">
                <i class="bi bi-people-fill me-2"></i>Player Management <span id="usersCount" class="badge bg-dark ms-1">0</span>
            </a>
            <a href="{{ route('neptune.show', $session->code) }}" class="btn btn-outline-theme w-100 mb-2" target="_blank">
                <i class="bi bi-person-workspace me-2"></i>Team View
            </a>
            <a href="{{ route('neptune.dashboard', $session->code) }}" class="btn btn-outline-secondary w-100 mb-2" target="_blank">
                <i class="bi bi-display me-2"></i>Grand Screen
            </a>
            <button onclick="copyJoinLink()" class="btn btn-outline-warning w-100">
                <i class="bi bi-clipboard me-2"></i>Copy Join Link
            </button>
        </div>

        {{-- Injects Tab --}}
        <div class="tab-pane show" id="pane-injects">
            <div class="card">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body p-2">
                    {{-- Auto Injects Control --}}
                    <div class="p-2 mb-2 rounded" style="background:rgba(255,255,255,.02); border: 1px solid rgba(255,255,255,.1);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-check-label small fw-bold text-white" for="autoInjectCheckbox">
                                <i class="bi bi-robot me-1 text-theme"></i>Auto Injects
                            </label>
                            <div class="form-check form-switch p-0 m-0">
                                <input class="form-check-input" type="checkbox" id="autoInjectCheckbox" onchange="saveAutoSettings()" checked style="margin-left:0; cursor:pointer">
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="small text-white-50" style="font-size:0.75rem">Interval (s):</span>
                            <input type="number" id="autoInjectInterval" class="form-control form-control-sm py-0 px-2" style="width:70px; height:22px; font-size:0.75rem" value="120" min="10" max="3600" onchange="saveAutoSettings()">
                        </div>
                    </div>
                    {{-- Inject target filter --}}
                    <div class="mb-2">
                        <select class="form-select form-select-sm" id="injectTargetFilter" onchange="filterInjects()">
                            <option value="">All Injects</option>
                            @foreach($teams as $t)
                            <option value="{{ $t->type }}">{{ $t->icon }} {{ $t->name }}</option>
                            @endforeach
                            <option value="__global">🌐 Global Only</option>
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
                        <div class="text-white-50 text-center py-3 small">No injects available</div>
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
                    <div class="decision-toolbar">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <span id="decSummaryTeams" class="badge bg-dark">0 teams</span>
                            <span id="decSummaryAnswers" class="badge bg-info text-dark">0 quiz answers</span>
                            <span id="decSummaryTotal" class="badge bg-secondary">0 items</span>
                        </div>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="decTypeFilter" onchange="renderDecisionsPanel()">
                                <option value="all">All</option>
                                <option value="question">Quiz Only</option>
                                <option value="decision">Strategic Decisions</option>
                                <option value="communication">Communications</option>
                                <option value="escalade">Escalations</option>
                            </select>
                            <select class="form-select form-select-sm" id="decTeamFilter" onchange="renderDecisionsPanel()">
                                <option value="all">All Teams</option>
                            </select>
                        </div>
                    </div>
                    <div style="max-height:calc(100vh - 240px);overflow-y:auto" id="decisionsArea">
                        <div class="text-white-50 text-center py-3 small">Awaiting decisions...</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
</div>

<script src="{{ asset('hud/js/vendor.min.js') }}"></script>
<!-- SweetAlert2 for modern alerts and confirms -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.swalConfirm = function(message, callback) {
        return Swal.fire({
            title: '<span style="font-family:\'Orbitron\',sans-serif;font-size:1.6rem;color:#00ffcc;font-weight:700">Confirm Action</span>',
            html: `<div style="font-family:\'Space Mono\',monospace;font-size:1.3rem;color:#fff;margin-top:15px">${message}</div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            background: '#0a1a2e',
            color: '#fff',
            width: '650px'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
            return result.isConfirmed;
        });
    };
</script>
<script>
const CODE  = '{{ $session->code }}';
const CSRF  = '{{ csrf_token() }}';
const PHASES = {{ count($scenario['phases']) }};
const SCENARIO_KEY = '{{ $scenario['key'] }}';
const IS_EN = true;
document.body.classList.add('scenario-' + SCENARIO_KEY);

// Pre-load the scenario phases for the matrix (passed from server)
const SCENARIO_PHASES = @json($scenario['phases']);
const INITIAL_BANK_BY_PHASE = @json($initialBankByPhase ?? []);


let lastDecId = 0, lastBadgeId = 0, lastDecCount = 0;
let currentPhaseIndex = null;
let currentBank = { messages: [], questions: [], media: [] };
let decisionsCache = [];
let decisionsSignature = '';
const pendingAwardEdits = {};
let latestSessionState = null;
let playersRosterCache = [];
let assignableUsersCache = [];
let injectCatalogCache = [];

async function api(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/neptune/${CODE}/api/${path}`, opts);
    if (!r.ok && r.status === 419) { location.reload(); return null; }
    try { return await r.json(); } catch(e) { console.error('API parse error', r.status, path); return null; }
}

async function apiChecked(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/neptune/${CODE}/api/${path}`, opts);
    let data = {};
    try {
        data = await r.json();
    } catch (e) {
        if (r.status === 419) { location.reload(); return null; }
        throw new Error('Invalid server response.');
    }
    if (!r.ok || data?.ok === false || data?.success === false) {
        const validationMessage = data?.errors ? Object.values(data.errors).flat()[0] : null;
        throw new Error(data?.error || data?.message || validationMessage || 'Operation denied.');
    }
    return data;
}

async function apiUpload(path, formData) {
    const r = await fetch(`/neptune/${CODE}/api/${path}`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        body: formData
    });
    try { return await r.json(); } catch(e) { console.error('Upload parse error', r.status); return null; }
}

// ── POLL ───────────────────────────────────────────────────
async function poll() {
    try {
        const d = await api('state');
        injectCatalogCache = d.injectCatalog ?? [];
        updateTimer(d.timer);
        updatePhase(d.session);
        updateTeams(d.teams);
        updateVoteTally(d.vote);
        updateQuizTally(d.quiz);
        updateDecisions(d.decisions ?? []);
        updateBadgeLog(d.badges ?? []);
        updateMatrix(d.decisionMatrix);
        updateOnlineCount(d.onlinePlayers ?? []);
        updatePlayersRoster(d.playersRoster ?? [], d.assignableUsers ?? []);
    } catch(e) { console.warn('Poll error', e); }
}
setInterval(poll, 5000); poll();

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
let settingsSynced = false;
function syncSettings(settings) {
    if (settingsSynced || !settings) return;
    const autoEnabled = settings.auto_inject_enabled !== false;
    const autoInterval = settings.auto_inject_interval || 120;
    
    const checkbox = document.getElementById('autoInjectCheckbox');
    const intervalInput = document.getElementById('autoInjectInterval');
    if (checkbox) checkbox.checked = autoEnabled;
    if (intervalInput) intervalInput.value = autoInterval;
    
    settingsSynced = true;
}

async function saveAutoSettings() {
    const checkbox = document.getElementById('autoInjectCheckbox');
    const intervalInput = document.getElementById('autoInjectInterval');
    const enabled = checkbox ? checkbox.checked : true;
    const interval = intervalInput ? parseInt(intervalInput.value, 10) : 120;

    const res = await api('settings', 'POST', {
        auto_inject_enabled: enabled,
        auto_inject_interval: interval
    });
    if (res?.ok) {
        showNotif('Auto-inject settings saved', 'success');
    } else {
        showNotif(res?.error || 'Failed to save settings', 'danger');
    }
}

function updatePhase(s) {
    latestSessionState = s || null;
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

    if (s.settings) {
        syncSettings(s.settings);
    }

    renderAssignmentControls();
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
                media: data.media ?? [],
            };
        } else {
            throw new Error('bank endpoint not ok');
        }
    } catch (e) {
        // Fallback server-rendered payload, avoids hard failure if API bank is blocked in prod.
        currentBank = INITIAL_BANK_BY_PHASE?.[idx] ?? { messages: [], questions: [], media: [] };
        showNotif('Library loaded via local fallback', 'warn');
    }

    renderBankMessages();
    renderBankMedia();
    renderBankQuestions();
}

function renderBankMessages() {
    const root = document.getElementById('bankMessages');
    if (!root) return;
    if (!currentBank.messages.length) {
        root.innerHTML = '<div class="text-white-50">No messages available for this phase.</div>';
        return;
    }

    root.innerHTML = currentBank.messages.map((m, idx) => `
        <div class="bank-item">
            <p>${m.content || ''}</p>
            <div class="mt-2 d-flex justify-content-between align-items-center">
                <span class="badge bg-dark">${(m.type || 'info').toUpperCase()}${m.stage ? ` · ${m.stage}` : ''}</span>
                <button class="btn btn-sm btn-outline-theme" onclick="sendBankMessage(${idx})">Send to live feed</button>
            </div>
        </div>
    `).join('');
}

function renderBankMedia() {
    const root = document.getElementById('bankMedia');
    if (!root) return;
    if (!currentBank.media?.length) {
        root.innerHTML = '<div class="text-white-50">No media available for this phase.</div>';
        return;
    }

    root.innerHTML = currentBank.media.map((m, idx) => `
        <div class="bank-item">
            <p class="fw-bold mb-1">${m.title || m.id || 'Media'} ${m.isLive ? '<span class="badge bg-danger ms-1">LIVE</span>' : ''}</p>
            <div class="bank-note">${(m.type || 'image').toUpperCase()}${m.stage ? ` · ${m.stage}` : ''}${m.scope ? ` · ${String(m.scope).toUpperCase()}` : ''}</div>
            <div class="small text-white-50 mt-1">${m.caption || ''}</div>
            <div class="small mt-1"><a href="${m.url || '#'}" target="_blank" rel="noopener">Open media</a></div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-theme" onclick="pushMediaLive(${idx})">Inject live</button>
                ${m.isEditable ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteMedia(${idx})">Delete</button>` : ''}
            </div>
        </div>
    `).join('');
}

function mediaPayloadFromForm() {
    return {
        type: document.getElementById('mediaType').value || 'image',
        title: (document.getElementById('mediaTitle').value || '').trim(),
        caption: (document.getElementById('mediaCaption').value || '').trim(),
        url: (document.getElementById('mediaUrl').value || '').trim(),
        autoplay: document.getElementById('mediaAutoplay').checked,
        loop: document.getElementById('mediaLoop').checked,
        muted: document.getElementById('mediaMuted').checked,
    };
}

async function saveMediaToPhase() {
    const payload = mediaPayloadFromForm();
    if (!payload.url) {
        showNotif('Media URL required', 'warn');
        return;
    }
    const phaseIndex = parseInt(document.getElementById('bankPhaseSelect')?.value || currentPhaseIndex || 0, 10) || 0;
    await api('media/save', 'POST', {
        scope: 'phase',
        phase_index: phaseIndex,
        ...payload
    });
    showNotif('Media added to phase', 'success');
    await refreshBank();
}

async function saveMediaToLive() {
    const payload = mediaPayloadFromForm();
    if (!payload.url) {
        showNotif('Media URL required', 'warn');
        return;
    }
    await api('media/save', 'POST', { scope: 'live', ...payload, context: 'manual_live' });
    showNotif('Media injected live', 'success');
    await refreshBank();
}

async function uploadMediaToPhase() {
    const input = document.getElementById('mediaFile');
    const file = input?.files?.[0];
    if (!file) {
        showNotif('Select a media file', 'warn');
        return;
    }
    const payload = mediaPayloadFromForm();
    const fd = new FormData();
    fd.append('file', file);
    fd.append('scope', 'phase');
    fd.append('phase_index', String(parseInt(document.getElementById('bankPhaseSelect')?.value || currentPhaseIndex || 0, 10) || 0));
    fd.append('type', payload.type);
    fd.append('title', payload.title || file.name);
    fd.append('caption', payload.caption || '');
    fd.append('autoplay', payload.autoplay ? '1' : '0');
    fd.append('loop', payload.loop ? '1' : '0');
    fd.append('muted', payload.muted ? '1' : '0');
    const res = await apiUpload('media/upload', fd);
    if (!res?.ok) {
        showNotif(res?.error || 'Media upload failed', 'warn');
        return;
    }
    input.value = '';
    showNotif('Media upload completed', 'success');
    await refreshBank();
}

async function uploadMediaToLive() {
    const input = document.getElementById('mediaFile');
    const file = input?.files?.[0];
    if (!file) {
        showNotif('Select a media file', 'warn');
        return;
    }
    const payload = mediaPayloadFromForm();
    const fd = new FormData();
    fd.append('file', file);
    fd.append('scope', 'live');
    fd.append('type', payload.type);
    fd.append('title', payload.title || file.name);
    fd.append('caption', payload.caption || '');
    fd.append('autoplay', payload.autoplay ? '1' : '0');
    fd.append('loop', payload.loop ? '1' : '0');
    fd.append('muted', payload.muted ? '1' : '0');
    const res = await apiUpload('media/upload', fd);
    if (!res?.ok) {
        showNotif(res?.error || 'Media upload failed', 'warn');
        return;
    }
    input.value = '';
    showNotif('Media upload completed', 'success');
    await refreshBank();
}

async function pushMediaLive(index) {
    const media = currentBank.media?.[index];
    if (!media?.url) return;
    await api('media/inject', 'POST', { media, context: 'bank_push' });
    showNotif('Media broadcasted to dashboards', 'success');
    await refreshBank();
}

async function deleteMedia(index) {
    const media = currentBank.media?.[index];
    if (!media?.id || !media?.scope || media.scope === 'bank') {
        showNotif('This media cannot be deleted', 'warn');
        return;
    }
    if (!await swalConfirm('Delete this media?')) return;
    await api('media/delete', 'POST', {
        scope: media.scope,
        id: media.id,
        phase_index: parseInt(document.getElementById('bankPhaseSelect')?.value || currentPhaseIndex || 0, 10) || 0
    });
    showNotif('Media deleted', 'success');
    await refreshBank();
}

function renderBankQuestions() {
    const root = document.getElementById('bankQuestions');
    if (!root) return;
    if (!currentBank.questions.length) {
        root.innerHTML = `<div class="text-white-50">No questions available for this phase.</div>`;
        return;
    }

    root.innerHTML = currentBank.questions.map((q, idx) => `
        <div class="bank-item">
            <p class="fw-bold">${idx + 1}. ${q.question || 'Question sans titre'}</p>
            <div class="bank-note mt-1">Type: ${normalizeQuizType(q.type).toUpperCase()}${q.points ? ` · ${q.points} pts` : ''}${q.time_limit ? ` · ${q.time_limit}s` : ''}</div>
            <div class="bank-note mt-1">${(q.options || []).map(o => `${o.key}: ${o.label} (${o.points ?? 0} pts)`).join(' | ') || (q.prompt || 'Question ouverte')}</div>
            <div class="mt-2 d-flex justify-content-end gap-2 flex-wrap">
                <button class="btn btn-sm btn-warning text-dark" onclick="prefillVoteFromBank(${idx})">Prefill Vote</button>
                <button class="btn btn-sm btn-info text-dark" onclick="prefillQuizFromBank(${idx})">Prefill Quiz</button>
                <button class="btn btn-sm btn-theme" onclick="openQuizFromBank(${idx})">Broadcast Quiz</button>
            </div>
        </div>
    `).join('');
}

async function sendBankMessage(index) {
    const msg = currentBank.messages[index];
    if (!msg?.content) return;
    if (!await swalConfirm('Send this message to the live feed?')) return;
    await api('broadcast', 'POST', { message: msg.content, type: msg.type || 'info' });
    showNotif('Library message broadcasted', 'success');
}

function prefillVoteFromBank(index) {
    const question = currentBank.questions[index];
    if (!question) return;
    if ((question.type || 'single_choice') !== 'single_choice') {
        showNotif('This quiz is not a single choice vote. Prefill only for single-choice.', 'warn');
        return;
    }

    document.getElementById('voteQ').value = question.question || '';
    
    const opts = Array.isArray(question.options) ? question.options : [];
    document.getElementById('vOpt').value = opts.map(opt => `${opt.key}|${opt.label}|${opt.color||'#00b4d8'}|${opt.points||0}|${opt.note||''}`).join('\n');

    const info = document.getElementById('preparedVoteInfo');
    const notes = opts.map(opt => `${opt.key}: ${opt.note || ('no note')}`).join(' | ');
    info.textContent = IS_EN ? `Preloaded question (${opts.length} options). Guide: ${notes}` : `Question préchargée (${opts.length} options). Guide: ${notes}`;
    document.getElementById('voteSecretSwitch').checked = question.secret === true;
}

function prefillQuizFromBank(index) {
    const question = currentBank.questions[index];
    if (!question) return;
    const opts = Array.isArray(question.options) ? question.options : [];

    document.getElementById('quizQ').value = question.question || '';
    document.getElementById('quizType').value = normalizeQuizType(question.type);
    document.getElementById('quizOpt').value = opts.map(opt => `${opt.key}|${opt.label}|${opt.color||'#00b4d8'}|${opt.points||0}`).join('\n');
    document.getElementById('quizCorrect').value = (question.acceptable_answers || question.correct_order || []).join(',');
    document.getElementById('quizBasePoints').value = question.points || 10;
    showNotif('Quiz prefilled from library', 'success');
}

async function openQuizFromBank(index) {
    prefillQuizFromBank(index);
    await openQuiz();
}

// ── ONLINE COUNT ────────────────────────────────────────────
function updateOnlineCount(players) {
    document.getElementById('onlineCount').textContent = `${players.length} online`;
}

// ── TEAMS ───────────────────────────────────────────────────
const teamsById = {};
function updateTeams(teams) {
    if (!teams) return;
    teams.forEach(t => {
        teamsById[t.id] = t;
        teamColorMap[t.type] = t.color;
        teamNameMap[t.type] = t.name;
        teamScoredMap[t.type] = !!t.isScored;
        const sc = document.getElementById('msc-'+t.id);
        const on = document.getElementById('mon-'+t.id);
        const onv = document.getElementById('monv-'+t.id);
        const pc = document.getElementById('mpc-'+t.id);
        const bg = document.getElementById('mbadge-'+t.id);
        if (sc) sc.textContent = t.isScored ? t.score : 'MENTOR';
        if (onv) onv.textContent = t.onlineCount;
        else if (on) on.innerHTML = `<i class="bi bi-person-fill text-success"></i> ${t.onlineCount}`;
        if (pc) pc.textContent = t.playerCount;
        if (bg) bg.textContent = t.badge.icon;
    });
    renderAssignmentControls();
    renderPlayersRoster();
}

async function adjustScore(teamId, delta) {
    if (teamsById[teamId] && !teamsById[teamId].isScored) {
        showNotif('Mentor team is not scored', 'warn');
        return;
    }
    await api(`score/${teamId}`, 'POST', {delta: parseInt(delta)});
}

// ── USER MANAGEMENT ─────────────────────────────────────────
function currentTeamsList() {
    return Object.values(teamsById).sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
}

function renderAssignmentControls() {
    // Only implemented in manage-players view
}

function updatePlayersRoster(players, assignableUsers) {
    playersRosterCache = Array.isArray(players) ? players.slice() : [];
    assignableUsersCache = Array.isArray(assignableUsers) ? assignableUsers.slice() : [];
    const usersCount = document.getElementById('usersCount');
    if (usersCount) usersCount.textContent = playersRosterCache.length;
    syncTeamCountsFromRoster();
}

function syncTeamCountsFromRoster() {
    const totalsByTeamId = {};
    const onlineByTeamId = {};
    playersRosterCache.forEach(p => {
        if (!p || !p.teamId) return;
        if (p.isBanned) return;
        totalsByTeamId[p.teamId] = (totalsByTeamId[p.teamId] || 0) + 1;
        if (p.isOnline) {
            onlineByTeamId[p.teamId] = (onlineByTeamId[p.teamId] || 0) + 1;
        }
    });

    Object.values(teamsById).forEach(t => {
        if (!t) return;
        const total = totalsByTeamId[t.id] || 0;
        const online = onlineByTeamId[t.id] || 0;
        t.playerCount = total;
        t.onlineCount = online;
        const onv = document.getElementById('monv-' + t.id);
        const pc = document.getElementById('mpc-' + t.id);
        const on = document.getElementById('mon-' + t.id);
        if (onv) onv.textContent = String(online);
        else if (on) on.innerHTML = `<i class="bi bi-person-fill text-success"></i> ${online}`;
        if (pc) pc.textContent = String(total);
    });
}

// Only implemented in manage-players view
function renderPlayersRoster() {}
async function assignSelectedUsers() {}
async function savePlayer(playerId) {}
async function banPlayerAction(playerId) {}
async function unbanPlayerAction(playerId) {}
async function removePlayerAction(playerId) {}

// ── DECISION MATRIX ─────────────────────────────────────────
function updateMatrix(matrix) {
    const panel = document.getElementById('matrixPanel');
    if (!matrix) {
        panel.innerHTML = `<div class="text-white-50 text-center py-4 small"><i class="bi bi-grid-3x3-gap me-2"></i>No matrix for this phase</div>`;
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
            <div class="matrix-header text-theme"><i class="bi bi-grid-3x3-gap me-2"></i>DECISION MATRIX</div>
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
    showNotif('Broadcasted!');
}
async function phantom() {
    const msg = document.getElementById('bcMsg').value.trim() || 'PHANTOM GRID HAS BREACHED YOUR NETWORK.';
    await api('phantom','POST',{message:msg});
    document.getElementById('bcMsg').value = '';
    showNotif('☠️ PHANTOM message sent');
}

// ── VOTE ────────────────────────────────────────────────────
async function openVote() {
    const q = document.getElementById('voteQ').value.trim();
    if (!q) {
        showNotif('Question required', 'danger');
        return;
    }

    const options = parseManualOptions(document.getElementById('vOpt').value);

    if (!options || options.length < 2) {
        showNotif('At least 2 options are required', 'danger');
        return;
    }

    const isSecret = document.getElementById('voteSecretSwitch').checked;
    const response = await api('vote/open', 'POST', { question: q, options, is_secret: isSecret });
    if (!response?.ok) {
        showNotif('Unable to open vote', 'danger');
        return;
    }
    showNotif(IS_EN ? `Vote opened${isSecret ? ' (secret)' : ''}` : `Vote ouvert${isSecret ? ' (secret)' : ''}`);
    document.getElementById('preparedVoteInfo').textContent = 'Vote in progress...';
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

async function openQuiz() {
    const q = document.getElementById('quizQ').value.trim();
    const type = normalizeQuizType(document.getElementById('quizType').value);
    const options = parseManualOptions(document.getElementById('quizOpt').value);
    if (!q) return showNotif('Quiz question required', 'danger');
    if (['single_choice', 'multi_choice', 'order'].includes(type) && (!options || options.length < 2)) {
        return showNotif('This quiz type must contain at least 2 choices', 'danger');
    }

    const correctRaw = (document.getElementById('quizCorrect').value || '').trim();
    const correct_answers = correctRaw ? correctRaw.split(',').map(x => x.trim().toUpperCase()).filter(Boolean) : [];
    const base_points = parseInt(document.getElementById('quizBasePoints').value || '0', 10) || 0;

    const r = await api('quiz/open', 'POST', {
        question: q,
        type,
        options,
        correct_answers,
        base_points,
    });
    if (!r?.ok) return showNotif(r?.error || ('Unable to open quiz'), 'danger');
    showNotif('Quiz opened', 'success');
}

async function closeQuizWithScore() {
    const r = await api('quiz/close', 'POST');
    if (!r?.ok) return showNotif(r?.error || 'Error closing quiz', 'danger');
    showNotif(`Quiz closed — ${r.answeredTeams || 0} teams scored`, 'success');
}

function updateQuizTally(quiz) {
    const el = document.getElementById('quizTally');
    if (!el) return;
    if (!quiz) {
        el.innerHTML = `<div class="small text-white-50">No active quiz</div>`;
        return;
    }

    const rows = (quiz.options || []).map(opt => `<div class="small"><span class="fw-bold">${opt.key}</span> - ${opt.label}</div>`).join('');
    const resultRows = (quiz.results || []).map(r => {
        const answerDisplay = r.answerText ? `${r.answerKey || '—'} (${r.answerText})` : (r.answerKey || '—');
        return `<div class="small">${r.teamName}: ${answerDisplay} => <span class="text-theme">${r.awardedPoints} pts</span></div>`;
    }).join('');
    el.innerHTML = `
        <div class="small fw-bold text-info mb-1">${quiz.question || 'Quiz'} (${normalizeQuizType(quiz.type).replace('_',' ')})</div>
        <div class="mb-1">${rows}</div>
        <div class="small text-white-50 mb-1">Answers: ${quiz.answerCount || 0}</div>
        <div>${resultRows}</div>
    `;
}

function normalizeQuizType(type) {
    const v = String(type || '').trim().toLowerCase();
    if (['multi_choice', 'multi choice', 'multichoice', 'multiple_choice', 'multiple choice', 'multi_chice', 'multi chice', 'multi-choise'].includes(v)) return 'multi_choice';
    if (['short_answer', 'short answer', 'shortanswer', 'text', 'open'].includes(v)) return 'short_answer';
    if (['order', 'sort_order', 'sort order', 'ordering', 'rank', 'ranking'].includes(v)) return 'order';
    return 'single_choice';
}

async function closeVoteWithScore() {
    const d = await api('vote/close','POST');
    if (d.ok) {
        const msg = d.isTie
            ? `Vote closed — Tie (${(d.tiedKeys || []).join(', ')}) — no points awarded`
            : (d.awardedPoints > 0
                ? `Vote closed — Winner: ${d.winnerLabel || d.winner} — ${d.awardedPoints} pts awarded`
                : `Vote closed — Winner: ${d.winnerLabel || d.winner}`);
        showNotif(msg, 'success');
    } else {
        showNotif(d.error || 'Error', 'danger');
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
    const secretTag = vote.isSecret ? ' 🔒 secret' : '';
    el.innerHTML = `<div class="small text-theme fw-bold mb-2">📊 ${vote.question||'Vote en cours'}${secretTag}</div>${rows}`;
}

// ── INJECT ──────────────────────────────────────────────────
async function triggerInject(id) {
    if (!await swalConfirm('Trigger this inject?')) return;
    await api(`inject/${id}`,'POST');
    showNotif('Inject triggered','success');
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
const teamScoredMap = {};
@foreach($teams as $t)
teamColorMap['{{ $t->type }}'] = '{{ $t->color }}';
teamNameMap['{{ $t->type }}']  = '{{ $t->name }}';
teamScoredMap['{{ $t->type }}'] = {{ $t->is_scored ? 'true' : 'false' }};
@endforeach

function updateDecisions(decisions) {
    if (!Array.isArray(decisions)) return;
    const latest = decisions[0];
    const count = decisions.length;
    const signature = decisions.map(d => `${d.id}:${d.scoreAwarded}:${d.type}:${d.at}`).join('|');
    const unchanged = signature === decisionsSignature;
    document.getElementById('decCount').textContent = count;
    if (latest && latest.id > lastDecId) {
        lastDecId = latest.id;
        lastDecCount = count;
    }
    if (unchanged) return;
    decisionsSignature = signature;
    decisionsCache = decisions.slice(0, 200);
    populateDecisionTeamFilter(decisionsCache);
    renderDecisionsPanel();
}

function populateDecisionTeamFilter(decisions) {
    const el = document.getElementById('decTeamFilter');
    if (!el) return;
    const current = el.value || 'all';
    const teams = Array.from(new Set(decisions.map(d => d.teamType).filter(Boolean)));
    const opts = ['<option value="all">All Teams</option>'];
    teams.forEach(tt => {
        opts.push(`<option value="${escapeHtml(tt)}">${escapeHtml(teamNameMap[tt] || tt)}</option>`);
    });
    el.innerHTML = opts.join('');
    el.value = teams.includes(current) || current === 'all' ? current : 'all';
}

function renderDecisionsPanel() {
    const area = document.getElementById('decisionsArea');
    if (!area) return;
    const typeFilter = document.getElementById('decTypeFilter')?.value || 'all';
    const teamFilter = document.getElementById('decTeamFilter')?.value || 'all';

    let filtered = decisionsCache.slice();
    if (typeFilter !== 'all') filtered = filtered.filter(d => (d.type || '') === typeFilter);
    if (teamFilter !== 'all') filtered = filtered.filter(d => (d.teamType || '') === teamFilter);

    const teamCount = new Set(filtered.map(d => d.teamType).filter(Boolean)).size;
    const answerCount = filtered.filter(d => d.type === 'question').length;
    document.getElementById('decSummaryTeams').textContent = `${teamCount} teams`;
    document.getElementById('decSummaryAnswers').textContent = `${answerCount} quiz answers`;
    document.getElementById('decSummaryTotal').textContent = `${filtered.length} items`;

    if (filtered.length === 0) {
        area.innerHTML = `<div class="text-white-50 text-center py-3 small">No items for this filter.</div>`;
        return;
    }

    const grouped = {};
    filtered.forEach(d => {
        const key = d.teamType || '__unknown__';
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(d);
    });

    const teamKeys = Object.keys(grouped).sort((a, b) => {
        const maxA = Math.max(...grouped[a].map(x => x.id));
        const maxB = Math.max(...grouped[b].map(x => x.id));
        return maxB - maxA;
    });

    const typeIcons = {decision:'🎯', escalade:'📡', communication:'📢', question:'❓'};
    const html = teamKeys.map((teamKey, idx) => {
        const items = grouped[teamKey].sort((a, b) => b.id - a.id);
        const color = teamColorMap[teamKey] || '#aaa';
        const teamName = teamNameMap[teamKey] || items[0]?.teamName || 'Team';
        const quizCount = items.filter(x => x.type === 'question').length;
        const collapseId = `dec-team-${teamKey.replace(/[^a-zA-Z0-9_-]/g, '_')}`;
        const entriesHtml = items.map(d => {
            const teamIsScored = teamScoredMap[d.teamType] ?? true;
            const isMentorDecision = !teamIsScored;
            const quizInfo = d.type === 'question' ? parseQuizDecisionContent(d.content || '') : null;
            const answerBlock = quizInfo ? renderQuizAnswerBlock(quizInfo) : `<div style="font-size:.83rem">${escapeHtml(d.content || '')}</div>`;
            
            // Find linked inject if any
            const inject = d.csInjectId ? (injectCatalogCache.find(inj => inj.id === d.csInjectId) || null) : null;
            let injectInfoHtml = '';
            let actionTypeDropdownHtml = '';
            
            if (inject) {
                injectInfoHtml = `
                    <div class="mt-2 mb-2 p-2 rounded small text-start" style="background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.05)">
                        <div class="fw-bold text-white-50" style="font-size:0.75rem"><i class="bi bi-lightning-fill text-warning"></i> Responding to Inject: [${escapeHtml(inject.tag)}]</div>
                        <div style="font-size:0.72rem; color:rgba(255,255,255,0.7); font-style:italic" class="mt-1">${escapeHtml(inject.content)}</div>
                    </div>
                `;
                
                if (inject.requiresAction) {
                    const currentExpected = d.expectedActionType || inject.expectedActionType || 'decision';
                    actionTypeDropdownHtml = `
                        <div class="mb-2 mt-2 text-start">
                            <label class="small text-white-50 me-2" for="expected-type-${d.id}" style="font-size:0.72rem">Expected Action:</label>
                            <select id="expected-type-${d.id}" class="form-select form-select-sm d-inline-block w-auto py-0 px-1" style="height:22px; font-size:0.7rem; line-height:1; vertical-align:middle" onchange="onExpectedTypeChange(${d.id}, '${d.type}')">
                                <option value="decision" ${currentExpected === 'decision' ? 'selected' : ''}>Decision</option>
                                <option value="escalade" ${currentExpected === 'escalade' ? 'selected' : ''}>Escalation</option>
                                <option value="communication" ${currentExpected === 'communication' ? 'selected' : ''}>Communication</option>
                            </select>
                            <span id="grading-warning-${d.id}" class="ms-2 small fw-bold" style="font-size:0.72rem"></span>
                        </div>
                    `;
                }
            }

            return `
                <div id="dec-${d.id}" class="decision-review ${d.id === lastDecId ? 'dec-new' : ''}">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="dr-type">${typeIcons[d.type]||'📋'} ${escapeHtml((d.type||'').toUpperCase())}</span>
                        <span class="ms-auto decision-meta">${new Date(d.at).toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'})}</span>
                    </div>
                    ${answerBlock}
                    ${injectInfoHtml}
                    ${actionTypeDropdownHtml}
                    <div class="d-flex gap-1 mt-2 align-items-center" ${isMentorDecision ? 'style="opacity:.5"' : ''}>
                        <span class="small text-white-50">Score:</span>
                        <input type="number" id="award-${d.id}" value="${pendingAwardEdits[d.id] ?? (Number.isFinite(parseInt(d.scoreAwarded,10)) ? parseInt(d.scoreAwarded,10) : 0)}" min="0" max="100" class="form-control form-control-sm" style="width:70px" oninput="setPendingAward(${d.id}, this.value)" ${isMentorDecision ? 'disabled' : ''}>
                        <button onclick="awardScore(${d.id})" class="btn btn-sm btn-success ld-award" ${isMentorDecision ? 'disabled' : ''}>
                            ${isMentorDecision ? ('Non-scored Mentor') : ('Validate / Adjust')}
                        </button>
                        ${!isMentorDecision ? `<span class="small text-white-50 award-current">Current: ${Number.isFinite(parseInt(d.scoreAwarded,10)) ? parseInt(d.scoreAwarded,10) : 0} pts</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="decision-team-item">
                <h2 class="decision-team-header" id="heading-${collapseId}">
                    <button class="decision-team-btn ${idx === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${idx === 0 ? 'true' : 'false'}">
                        <span class="dr-team" style="background:${color}22;color:${color}">${escapeHtml(teamName)}</span>
                        <span class="badge bg-dark">${items.length}</span>
                        <span class="badge bg-info text-dark">${quizCount} quiz</span>
                        <span class="ms-auto decision-meta">${items[0]?.at ? new Date(items[0].at).toLocaleTimeString('en',{hour:'2-digit',minute:'2-digit'}) : ''}</span>
                    </button>
                </h2>
                <div id="${collapseId}" class="collapse ${idx === 0 ? 'show' : ''}">
                    <div class="decision-team-body">${entriesHtml}</div>
                </div>
            </div>
        `;
    }).join('');

    area.innerHTML = html;

    // Trigger initial state sync for expected types on any action-required injects
    filtered.forEach(d => {
        if (d.csInjectId) {
            const inject = injectCatalogCache.find(inj => inj.id === d.csInjectId);
            if (inject && inject.requiresAction) {
                onExpectedTypeChange(d.id, d.type);
            }
        }
    });
}



function parseQuizDecisionContent(content) {
    const raw = String(content || '');
    const m = raw.match(/^Quiz\s*\(([^)]+)\)\s*:\s*(.*?)\s*\|\s*(?:Answer|Réponse)\s*:\s*(.*)$/i);
    if (!m) return null;
    const quizType = normalizeQuizType((m[1] || '').trim());
    const question = (m[2] || '').trim();
    const answerRaw = (m[3] || '').trim();
    const am = answerRaw.match(/^(.*?)(?:\s*\((.*)\))?$/);
    const answerKey = (am?.[1] || '').trim();
    const answerText = (am?.[2] || '').trim();
    return { quizType, question, answerKey, answerText };
}

function renderQuizAnswerBlock(q) {
    const keyRaw = (q.answerKey || '').trim();
    const textRaw = (q.answerText || '').trim();
    const keyNorm = keyRaw && keyRaw !== '—' ? keyRaw : '';
    let answer = '—';
    if (q.quizType === 'order') {
        answer = keyNorm ? keyNorm.split(',').map(x => x.trim()).filter(Boolean).join(' > ') : '—';
    } else if (q.quizType === 'multi_choice') {
        answer = keyNorm ? keyNorm.split(',').map(x => x.trim()).filter(Boolean).join(', ') : '—';
    } else if (q.quizType === 'short_answer') {
        answer = textRaw || keyNorm || '—';
    } else {
        answer = keyNorm || textRaw || '—';
    }
    return `
        <div class="small mb-1">
            <span class="quiz-answer-chip">${escapeHtml(q.quizType.replace('_',' '))}</span>
            <span class="ms-1">${escapeHtml(q.question || 'Quiz')}</span>
        </div>
        <div style="font-size:.83rem">
            <span class="text-white-50">Answer:</span> ${escapeHtml(answer)}
        </div>
    `;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function setPendingAward(id, value) {
    pendingAwardEdits[id] = value;
    const expectedSelect = document.getElementById(`expected-type-${id}`);
    if (expectedSelect) {
        const warningSpan = document.getElementById(`grading-warning-${id}`);
        if (warningSpan) {
            const val = parseInt(value, 10);
            if (isNaN(val)) {
                warningSpan.textContent = "⚠️ Input a valid number";
                warningSpan.style.color = "#ffbe76";
            } else if (val < 10 || val > 30) {
                warningSpan.textContent = `⚠️ Warning: Out of range 10-30 (will clamp to ${val < 10 ? 10 : 30})`;
                warningSpan.style.color = "#ffbe76";
            } else {
                warningSpan.textContent = "✅ Match: Allowed range 10-30";
                warningSpan.style.color = "#2ecc71";
            }
        }
    }
}

async function awardScore(id) {
    const pts = parseInt(document.getElementById('award-'+id).value);
    const body = { points: pts };
    const expectedSelect = document.getElementById('expected-type-' + id);
    if (expectedSelect) {
        body.expected_action_type = expectedSelect.value;
    }
    
    const res = await api(`decision/${id}/award`,'POST',body);
    const awarded = res && typeof res.points_awarded !== 'undefined' ? res.points_awarded : pts;
    showNotif(`Score validated: ${awarded} pts`,'success');
    delete pendingAwardEdits[id];
    decisionsSignature = '';
    await poll();
    const card = document.getElementById(`dec-${id}`);
    if (card) {
        const note = card.querySelector('.award-current');
        if (note) note.textContent = `Current: ${awarded} pts`;
        const input = document.getElementById('award-'+id);
        if (input) input.value = awarded;
    }
}

function onExpectedTypeChange(decisionId, teamSubmittedType) {
    const expectedSelect = document.getElementById(`expected-type-${decisionId}`);
    const scoreInput = document.getElementById(`award-${decisionId}`);
    const warningSpan = document.getElementById(`grading-warning-${decisionId}`);
    if (!expectedSelect || !scoreInput || !warningSpan) return;

    const expectedType = expectedSelect.value;
    if (teamSubmittedType !== expectedType) {
        scoreInput.value = 0;
        scoreInput.disabled = true;
        warningSpan.textContent = "⚠️ Mismatch: Force 0 points";
        warningSpan.style.color = "#ff4d4d";
        pendingAwardEdits[decisionId] = 0;
    } else {
        scoreInput.disabled = false;
        let val = parseInt(scoreInput.value, 10) || 0;
        if (val < 10) {
            val = 10;
        } else if (val > 30) {
            val = 30;
        }
        scoreInput.value = val;
        warningSpan.textContent = "✅ Match: Allowed range 10-30";
        warningSpan.style.color = "#2ecc71";
        pendingAwardEdits[decisionId] = val;
    }
}

// ── BONUS BADGES ────────────────────────────────────────────
async function awardBadge(type) {
    const teamId = document.getElementById('badgeTeamSelect').value;
    if (!teamId) { showNotif('Select a team first','danger'); return; }
    const d = await api(`badge/${teamId}`,'POST',{badge_type: type});
    if (d.ok) showNotif(`${d.badge} → +${d.points} pts`,'success');
    else showNotif(d.error || ('Badge error'),'danger');
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

// ── COPY JOIN LINK ──────────────────────────────────────────
function copyJoinLink() {
    const url = window.location.origin + '/neptune/' + CODE;
    navigator.clipboard.writeText(url).then(() => {
        showNotif('Join link copied!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotif('Failed to copy link', 'danger');
    });
}

// ── END ─────────────────────────────────────────────────────
async function confirmEnd() {
    if (!await swalConfirm('End the exercise? This action is irreversible.')) return;
    await api('end','POST');
    showNotif('Exercise finished!','success');
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
</body>
</html>
