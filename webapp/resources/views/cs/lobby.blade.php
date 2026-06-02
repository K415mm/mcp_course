@extends('layouts.app')
@php
    use App\Models\CsScenario;
    $hasEnSession = collect($mySessions)->contains(fn($s) => (CsScenario::find($s->scenario_key)['language'] ?? 'fr') === 'en');
@endphp
@section('title', $hasEnSession ? 'My Sessions — Tabletop Simulation' : 'Mes Sessions — Carthage Shield')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header mb-0">
            <i class="bi bi-shield-fill-exclamation text-theme me-2"></i>{{ $hasEnSession ? 'My Sessions' : 'Mes Sessions Carthage Shield' }}
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-controller me-2 text-theme"></i>{{ $hasEnSession ? 'Active Sessions' : 'Sessions en cours' }}</h5>

            @forelse($mySessions as $s)
                @php
                    $scenario = CsScenario::find($s->scenario_key);
                    $isEn = ($scenario['language'] ?? 'fr') === 'en';
                @endphp
                <div class="rounded-3 p-3 mb-2" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08); transition:all .2s;" onmouseover="this.style.borderColor='var(--bs-theme)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-white fs-5">{{ $s->name }}</strong>
                                <span class="badge {{ $s->status === 'lobby' ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ strtoupper($s->status) }}
                                </span>
                            </div>
                            <div class="small text-white-50">
                                <i class="bi bi-person me-1"></i>{{ $isEn ? 'Moderator' : 'Modérateur' }} : {{ $s->moderator->name }}
                                &middot; {{ $isEn ? 'Scenario' : 'Scénario' }} : {{ $scenario['title'] ?? $s->scenario_key }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('cs.show', $s->code) }}" class="btn btn-outline-theme">
                                <i class="bi bi-people me-1"></i> {{ $isEn ? 'Team Dashboard' : 'Dashboard Équipe' }}
                            </a>
                            <a href="{{ route('cs.dashboard', $s->code) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="bi bi-display me-1"></i> {{ $isEn ? 'Grand Screen' : 'Grand Écran' }}
                            </a>
                            @if($s->moderator_id === Auth::id() || Auth::user()->isAdmin())
                                <a href="{{ route('cs.moderator', $s->code) }}" class="btn btn-outline-warning">
                                    <i class="bi bi-sliders me-1"></i> {{ $isEn ? 'Moderator Console' : 'Modérateur' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-white-50 py-5">
                    <i class="bi bi-shield-slash" style="font-size:2.5rem;opacity:.3;"></i>
                    @if($hasEnSession)
                        <p class="mt-2 mb-0">You are not assigned to any active session.</p>
                        <p class="small">Please wait for a moderator to add you to an exercise.</p>
                    @else
                        <p class="mt-2 mb-0">Vous n'êtes assigné à aucune session active.</p>
                        <p class="small">Veuillez attendre qu'un modérateur vous ajoute à un exercice.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
