@extends('layouts.app')
@php
    use App\Models\NeptuneScenario;
@endphp
@section('title', 'My Sessions — Neptune Strike')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header mb-0">
            <i class="bi bi-anchor text-theme me-2"></i>My Sessions
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
            <h5 class="card-title mb-3"><i class="bi bi-controller me-2 text-theme"></i>Active Sessions</h5>

            @forelse($mySessions as $s)
                @php
                    $scenario = NeptuneScenario::find($s->scenario_key);
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
                                <i class="bi bi-person me-1"></i>Moderator: {{ $s->moderator->name }}
                                &middot; Scenario: {{ $scenario['title'] ?? $s->scenario_key }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('neptune.show', $s->code) }}" class="btn btn-outline-theme">
                                <i class="bi bi-people me-1"></i> Team Dashboard
                            </a>
                            <a href="{{ route('neptune.dashboard', $s->code) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="bi bi-display me-1"></i> Grand Screen
                            </a>
                            @if($s->moderator_id === Auth::id() || Auth::user()->isAdmin())
                                <a href="{{ route('neptune.moderator', $s->code) }}" class="btn btn-outline-warning">
                                    <i class="bi bi-sliders me-1"></i> Moderator Console
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-white-50 py-5">
                    <i class="bi bi-shield-slash" style="font-size:2.5rem;opacity:.3;"></i>
                    <p class="mt-2 mb-0">You are not assigned to any active session.</p>
                    <p class="small">Please wait for a moderator to add you to an exercise.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
