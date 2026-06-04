<!DOCTYPE html>
@php
    $isEn = true;
    $title = $scenario['title'] ?? 'NEPTUNE STRIKE';
@endphp
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} — {{ $session->name }}</title>
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
/* ── CS Participant Styles ────────────────────────────────── */
body {
    --bs-theme: #00ffcc !important;
    --bs-theme-rgb: 0, 255, 204 !important;
}
.cs-hero {
    background: linear-gradient(135deg, #000810 0%, #001525 60%, #000c14 100%) !important;
    border-top: 2px solid #00ffcc;
    border-bottom: 1px solid #00aaff;
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
</head>
<body>
<div class="container-fluid py-3" id="csParticipant">

{{-- PHANTOM Modal --}}
<div class="phantom-modal" id="phantomModal" onclick="closePhantom()">
    <div class="phantom-label">INTERCEPTED MESSAGE — {{ $scenario['attacker_name'] ?? 'MENACE' }}</div>
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
                <h1 class="mb-0 cs-title">
                    <span style="color:var(--bs-theme)">NEPTUNE</span> STRIKE
                </h1>
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
                                Role: <strong class="text-warning">Non-scored Mentor</strong>
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
                <h5 class="card-title mb-3"><i class="bi bi-collection-play me-2 text-info"></i>Operations Map</h5>
                <div id="phaseSituationMedia" class="small text-white-50">No media for the current phase.</div>
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
                <textarea class="form-control mb-3" id="decisionContent" rows="4" placeholder="Describe your decision, escalation or question..."></textarea>
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
                <h5 class="card-title mb-1"><i class="bi bi-hand-thumbs-up me-2 text-warning"></i>Vote in Progress</h5>
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
                    <div class="text-white-50 text-center py-3 small">Awaiting communications...</div>
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

<script src="{{ asset('hud/js/vendor.min.js') }}"></script>
<script>
const CODE = '{{ $session->code }}';
const CSRF = '{{ csrf_token() }}';
const PHASES = {{ count($scenario['phases']) }};
const PLAYER_ID = {{ $player?->id ?? 'null' }};
const TEAM_ID   = {{ $player?->cs_team_id ?? 'null' }};

const SCENARIO_KEY = '{{ $scenario['key'] }}';
const IS_EN = true;
let animationLoopRunning = false;
let latestSessionPhaseIdx = null;

// Canvas refs
let bgCv, mainCv, bgCtx, ctx, W, H;
const stars = Array.from({length:60},()=>({x:Math.random(),y:Math.random()*.44,r:Math.random()*.85+.2,a:Math.random()*.7+.3,t:Math.random()*6.28}));
const ships = [
  {name:'MV OLYMPIA',x:.28,y:.62,spd:.00008,dir:1,col:'#1a4060',alert:false,lbl:'IMO CLASS 3',type:'cargo'},
  {name:'MV ADRIATIC STAR',x:.63,y:.55,spd:.00006,dir:-1,col:'#1a3050',alert:true,lbl:'80,000T CRUDE',type:'tanker'},
  {name:'MV SILVER HORIZON',x:.52,y:.71,spd:.00005,dir:1,col:'#102030',alert:false,lbl:'AIS DARK',type:'susp'},
];
const cables=[
  {pts:[[.05,.82],[.2,.76],[.4,.8],[.6,.74],[.8,.78],[.95,.81]],name:'SEA-ME-WE 5'},
  {pts:[[.1,.86],[.3,.84],[.5,.88],[.7,.85],[.9,.83]],name:'MEDEX-3'},
];
const cmdNodes=[
  {lbl:'ANSSI\nCERT',x:.5,y:.38,col:'#00ffcc',ring:true},
  {lbl:'MARINE\nNATIONALE',x:.2,y:.56,col:'#00aaff'},
  {lbl:'PORT\nMARSEILLE',x:.78,y:.56,col:'#ffaa00'},
  {lbl:'SGDSN',x:.35,y:.73,col:'#ff6688'},
  {lbl:'EUNAVFOR\nNATO',x:.65,y:.73,col:'#aa88ff'},
  {lbl:'ENISA',x:.12,y:.38,col:'#44ddaa'},
  {lbl:'IMO',x:.88,y:.38,col:'#ffdd44'},
];
const cmdLinks=[[0,1],[0,2],[0,3],[0,4],[0,5],[0,6],[1,3],[2,4],[5,6]];

const HUD_BY_SCENE = {
  ocean:  {lat:"43°17'N",lon:"005°22'E",time:'06:42:00',vtms:'NOMINAL',scada:'NOMINAL',ais:'ACTIVE',threat:'LOW',apt:'MONITORING',marsec:'BRAVO',pct:5},
  port:   {lat:"43°18'N",lon:"005°21'E",time:'06:42:33',vtms:'OFFLINE',scada:'COMPROMISED',ais:'DISRUPTED',threat:'CRITICAL',apt:'73% MATCH',marsec:'CHARLIE',pct:80},
  cable:  {lat:"43°09'N",lon:"005°55'E",time:'07:17:12',vtms:'DEGRADED',scada:'NOMINAL',ais:'DARK',threat:'HIGH',apt:'APT-POSEIDON',marsec:'CHARLIE',pct:50},
  hack:   {lat:'--',lon:'--',time:'07:57:44',vtms:'OFFLINE',scada:'COMPROMISED',ais:'SPOOFED',threat:'EXTREME',apt:'CONFIRMED',marsec:'DELTA',pct:98},
  command:{lat:"48°52'N",lon:"002°21'E",time:'09:12:00',vtms:'RESTORING',scada:'ISOLATED',ais:'MONITORED',threat:'MEDIUM',apt:'ATTRIBUTED',marsec:'CHARLIE',pct:45}
};

const G = {
  cinScene: 'ocean',
  sceneIdx: 0,
  frame: 0,
  t: 0
};

function setScene(sc) {
  G.cinScene = sc;
  const el = document.getElementById('scene-title');
  if (el) {
    el.classList.remove('on');
    const scLabels = {
      ocean: 'PHASE I · INITIAL DETECTION',
      port: 'PHASE I–II · ATTACK ACTIVE',
      cable: 'PHASE II · HYBRID THREAT',
      hack: 'PHASE III · ESCALATION',
      command: 'PHASE IV · STRATEGIC RESPONSE'
    };
    const scSubs = {
      ocean: 'JUNE 9 2026 · 06:42 LOCAL · SITUATION NOMINALE',
      port: 'T+00:00 · SYSTEM FAILURE ACTIVE',
      cable: 'MV SILVER HORIZON · ROV DETECTED',
      hack: 'T+01:15 · MULTI-VECTOR ATTACK ACTIVE',
      command: 'CRISIS COORDINATION CELL ACTIVATED'
    };
    
    const stPh = document.getElementById('st-ph');
    const stH = document.getElementById('st-h');
    const stS = document.getElementById('st-s');
    
    if (stPh) stPh.textContent = scLabels[sc] || '';
    if (stH) stH.textContent = sc.toUpperCase();
    if (stS) stS.textContent = scSubs[sc] || '';
    
    setTimeout(() => {
      el.classList.add('on');
      setTimeout(() => el.classList.remove('on'), 3500);
    }, 50);
  }
}

function initCanvas() {
  bgCv = document.getElementById('bg-cv');
  mainCv = document.getElementById('main-cv');
  const container = document.getElementById('neptuneCanvasContainer');
  if (!bgCv || !mainCv || !container) return;
  
  W = container.clientWidth || 400;
  H = container.clientHeight || 225;
  bgCv.width = mainCv.width = W;
  bgCv.height = mainCv.height = H;
  
  bgCtx = bgCv.getContext('2d');
  ctx = mainCv.getContext('2d');
  
  const resizeObserver = new ResizeObserver(entries => {
    for (let entry of entries) {
      W = entry.contentRect.width;
      H = entry.contentRect.height;
      if (bgCv && mainCv) {
        bgCv.width = mainCv.width = W;
        bgCv.height = mainCv.height = H;
      }
    }
  });
  resizeObserver.observe(container);
}

function renderLoop() {
  if (!animationLoopRunning) return;
  G.t += .016; G.frame++;
  drawBG(); drawScene(); updateHUD();
  requestAnimationFrame(renderLoop);
}

function drawBG() {
  if (!bgCtx) return;
  bgCtx.clearRect(0,0,W,H);
  const sc = G.cinScene;
  if(sc==='ocean'||sc==='port'||sc==='cable') {
    const sh = H*.42;
    const sg = bgCtx.createLinearGradient(0,0,0,sh);
    sg.addColorStop(0,'#000810'); sg.addColorStop(.5,'#001525'); sg.addColorStop(1,'#002035');
    bgCtx.fillStyle=sg; bgCtx.fillRect(0,0,W,sh);
    stars.forEach(s=>{s.t+=.007;const a=s.a*(.7+.3*Math.sin(s.t));bgCtx.beginPath();bgCtx.arc(s.x*W,s.y*sh,s.r,0,Math.PI*2);bgCtx.fillStyle=`rgba(255,255,255,${a})`;bgCtx.fill();});
    bgCtx.beginPath();bgCtx.arc(W*.86,sh*.22,10,0,Math.PI*2);bgCtx.fillStyle='rgba(215,225,255,.78)';bgCtx.fill();
    bgCtx.beginPath();bgCtx.arc(W*.86+4,sh*.22-2,9,0,Math.PI*2);bgCtx.fillStyle='#000810';bgCtx.fill();
    const hg=bgCtx.createLinearGradient(0,sh-10,0,sh+15);hg.addColorStop(0,'rgba(0,180,120,.07)');hg.addColorStop(1,'transparent');bgCtx.fillStyle=hg;bgCtx.fillRect(0,sh-10,W,25);
    const seaG=bgCtx.createLinearGradient(0,sh,0,H);seaG.addColorStop(0,'#002d50');seaG.addColorStop(.3,'#001c35');seaG.addColorStop(1,'#000d1a');bgCtx.fillStyle=seaG;bgCtx.fillRect(0,sh,W,H-sh);
    for(let w=0;w<5;w++){const wy=sh+(H-sh)*(w/5)+Math.sin(G.t*.4+w)*2;bgCtx.beginPath();bgCtx.moveTo(0,wy);for(let x=0;x<=W;x+=8)bgCtx.lineTo(x,wy+Math.sin(x*.02+G.t*.5+w*.8)*3);bgCtx.strokeStyle=`rgba(0,255,204,${.025+w*.012})`;bgCtx.lineWidth=1;bgCtx.stroke();}
  } else if(sc==='hack') {
    bgCtx.fillStyle='#000004';bgCtx.fillRect(0,0,W,H);
    bgCtx.font='7px Share Tech Mono';
    for(let c=0;c<Math.floor(W/10);c++){for(let r=0;r<3;r++){const y=((G.frame*2+c*20+r*40)%(H+40))-20;bgCtx.fillStyle=`rgba(255,30,50,${.02+Math.random()*.03})`;bgCtx.fillText('01ABCDEF'[Math.floor(Math.random()*8)],c*10,y);}}
  } else if(sc==='command') {
    const g=bgCtx.createRadialGradient(W/2,H/2,0,W/2,H/2,W*.7);g.addColorStop(0,'#000d18');g.addColorStop(1,'#000004');bgCtx.fillStyle=g;bgCtx.fillRect(0,0,W,H);
    bgCtx.strokeStyle='rgba(0,255,204,.04)';bgCtx.lineWidth=.5;
    for(let x=0;x<W;x+=20){bgCtx.beginPath();bgCtx.moveTo(x,0);bgCtx.lineTo(x,H);bgCtx.stroke();}
    for(let y=0;y<H;y+=20){bgCtx.beginPath();bgCtx.moveTo(0,y);bgCtx.lineTo(W,y);bgCtx.stroke();}
  }
}

function drawShip(x,y,ship,flip=false) {
  if (!ctx) return;
  const sc=(ship.type==='tanker'?1.25:ship.type==='susp'?.78:1) * 0.6;
  ctx.save();ctx.translate(x,y);if(flip)ctx.scale(-1,1);ctx.scale(sc,sc);
  ctx.beginPath();ctx.moveTo(-48,0);ctx.quadraticCurveTo(-53,8,-38,10);ctx.lineTo(38,10);ctx.quadraticCurveTo(53,8,56,0);ctx.quadraticCurveTo(53,-2,38,-3);ctx.lineTo(-38,-3);ctx.closePath();
  ctx.fillStyle=ship.col;ctx.fill();ctx.strokeStyle=ship.type==='susp'?'rgba(255,100,0,.45)':'rgba(0,200,180,.18)';ctx.lineWidth=.7;ctx.stroke();
  ctx.fillStyle='#0d2030';ctx.fillRect(-4,-15,22,12);ctx.fillRect(1,-24,13,10);ctx.fillRect(4,-32,7,9);
  if(ship.type!=='susp'){ctx.beginPath();ctx.arc(56,-2,2,0,Math.PI*2);ctx.fillStyle=`rgba(255,240,150,${.65+.3*Math.sin(G.frame*.08)})`;ctx.fill();}
  if(ship.alert){ctx.beginPath();ctx.arc(0,-24,3+2*Math.sin(G.frame*.12),0,Math.PI*2);ctx.strokeStyle=`rgba(255,50,80,${.45+.4*Math.sin(G.frame*.12)})`;ctx.lineWidth=1;ctx.stroke();}
  ctx.restore();
  ctx.font='6px Share Tech Mono';ctx.textAlign='center';
  ctx.fillStyle=ship.type==='susp'?'rgba(255,140,0,.55)':'rgba(0,255,204,.4)';ctx.fillText(ship.name,x,y-19*sc-4);
}

function drawScene() {
  if (!ctx) return;
  ctx.clearRect(0,0,W,H);
  const sc=G.cinScene, sh=H*.42;
  if(sc==='ocean'||sc==='port'||sc==='cable') {
    if(sc==='ocean'){
      ships.forEach((s,i)=>{s.x+=s.spd*s.dir;if(s.x>1.1)s.x=-.1;if(s.x<-.1)s.x=1.1;drawShip(s.x*W,sh+(H-sh)*(.14+i*.22),s,s.dir<0);});
      ctx.fillStyle='#0a1a28';ctx.fillRect(W*.05-2,sh-16,4,16);
      ctx.beginPath();ctx.arc(W*.05,sh-16,3,0,Math.PI*2);ctx.fillStyle=`rgba(255,240,100,${.45+.45*Math.sin(G.frame*.05)})`;ctx.fill();
      ctx.save();ctx.translate(W*.05,sh-16);ctx.rotate(G.frame*.012);const bg2=ctx.createLinearGradient(0,0,W*.22,0);bg2.addColorStop(0,'rgba(255,240,100,.1)');bg2.addColorStop(1,'transparent');ctx.beginPath();ctx.moveTo(0,0);ctx.lineTo(W*.22,-H*.05);ctx.lineTo(W*.22,H*.05);ctx.fillStyle=bg2;ctx.fill();ctx.restore();
    }
    if(sc==='port'){
      ctx.fillStyle='#0a1520';ctx.fillRect(0,sh,W*.42,H-sh);ctx.fillStyle='#0d1e2e';ctx.fillRect(0,sh,W*.42,3);
      [W*.08,W*.18,W*.29].forEach((cx,ci)=>{
        ctx.fillStyle='#0a1820';ctx.fillRect(cx-2,sh-32,4,32);
        ctx.beginPath();ctx.moveTo(cx,sh-32);ctx.lineTo(cx+26,sh-41);ctx.strokeStyle=`rgba(255,50,80,${.45+.4*Math.sin(G.frame*.12+ci)})`;ctx.lineWidth=1;ctx.stroke();
        ctx.beginPath();ctx.moveTo(cx+26,sh-41);ctx.lineTo(cx+26,sh-13);ctx.strokeStyle=`rgba(255,50,80,${.35+.3*Math.sin(G.frame*.1+ci)})`;ctx.lineWidth=0.7;ctx.stroke();
      });
      for(let r=0;r<3;r++)for(let c=0;c<7;c++){ctx.fillStyle=['#1a3040','#0f2030','#162535','#0a1a28'][(r+c)%4];ctx.fillRect(2+c*21,sh+8+r*9,20,8);ctx.strokeStyle='rgba(0,100,150,.2)';ctx.lineWidth=.2;ctx.strokeRect(2+c*21,sh+8+r*9,20,8);}
      ctx.fillStyle='#3a0810';ctx.fillRect(W*.05,sh+8,20,8);ctx.strokeStyle=`rgba(255,50,80,${.35+.35*Math.sin(G.frame*.1)})`;ctx.lineWidth=.4;ctx.strokeRect(W*.05,sh+8,20,8);
      drawShip(W*.62,sh+(H-sh)*.36,ships[0]);
      ctx.fillStyle=`rgba(255,20,40,${.015+.012*Math.sin(G.frame*.08)})`;ctx.fillRect(0,sh,W*.42,H-sh);
    }
    if(sc==='cable'){
      const fg=ctx.createLinearGradient(0,H*.68,0,H);fg.addColorStop(0,'#001020');fg.addColorStop(1,'#000508');ctx.fillStyle=fg;ctx.fillRect(0,H*.68,W,H*.32);
      cables.forEach((cable,ci)=>{
        const pts=cable.pts.map(p=>[p[0]*W,p[1]*H]);
        ctx.beginPath();ctx.moveTo(pts[0][0],pts[0][1]);
        for(let i=1;i<pts.length;i++){const mx=(pts[i-1][0]+pts[i][0])/2,my=(pts[i-1][1]+pts[i][1])/2;ctx.quadraticCurveTo(pts[i-1][0],pts[i-1][1],mx,my);}
        ctx.lineTo(pts[pts.length-1][0],pts[pts.length-1][1]);ctx.strokeStyle=`rgba(0,255,204,${.22+.08*Math.sin(G.frame*.05+ci)})`;ctx.lineWidth=1;ctx.stroke();
        const pp=(G.frame*.007+ci*.45)%1,pi2=Math.floor(pp*(pts.length-1));
        if(pi2<pts.length-1){const f=pp*(pts.length-1)-pi2,px2=pts[pi2][0]+(pts[pi2+1][0]-pts[pi2][0])*f,py2=pts[pi2][1]+(pts[pi2+1][1]-pts[pi2][1])*f;ctx.beginPath();ctx.arc(px2,py2,1.5,0,Math.PI*2);ctx.fillStyle='#00ffcc';ctx.fill();}
      });
      drawShip(W*.54,H*.44,ships[2]);
      const rx=W*.54+Math.sin(G.frame*.016)*11,ry=H*.77;
      ctx.save();ctx.translate(rx,ry);ctx.fillStyle='#0a1520';ctx.fillRect(-8,-3,16,6);ctx.strokeStyle='rgba(255,150,0,.6)';ctx.lineWidth=.4;ctx.strokeRect(-8,-3,16,6);ctx.beginPath();ctx.arc(8,0,1.1,0,Math.PI*2);ctx.fillStyle=`rgba(255,200,0,${.5+.4*Math.sin(G.frame*.2)})`;ctx.fill();ctx.beginPath();ctx.moveTo(0,-3);ctx.lineTo(0,-(H*.3));ctx.strokeStyle='rgba(180,140,60,.22)';ctx.lineWidth=.4;ctx.stroke();ctx.restore();
    }
  } else if(sc==='hack') {
    const panels=[
      {t:'VTMS ACCESS',bl:true},
      {t:'SCADA BREACH',bl:false},
      {t:'AIS SPOOFER',bl:true},
      {t:'C2 BEACON',bl:false},
      {t:'APT-POSEIDON',bl:true},
      {t:'NETWORK MAP',bl:false},
    ];
    const cols=3,panW=(W-10)/cols,panH=(H-30)/2;
    panels.forEach((p,i)=>{
      const c=i%cols,r=Math.floor(i/cols),px=5+c*panW,py=15+r*panH*.72,pw=panW-3,ph=panH*.66;
      const ba=p.bl?.48+.48*Math.sin(G.frame*.12+i):1;
      ctx.fillStyle='rgba(20,5,8,.86)';ctx.fillRect(px,py,pw,ph);ctx.strokeStyle=`rgba(255,50,80,${ba*.5})`;ctx.lineWidth=.4;ctx.strokeRect(px,py,pw,ph);
      ctx.font='bold 5px Share Tech Mono';ctx.fillStyle=`rgba(255,${p.bl?'80':'130'},100,${ba})`;ctx.textAlign='left';ctx.fillText('> '+p.t,px+2,py+7);
    });
  } else if(sc==='command') {
    cmdLinks.forEach(([a2,b])=>{
      const na=cmdNodes[a2],nb=cmdNodes[b];const ax=na.x*W,ay=na.y*H,bx=nb.x*W,by=nb.y*H;
      const pr=((G.frame*.006+a2*.2)%1);const px2=ax+(bx-ax)*pr,py2=ay+(by-ay)*pr;
      ctx.strokeStyle='rgba(0,200,160,.09)';ctx.lineWidth=.4;ctx.beginPath();ctx.moveTo(ax,ay);ctx.lineTo(bx,by);ctx.stroke();
      ctx.beginPath();ctx.arc(px2,py2,1,0,Math.PI*2);ctx.fillStyle=`rgba(0,255,200,${.3+.3*Math.sin(G.frame*.1+a2)})`;ctx.fill();
    });
    cmdNodes.forEach((nd,ni)=>{
      const nx=nd.x*W,ny=nd.y*H,ic=ni===0,r=ic?17:12;
      const h=parseInt(nd.col.slice(1),16),cr=(h>>16)&255,cg=(h>>8)&255,cb=h&255;
      ctx.beginPath();ctx.arc(nx,ny,r,0,Math.PI*2);ctx.fillStyle=`rgba(${cr},${cg},${cb},.09)`;ctx.fill();ctx.strokeStyle=`rgba(${cr},${cg},${cb},.6)`;ctx.lineWidth=ic?0.8:0.6;ctx.stroke();
      const lines=nd.lbl.split('\n');ctx.font='5px Share Tech Mono';ctx.textAlign='center';
      lines.forEach((ln,li)=>{ctx.fillStyle=nd.col;ctx.fillText(ln,nx,ny-(lines.length-1)*2.5+li*5);});
    });
  }
  // Alert overlay
  const ao = document.getElementById('alert-ov');
  if (ao) {
    if(G.cinScene=='hack'||G.cinScene=='port'){ao.style.background=`rgba(255,20,30,${.015+.012*Math.sin(G.frame*.1)})`;ao.style.opacity='1';}
    else ao.style.opacity='0';
  }
}

function updateHUD() {
  const d = HUD_BY_SCENE[G.cinScene];
  if(!d) return;
  const setH=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
  setH('h-lat',d.lat); setH('h-lon',d.lon); setH('h-time',d.time); setH('h-vtms',d.vtms);
  setH('h-scada',d.scada); setH('h-ais',d.ais); setH('h-threat',d.threat); setH('h-apt',d.apt);
  setH('h-marsec',d.marsec);
  const el=document.getElementById('ts-fill');if(el)el.style.width=d.pct+'%';
}

let selectedTeam = null, decisionType = 'decision';
let lastBcId = 0, lastAtmo = '';
const teamStateById = {};
let currentBroadcasts = [];
let currentPhaseMessages = [];

// ── API helper ─────────────────────────────────────────────
async function api(path, method='GET', body=null) {
    const opts = { method, headers: {'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} };
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/neptune/${CODE}/api/${path}`, opts);
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
    if (!name) { toast('warn', 'Enter your display name'); return; }
    if (!selectedTeam) { toast('warn', 'Choose a team'); return; }
    const d = await api('join','POST',{team_type:selectedTeam, display_name:name});
    if (d.success) location.reload();
    else toast('danger', d.message ?? ('Error'));
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
    if (!content) { toast('warn', 'Write your decision'); return; }

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
    if (d.ok) { document.getElementById('decisionContent').value=''; toast('success', 'Decision submitted!'); }
    else toast('danger', d.error??('Error'));
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
        toast('success', 'Vote recorded');
    } else {
        toast('warn', res.error || ('Vote not registered'));
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
    if (res.ok) toast('success', 'Quiz answer recorded');
    else toast('warn', res.error || ('Quiz answer not registered'));
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
        toast('success', 'Quiz answers recorded');
        multiChoiceSelection = [];
    }
    else toast('warn', res.error || ('Quiz answer not registered'));
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
        toast('success', 'Quiz order recorded');
        orderSelection = [];
    } else {
        toast('warn', res.error || ('Quiz answer not registered'));
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
    if (res.ok) toast('success', 'Short answer recorded');
    else toast('warn', res.error || ('Quiz answer not registered'));
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
        renderPhaseContent(d.phaseContent);
        updateVote(d.vote);
        updateQuiz(d.quiz);
        handleAtmo(d.session?.atmosphere);
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

    if (SCENARIO_KEY === 'neptune_strike' && !media.some(m => m.isLive)) {
        if (!document.getElementById('neptuneCanvasContainer')) {
            mediaRoot.innerHTML = `
                <div id="neptuneCanvasContainer" class="position-relative overflow-hidden w-100 rounded" style="aspect-ratio: 16/9; background: #000;">
                    <canvas id="bg-cv" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;"></canvas>
                    <canvas id="main-cv" style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none;"></canvas>
                    <div id="alert-ov" style="position:absolute; inset:0; pointer-events:none; opacity:0; transition:opacity .1s; z-index:3;"></div>
                    <div class="scanlines" style="position:absolute; inset:0; background:repeating-linear-gradient(0deg,transparent,transparent 3px,rgba(0,0,0,.04) 3px,rgba(0,0,0,.04) 4px); pointer-events:none; z-index:2;"></div>
                    <div class="vignette" style="position:absolute; inset:0; background:radial-gradient(ellipse at center,transparent 38%,rgba(0,0,0,.72) 100%); pointer-events:none; z-index:2;"></div>
                    <div id="hud" style="position:absolute; inset:0; pointer-events:none; z-index:4; font-family:'Share Tech Mono',monospace; font-size:7px; color:rgba(0,255,204,0.65); padding:6px; line-height:1.2">
                        <div class="d-flex justify-content-between">
                            <div>LAT: <span id="h-lat" class="hv">--</span> | LON: <span id="h-lon" class="hv">--</span></div>
                            <div>TIME: <span id="h-time" class="hv">--</span></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>VTMS: <span id="h-vtms" class="hv">--</span></div>
                            <div>SCADA: <span id="h-scada" class="hv">--</span></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>AIS: <span id="h-ais" class="hv">--</span></div>
                            <div>APT: <span id="h-apt" class="hv">--</span></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>THREAT: <span id="h-threat" class="hv">--</span></div>
                            <div>MARSEC: <span id="h-marsec" class="hv">--</span></div>
                        </div>
                        <div style="position:absolute; bottom:0; left:0; right:0; height:2px; background:#030c14;">
                            <div id="ts-fill" style="height:100%; background:linear-gradient(90deg,#00ffcc,#ffaa00,#ff3355); transition:width .4s; width:0%;"></div>
                        </div>
                    </div>
                    <div id="scene-title" class="position-absolute text-center text-white" style="top:50%; left:50%; transform:translate(-50%,-50%); pointer-events:none; z-index:5; opacity:0; transition:opacity .5s;">
                        <div id="st-ph" style="font-family:'Share Tech Mono',monospace; font-size:7px; color:#00ffcc; letter-spacing:2px; margin-bottom:2px;"></div>
                        <div id="st-h" style="font-family:'Orbitron',monospace; font-weight:700; font-size:9px; letter-spacing:1px; text-shadow:0 0 20px rgba(0,255,204,0.5); text-transform:uppercase;"></div>
                        <div id="st-s" style="font-family:'Share Tech Mono',monospace; font-size:6px; color:rgba(255,255,255,0.5); letter-spacing:1px;"></div>
                    </div>
                </div>
            `;
            initCanvas();
            if (!animationLoopRunning) {
                animationLoopRunning = true;
                renderLoop();
            }
        }
        const sceneOrder = ['ocean', 'cable', 'port', 'hack', 'command'];
        const targetScene = sceneOrder[latestSessionPhaseIdx ?? 0] || 'ocean';
        if (targetScene !== G.cinScene) {
            setScene(targetScene);
        }
        return;
    }

    const mediaHtml = media.map((m) => {
        if (m.type === 'video') {
            return `<div class="mb-2"><div class="fw-bold">${m.title || 'Video'}</div><video src="${m.url}" class="w-100 rounded mt-1" controls ${m.muted ? 'muted' : ''} ${m.loop ? 'loop' : ''}></video><div class="text-white-50 small">${m.caption || ''}</div></div>`;
        }
        if (m.type === 'animation') {
            return `<div class="mb-2"><div class="fw-bold">${m.title || 'Animation'}</div><img src="${m.url}" class="w-100 rounded mt-1" alt="${m.title || 'animation'}"><div class="text-white-50 small">${m.caption || ''}</div></div>`;
        }
        return `<div class="mb-2"><div class="fw-bold">${m.title || 'Image'}</div><img src="${m.url}" class="w-100 rounded mt-1" alt="${m.title || 'image'}"><div class="text-white-50 small">${m.caption || ''}</div></div>`;
    }).join('');
    mediaRoot.innerHTML = mediaHtml || `<div class="text-white-50">${'No media for the current phase.'}</div>`;
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
    latestSessionPhaseIdx = idx;
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
        lastBcId = latest.id;
        if (latest.isPhantom) showPhantom(latest.message);
        else toast(latest.type || 'info', String(latest.message || '').substring(0, 80));
    }
    currentBroadcasts = bcs.filter(b => !b.isPhantom);
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
        source: 'White Cell',
    }));

    const merged = [...mentorItems, ...phaseItems].slice(0, 30);
    if (!merged.length) {
        feed.innerHTML = `<div class="text-white-50 text-center py-3 small">${'Awaiting communications...'}</div>`;
        return;
    }

    feed.innerHTML = merged.map(item => {
        const when = item.at ? new Date(item.at).toLocaleTimeString('fr', {hour:'2-digit', minute:'2-digit'}) : 'PHASE';
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
    document.getElementById('voteQuestion').textContent = `${vote.question ?? ''}${isSecretOpen ? (' (secret vote)') : ''}`;
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
        opts.innerHTML += `<div class="small text-warning mt-2 w-100">${'Your team has a mentor role and does not vote.'}</div>`;
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
            html += `<div class="w-100 mt-2"><button onclick="submitMultiChoice()" class="btn btn-sm btn-primary w-100 fw-bold">${'Validate multiple answers'}</button></div>`;
        }
    } else if (currentQuizType === 'order') {
        const answeredOrder = alreadyAnswered ? myAnswer.split(',').filter(Boolean) : orderSelection;
        html = `<div class="small text-white-50 w-100 mb-2">${'Current order:'} ${answeredOrder.length ? answeredOrder.join(' > ') : '—'}</div>`;
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
                <button onclick="submitOrderChoice()" class="btn btn-sm btn-primary flex-fill fw-bold">${IS_EN ? 'Validate order' : "Valider l'ordre"}</button>
                <button onclick="resetOrderChoice()" class="btn btn-sm btn-outline-light">${'Reset'}</button>
            </div>`;
        }
    } else if (currentQuizType === 'short_answer') {
        const safeText = (alreadyAnswered ? (myAnswerText || '') : '');
        html = `<textarea id="quizShortAnswer" class="form-control form-control-sm mb-2" rows="3" placeholder="${'Enter your response...'}" ${alreadyAnswered ? 'disabled' : ''}>${safeText}</textarea>`;
        if (!alreadyAnswered) {
            html += `<button onclick="submitShortAnswer()" class="btn btn-sm btn-primary w-100 fw-bold">${'Validate text answer'}</button>`;
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
    document.getElementById('quizMeta').textContent = `${'Type:'} ${currentQuizType.replace('_',' ')} · ${'Answers received:'} ${quiz.answerCount || 0}${prompt ? ' · ' + prompt : ''}`;
}

// ── ATMOSPHERE ─────────────────────────────────────────────
function handleAtmo(atmo) {
    if (atmo === lastAtmo) return;
    lastAtmo = atmo;
    if (atmo === 'victory') toast('success', '🏆 Exercise finished — Calculating results...');
    if (atmo === 'crisis') toast('danger', '🚨 CRISIS STATE DECLARED');
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
</body>
</html>
