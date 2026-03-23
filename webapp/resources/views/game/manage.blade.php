@extends('layouts.app')
@section('title', 'CyberBreach — Gérer ' . $session->name)

@section('content')
<div class="container-fluid py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-0" style="font-family:'Space Mono',monospace;">
                <span style="color:#3a90e8;">CYBER</span><span style="color:#e83a3a;">BREACH</span>
                <span class="text-white-50 fw-normal ms-2" style="font-size:.9rem;">{{ $session->name }}</span>
            </h4>
            <div class="text-white-50 small mt-1">
                <i class="bi bi-key me-1"></i>{{ $session->code }}
                &middot; Scénario: {{ $session->scenarioTitle() }}
                &middot; {{ $session->maxRounds() }} rounds
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('game.show', $session) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-play-fill me-1"></i>Ouvrir le plateau
            </a>
            <a href="{{ route('game.lobby') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Lobby
            </a>
        </div>
    </div>

    <div class="row g-3">
        {{-- Available Users --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h6 class="mb-2"><i class="bi bi-people me-1 text-theme"></i>Étudiants disponibles</h6>
                    <input type="text" id="userSearch" class="form-control form-control-sm mb-2" placeholder="Rechercher un étudiant...">
                    <div id="availableUsers" style="max-height:500px;overflow-y:auto;">
                        @foreach($availableUsers as $user)
                            <div class="user-item d-flex align-items-center justify-content-between p-2 mb-1 rounded" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);" data-name="{{ strtolower($user->name) }}" data-user-id="{{ $user->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cb-player-avatar" style="background:var(--bs-theme);width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">{{ $user->initials() }}</div>
                                    <div>
                                        <div class="small fw-bold text-white">{{ $user->name }}</div>
                                        <div class="small text-white-50" style="font-size:.7rem;">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-primary" onclick="assignUser({{ $user->id }}, 'blue', this)" title="Blue Team">
                                        <i class="bi bi-shield"></i>
                                    </button>
                                    <button class="btn btn-xs btn-outline-danger" onclick="assignUser({{ $user->id }}, 'red', this)" title="Red Team">
                                        <i class="bi bi-bug"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        @if($availableUsers->isEmpty())
                            <div class="text-center text-white-50 py-3 small">Tous les étudiants sont assignés</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Blue Team --}}
        <div class="col-lg-4">
            <div class="card h-100" style="border-left:3px solid #3a90e8;">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h6 class="mb-2" style="color:#3a90e8;font-family:'Space Mono',monospace;">
                        <i class="bi bi-shield-lock me-1"></i>BLUE TEAM
                        <span class="badge bg-primary ms-1" id="blueCount">{{ $session->blueTeam?->players->count() ?? 0 }}</span>
                    </h6>
                    <div id="blueTeamList">
                        @foreach($session->blueTeam?->players ?? [] as $p)
                            <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded" style="background:rgba(58,144,232,.08);border:1px solid rgba(58,144,232,.2);" id="player-{{ $p->user_id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:30px;height:30px;border-radius:50%;background:#3a90e8;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#000;">{{ $p->user->initials() }}</div>
                                    <div>
                                        <div class="small fw-bold text-white">{{ $p->user->name }}</div>
                                        <div class="small text-white-50" style="font-size:.7rem;">{{ $p->user->email }}</div>
                                    </div>
                                </div>
                                <button class="btn btn-xs btn-outline-danger" onclick="removeUser({{ $p->user_id }}, this)">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Red Team --}}
        <div class="col-lg-4">
            <div class="card h-100" style="border-left:3px solid #e83a3a;">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h6 class="mb-2" style="color:#e83a3a;font-family:'Space Mono',monospace;">
                        <i class="bi bi-bug me-1"></i>RED TEAM
                        <span class="badge bg-danger ms-1" id="redCount">{{ $session->redTeam?->players->count() ?? 0 }}</span>
                    </h6>
                    <div id="redTeamList">
                        @foreach($session->redTeam?->players ?? [] as $p)
                            <div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded" style="background:rgba(232,58,58,.08);border:1px solid rgba(232,58,58,.2);" id="player-{{ $p->user_id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:30px;height:30px;border-radius:50%;background:#e83a3a;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#000;">{{ $p->user->initials() }}</div>
                                    <div>
                                        <div class="small fw-bold text-white">{{ $p->user->name }}</div>
                                        <div class="small text-white-50" style="font-size:.7rem;">{{ $p->user->email }}</div>
                                    </div>
                                </div>
                                <button class="btn btn-xs btn-outline-danger" onclick="removeUser({{ $p->user_id }}, this)">
                                    <i class="bi bi-x-lg"></i>
                                </button>
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
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<script>
const SESSION_ID = {{ $session->id }};
const CSRF = '{{ csrf_token() }}';
const API = `/game/${SESSION_ID}/api`;

// Search filter
document.getElementById('userSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#availableUsers .user-item').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

async function assignUser(userId, teamType, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`${API}/assign-player`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ user_id: userId, team_type: teamType }),
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur');
            btn.disabled = false;
        }
    } catch (e) { alert('Erreur réseau'); btn.disabled = false; }
}

async function removeUser(userId, btn) {
    if (!confirm('Retirer ce joueur ?')) return;
    btn.disabled = true;
    try {
        const res = await fetch(`${API}/remove-player`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ user_id: userId }),
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur');
            btn.disabled = false;
        }
    } catch (e) { alert('Erreur réseau'); btn.disabled = false; }
}
</script>
@endpush
