@extends('layouts.app')
@section('title', 'NEPTUNE STRIKE — Sessions')

@push('head')
<style>
/* ═══════════════════════════════════════════════════
   HEADER — Teal/Blue HUD Bar with center medallion
   ═══════════════════════════════════════════════════ */
.cs-header {
    position: relative;
    display: flex; align-items: center;
    height: 88px;
    background: linear-gradient(90deg, rgba(4,10,15,.98) 0%, rgba(8,18,30,.97) 30%, rgba(8,30,45,.97) 50%, rgba(8,18,30,.97) 70%, rgba(4,10,15,.98) 100%);
    border-top: 2px solid #00ffcc;
    border-bottom: 1px solid #00aaff;
    border-left: 1px solid rgba(255,255,255,0.08);
    border-right: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    box-shadow: 0 0 40px rgba(0,255,204,.2), inset 0 1px 0 rgba(0,255,204,.15), inset 0 -1px 0 rgba(0,170,255,.2);
    overflow: visible;
    padding: 0 28px;
    margin-bottom: 2rem;
}
.cs-header::before {
    content: ''; position: absolute; top: 0; left: 10%; right: 10%; height: 1px;
    background: linear-gradient(90deg, transparent, #00ffcc, transparent);
    opacity: .5;
}
.cs-left { display: flex; flex-direction: column; justify-content: center; gap: 2px; flex: 1; min-width: 0; }
.logo-txt {
    font-family: 'Space Mono', monospace;
    font-weight: 700; font-size: 1.3rem; letter-spacing: 4px;
    background: linear-gradient(90deg, #00ffcc 0%, #00aaff 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.logo-txt .shield-word {
    background: linear-gradient(90deg, #ff3355 0%, #ffaa00 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.scenario-sub { font-size: .72rem; color: rgba(0,255,204,.55); font-family: 'Space Mono', monospace; letter-spacing: 1px; }

.cs-medallion {
    position: absolute; left: 50%; transform: translateX(-50%); top: -18px; width: 124px; height: 124px; z-index: 20;
    display: flex; align-items: center; justify-content: center;
}
.cs-medallion::before {
    content: ''; position: absolute; left: 50%; transform: translateX(-50%); top: 50%; transform: translate(-50%, -50%);
    width: 220px; height: 44px;
    background: linear-gradient(90deg, transparent 0%, rgba(0,255,204,.12) 15%, rgba(0,255,204,.25) 50%, rgba(0,255,204,.12) 85%, transparent 100%);
    border-top: 1px solid rgba(0,255,204,.3); border-bottom: 1px solid rgba(0,255,204,.3); border-radius: 4px;
}
.cs-medallion::after {
    content: '⚓'; position: absolute; font-size: .9rem; color: #00ffcc; opacity: .5;
    animation: cornerSpin 8s linear infinite;
    top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(0deg) translateX(72px);
}
@keyframes cornerSpin { from { transform: translate(-50%,-50%) rotate(0deg) translateX(72px); } to { transform: translate(-50%,-50%) rotate(360deg) translateX(72px); } }
.cs-medal-img {
    width: 110px; height: 110px; object-fit: contain; position: relative; z-index: 2;
    filter: drop-shadow(0 0 18px rgba(0,255,204,.7)) drop-shadow(0 0 6px rgba(0,170,255,.5));
    animation: medalFloat 4s ease-in-out infinite;
}
@keyframes medalFloat { 0%,100% { transform: translateY(0); filter: drop-shadow(0 0 18px rgba(0,255,204,.7)) drop-shadow(0 0 6px rgba(0,170,255,.5)); } 50% { transform: translateY(-4px); filter: drop-shadow(0 0 26px rgba(0,255,204,.9)) drop-shadow(0 0 10px rgba(0,170,255,.6)); } }

.cs-right { display: flex; align-items: center; gap: 18px; flex-shrink: 0; position: relative; z-index: 30; }

.scenario-pick{display:grid;grid-template-columns:1fr;gap:10px}
.sc-opt{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:14px 12px;cursor:pointer;transition:all .2s;border-top:3px solid transparent}
.sc-opt:hover{border-color:rgba(255,255,255,.2)}
.sc-opt.active{border-top-color:var(--bs-theme);border-color:var(--bs-theme);background:rgba(var(--bs-theme-rgb),.08)}
.sc-opt-title{font-weight:700;font-size:.95rem;margin-bottom:4px}
.sc-opt-desc{font-size:.78rem;color:rgba(255,255,255,.5);line-height:1.4;margin-bottom:6px}
.session-row{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:14px 16px;margin-bottom:8px;display:flex;align-items:center;gap:12px;transition:all .2s}
.session-row:hover{border-color:var(--bs-theme);background:rgba(var(--bs-theme-rgb),.05)}
.session-code{font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--bs-theme);min-width:80px}
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="cs-header mt-3">
        <div class="cs-left">
            <div class="logo-txt">NEPTUNE <span class="shield-word">STRIKE</span></div>
            <div class="scenario-sub mt-1">Maritime Cyber Crisis Simulation</div>
            <div class="scenario-sub" style="font-size: 0.65rem; opacity: 0.6;">
                <i class="bi bi-people me-1"></i> 5 Roles &middot;
                <i class="bi bi-layers me-1"></i> 5 Phases &middot;
                <i class="bi bi-clock me-1"></i> 45 min
            </div>
        </div>

        <div class="cs-medallion">
            <span style="font-size: 3rem; filter: drop-shadow(0 0 10px var(--bs-theme))">⚓</span>
        </div>

        <div class="cs-right">
            <a href="{{ route('admin.neptune.index') }}" class="btn btn-sm px-3" style="background:linear-gradient(90deg,#00ffcc,#00aaff);color:#000;font-weight:700;border:none;">
                <i class="bi bi-plus-lg me-1"></i>New Session
            </a>
        </div>
    </div>

    <div class="row gx-3">

        {{-- CREATE FORM --}}
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-plus-circle me-2 text-theme"></i>Create Session</h5>

                    <div class="scenario-pick mb-3" id="scenarioPicker">
                        @foreach($scenarios as $s)
                        <div class="sc-opt {{ $loop->first ? 'active' : '' }}" data-key="{{ $s['key'] }}" onclick="pickScenario('{{ $s['key'] }}',this)">
                            <div class="sc-opt-title">{{ $s['title'] }}</div>
                            <div class="sc-opt-desc">{{ $s['description'] }}</div>
                            <span class="badge" style="background:rgba(var(--bs-theme-rgb),.2);color:var(--bs-theme);font-size:.7rem">{{ $s['difficulty'] }}</span>
                            <span class="badge bg-dark text-white-50 ms-1" style="font-size:.7rem">⏱ {{ $s['duration'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.neptune.store') }}">
                        @csrf
                        <input type="hidden" name="scenario_key" id="scenarioInput" value="{{ $scenarios[0]['key'] ?? 'neptune_strike' }}">
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Session Name</label>
                            <input class="form-control" name="name" placeholder="Ex: NEPTUNE STRIKE — Session A" required>
                        </div>
                        <button type="submit" class="btn btn-theme w-100 fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>CREATE SESSION
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- SESSION LIST --}}
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title mb-0"><i class="bi bi-collection me-2 text-theme"></i>Existing Sessions
                            <span class="badge bg-dark text-white-50 ms-2">{{ $sessions->total() }}</span>
                        </h5>
                        <form method="POST" action="{{ route('admin.neptune.clean_finished') }}" onsubmit="return confirmFormSubmit(event, 'Delete all finished sessions? This action is irreversible.')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Clean finished sessions">
                                <i class="bi bi-trash"></i> Clean
                            </button>
                        </form>
                    </div>

                    @forelse($sessions as $s)
                    <div class="session-row">
                        <div class="session-code">{{ $s->code }}</div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-white">{{ $s->name }}</div>
                            <div class="small text-white-50">
                                {{ $s->scenario_key }} &middot; {{ $s->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <span class="badge {{ match($s->status){ 'active'=>'bg-success','paused'=>'bg-warning text-dark','finished'=>'bg-secondary',default=>'bg-dark text-white-50' } }}">
                            {{ strtoupper($s->status) }}
                        </span>
                        <div class="d-flex gap-1">
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('neptune.moderator', $s->code) }}" title="Moderator Console"><i class="bi bi-sliders"></i></a>
                            <a class="btn btn-sm btn-outline-theme" href="{{ route('neptune.show', $s->code) }}" title="Team View"><i class="bi bi-people"></i></a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('neptune.dashboard', $s->code) }}" target="_blank" title="Grand Screen"><i class="bi bi-display"></i></a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-white-50 py-5">
                        <i class="bi bi-shield-slash" style="font-size:2.5rem;opacity:.3"></i>
                        <p class="mt-2 mb-0">No sessions created yet</p>
                        <p class="small">Use the form to create your first session.</p>
                    </div>
                    @endforelse

                    @if($sessions->hasPages())
                        <div class="mt-3">{{ $sessions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function pickScenario(key, el) {
    document.querySelectorAll('.sc-opt').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('scenarioInput').value = key;
}
</script>
@endpush
