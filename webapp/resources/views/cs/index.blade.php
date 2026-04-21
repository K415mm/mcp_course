@extends('layouts.app')
@section('title', 'CARTHAGE SHIELD — Sessions')

@push('head')
<style>
.cs-hero{background:linear-gradient(135deg,#030f1a 0%,#071a2e 60%,#0a1a1a 100%);min-height:180px;position:relative;overflow:hidden}
.cs-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(0,180,216,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(0,180,216,.04) 1px,transparent 1px);background-size:40px 40px}
.cs-title{font-family:'Space Mono',monospace;font-weight:700;font-size:2.2rem;letter-spacing:2px}
.cs-title .shield{color:#00b4d8}.cs-title .word{color:#e2eaf4}
.scenario-pick{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
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
    <div class="card mb-4 border-0 cs-hero">
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        <div class="card-body d-flex align-items-center justify-content-between p-4" style="position:relative;z-index:1">
            <div>
                <h1 class="mb-1 cs-title"><span class="shield">CARTHAGE</span> <span class="word">SHIELD</span></h1>
                <p class="text-white-50 mb-2" style="font-size:.95rem">Exercice de Cybersécurité Nationale — Simulation Tabletop Multi-Équipes</p>
                <p class="text-white-50 mb-0" style="font-size:.8rem">
                    <i class="bi bi-people me-1"></i> 6 équipes &middot;
                    <i class="bi bi-layers me-1"></i> 5 phases &middot;
                    <i class="bi bi-clock me-1"></i> 90–120 min &middot;
                    <i class="bi bi-shield-fill-exclamation me-1"></i> PHANTOM GRID
                </p>
            </div>
            <div class="d-none d-md-flex flex-column align-items-end gap-2" style="position:relative;z-index:1">
                <a href="{{ route('admin.cs.index') }}" class="btn btn-lg px-4" style="background:linear-gradient(90deg,#00b4d8,#0077a8);color:#000;font-weight:700">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle Session
                </a>
                <a href="{{ route('admin.cs.entities.index') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-gear me-1"></i>Configurer Entités
                </a>
            </div>
        </div>
    </div>

    <div class="row gx-3">

        {{-- CREATE FORM --}}
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-plus-circle me-2 text-theme"></i>Créer une session</h5>

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

                    <form method="POST" action="{{ route('admin.cs.store') }}">
                        @csrf
                        <input type="hidden" name="scenario_key" id="scenarioInput" value="{{ $scenarios[0]['key'] ?? 'phantom_grid' }}">
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Nom de la session</label>
                            <input class="form-control" name="name" placeholder="Ex: CARTHAGE SHIELD — Exercice 2026" required>
                        </div>
                        <button type="submit" class="btn btn-theme w-100 fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>CRÉER LA SESSION
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
                    <h5 class="card-title mb-3"><i class="bi bi-collection me-2 text-theme"></i>Sessions existantes
                        <span class="badge bg-dark text-white-50 ms-2">{{ $sessions->total() }}</span>
                    </h5>

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
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('cs.moderator', $s->code) }}" title="Console Modérateur"><i class="bi bi-sliders"></i></a>
                            <a class="btn btn-sm btn-outline-theme" href="{{ route('cs.show', $s->code) }}" title="Vue Participant"><i class="bi bi-people"></i></a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('cs.dashboard', $s->code) }}" target="_blank" title="Grand écran"><i class="bi bi-display"></i></a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-white-50 py-5">
                        <i class="bi bi-shield-slash" style="font-size:2.5rem;opacity:.3"></i>
                        <p class="mt-2 mb-0">Aucune session créée</p>
                        <p class="small">Utilisez le formulaire pour créer votre première session.</p>
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
