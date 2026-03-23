@extends('layouts.app')
@section('title', 'CyberBreach — Game Lobby')

@section('content')
<div class="container-fluid py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="card mb-4 border-0 overflow-hidden" style="background: linear-gradient(135deg, #0a1628 0%, #1a0a2e 50%, #2a0a1e 100%); min-height: 180px;">
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        <div class="card-body d-flex align-items-center justify-content-between p-4">
            <div>
                <h1 class="mb-1" style="font-family:'Space Mono',monospace;font-weight:700;font-size:2.2rem;">
                    <span style="color:#3a90e8;">CYBER</span><span style="color:#e83a3a;">BREACH</span>
                </h1>
                <p class="text-white-50 mb-2" style="font-size:.95rem;">DevCo Edition v2.0 — Tabletop Cybersecurity Exercise</p>
                <p class="text-white-50 mb-0" style="font-size:.8rem;">
                    <i class="bi bi-people me-1"></i> 50 participants &middot;
                    <i class="bi bi-layers me-1"></i> 8 phases &middot;
                    <i class="bi bi-clock me-1"></i> 4 heures &middot;
                    <i class="bi bi-shield-lock me-1"></i> 2 équipes
                </p>
            </div>
            @if(Auth::user()->isAdmin())
                <div class="d-none d-md-block text-end">
                    <a href="{{ route('game.create') }}" class="btn btn-lg px-4" style="background:linear-gradient(90deg,#3a90e8,#1a6fc4);color:#fff;font-weight:600;">
                        <i class="bi bi-plus-lg me-2"></i>Créer une session
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="row gx-3">
        {{-- My Sessions --}}
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-controller me-2 text-theme"></i>Mes sessions</h5>

                    @forelse($mySessions as $s)
                        <a href="{{ route('game.show', $s) }}" class="d-block text-decoration-none mb-2">
                            <div class="rounded-3 p-3" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);transition:all .2s;" onmouseover="this.style.borderColor='var(--bs-theme)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-white">{{ $s->name }}</strong>
                                    <span class="badge {{ $s->status === 'lobby' ? 'bg-warning text-dark' : ($s->status === 'active' ? 'bg-success' : ($s->status === 'paused' ? 'bg-info' : 'bg-secondary')) }}">
                                        {{ ucfirst($s->status) }}
                                    </span>
                                </div>
                                <div class="small text-white-50">
                                    <i class="bi bi-key me-1"></i>{{ $s->code }}
                                    &middot; Scénario {{ $s->scenario }}: {{ $s->scenarioTitle() }}
                                    &middot; Round {{ $s->current_round }}/{{ $s->maxRounds() }}
                                </div>
                                <div class="small text-white-50 mt-1">
                                    <span class="text-primary"><i class="bi bi-shield me-1"></i>Blue: {{ $s->teams->firstWhere('type','blue')?->players->count() ?? 0 }}</span>
                                    &middot;
                                    <span class="text-danger"><i class="bi bi-bug me-1"></i>Red: {{ $s->teams->firstWhere('type','red')?->players->count() ?? 0 }}</span>
                                    @if($s->isModerator(Auth::user()))
                                        &middot; <span class="text-warning"><i class="bi bi-star me-1"></i>Modérateur</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-white-50 py-4">
                            <i class="bi bi-controller" style="font-size:2rem;opacity:.3;"></i>
                            <p class="mt-2 mb-0">Aucune session active</p>
                        </div>
                    @endforelse

                    @if(Auth::user()->isAdmin())
                        <div class="d-md-none mt-3">
                            <a href="{{ route('game.create') }}" class="btn btn-theme w-100">
                                <i class="bi bi-plus-lg me-2"></i>Créer une session
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Available Sessions --}}
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-globe me-2 text-theme"></i>Sessions disponibles</h5>

                    @forelse($sessions as $s)
                        <div class="rounded-3 p-3 mb-2" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-white">{{ $s->name }}</strong>
                                        <span class="badge {{ $s->status === 'lobby' ? 'bg-warning text-dark' : 'bg-success' }}">
                                            {{ ucfirst($s->status) }}
                                        </span>
                                    </div>
                                    <div class="small text-white-50">
                                        <i class="bi bi-person me-1"></i>MJ: {{ $s->moderator->name }}
                                        &middot; Scénario {{ $s->scenario }}: {{ $s->scenarioTitle() }}
                                    </div>
                                    <div class="small mt-1">
                                        <span class="text-primary"><i class="bi bi-shield me-1"></i>Blue: {{ $s->teams->firstWhere('type','blue')?->players->count() ?? 0 }}</span>
                                        &middot;
                                        <span class="text-danger"><i class="bi bi-bug me-1"></i>Red: {{ $s->teams->firstWhere('type','red')?->players->count() ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    @if($s->isModerator(Auth::user()))
                                        <a href="{{ route('game.manage', $s) }}" class="btn btn-sm btn-outline-warning" title="Gérer les équipes">
                                            <i class="bi bi-gear"></i> Gérer
                                        </a>
                                    @endif
                                    @if($s->isModerator(Auth::user()) || $s->playerFor(Auth::user()))
                                        <a href="{{ route('game.show', $s) }}" class="btn btn-sm btn-outline-theme">
                                            <i class="bi bi-box-arrow-in-right"></i> Entrer
                                        </a>
                                    @else
                                        <span class="badge bg-dark text-white-50 align-self-center">Invitation requise</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-white-50 py-4">
                            <i class="bi bi-globe" style="font-size:2rem;opacity:.3;"></i>
                            <p class="mt-2 mb-0">Aucune session disponible</p>
                            <p class="small">Attendez qu'un modérateur vous assigne à une partie</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
