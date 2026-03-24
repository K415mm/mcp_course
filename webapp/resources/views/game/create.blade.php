@extends('layouts.app')
@section('title', 'CyberBreach — Créer une session')

@section('content')
<div class="container py-4" style="max-width:720px;">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('game.lobby') }}">CyberBreach</a></li>
            <li class="breadcrumb-item active">Nouvelle session</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
        <div class="card-body p-4">
            <h4 class="mb-1" style="font-family:'Space Mono',monospace;">
                <span style="color:#3a90e8;">CYBER</span><span style="color:#e83a3a;">BREACH</span>
                <span class="text-white-50 fw-normal" style="font-size:.9rem;"> — Nouvelle session</span>
            </h4>
            <p class="text-white-50 small mb-4">Configurez votre exercice de cybersécurité tabletop</p>

            <form method="POST" action="{{ route('game.store') }}">
                @csrf

                {{-- Game Name --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Nom de la session</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Ex: Session Ynov — Mars 2026" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Scenario Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Scénario</label>
                    <div class="row g-2">
                        @php
                            $scenarios = [
                                1 => ['title' => 'Opération NightOwl', 'level' => 'Intermédiaire', 'color' => '#3a90e8', 'desc' => 'Phishing d\'un dev senior → credentials SSO volés → pivot CI/CD → exfiltration S3'],
                                2 => ['title' => 'Supply Chain Poisoning', 'level' => 'Avancé', 'color' => '#e83a3a', 'desc' => 'Package npm empoisonné → 8 microservices touchés → credentials exfiltrés'],
                                3 => ['title' => 'Insider Threat', 'level' => 'Expert', 'color' => '#d97706', 'desc' => 'Ex-employé licencié → backdoors AWS → code source exfiltré → fuite concurrentielle'],
                                4 => ['title' => 'Zero-Day API', 'level' => 'Avancé', 'color' => '#7c6cc5', 'desc' => 'CVE critique CVSS 10.0 → PoC publique → course contre la montre pour patcher'],
                                5 => ['title' => 'BEC Attack', 'level' => 'Intermédiaire', 'color' => '#20c997', 'desc' => 'CEO usurpé → factures frauduleuses → vol financier'],
                                6 => ['title' => 'Ransomware Total', 'level' => 'Avancé', 'color' => '#dc3545', 'desc' => 'LockBit → chiffrement massif → backups détruits → double extorsion'],
                                7 => ['title' => 'APT ciblée (Lazarus)', 'level' => 'Expert', 'color' => '#fd7e14', 'desc' => 'Infiltration étatique (6 mois) → DNS C2 → vol de propriété intellectuelle'],
                                8 => ['title' => 'Attaque Industrielle', 'level' => 'Expert', 'color' => '#6610f2', 'desc' => 'Pivot IT/OT → automates SCADA/PLC compromis → sabotage physique'],
                            ];
                        @endphp

                        @foreach($scenarios as $num => $s)
                            <div class="col-md-6">
                                <label class="d-block cursor-pointer">
                                    <input type="radio" name="scenario" value="{{ $num }}" class="d-none scenario-radio" {{ old('scenario', 1) == $num ? 'checked' : '' }}>
                                    <div class="scenario-card rounded-3 p-3 h-100" style="border:2px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);transition:all .2s;cursor:pointer;" data-color="{{ $s['color'] }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold" style="color:{{ $s['color'] }}">{{ $s['title'] }}</span>
                                            <span class="badge" style="background:{{ $s['color'] }}20;color:{{ $s['color'] }};font-size:.7rem;">{{ $s['level'] }}</span>
                                        </div>
                                        <p class="text-white-50 mb-0" style="font-size:.78rem;line-height:1.4;">{{ $s['desc'] }}</p>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('scenario')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Advanced Settings --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre de rounds</label>
                        <select name="max_rounds" class="form-select">
                            @for($i = 4; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ old('max_rounds', 8) == $i ? 'selected' : '' }}>{{ $i }} rounds</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Timer par round</label>
                        <select name="timer_seconds" class="form-select">
                            <option value="600" {{ old('timer_seconds', 900) == 600 ? 'selected' : '' }}>10 minutes</option>
                            <option value="900" {{ old('timer_seconds', 900) == 900 ? 'selected' : '' }}>15 minutes</option>
                            <option value="1200" {{ old('timer_seconds', 900) == 1200 ? 'selected' : '' }}>20 minutes</option>
                            <option value="1800" {{ old('timer_seconds', 900) == 1800 ? 'selected' : '' }}>30 minutes</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn px-4" style="background:linear-gradient(90deg,#3a90e8,#1a6fc4);color:#fff;font-weight:600;">
                        <i class="bi bi-rocket-takeoff me-2"></i>Lancer la session
                    </button>
                    <a href="{{ route('game.lobby') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCards() {
        document.querySelectorAll('.scenario-card').forEach(card => {
            const radio = card.closest('label').querySelector('.scenario-radio');
            const color = card.dataset.color;
            if (radio.checked) {
                card.style.borderColor = color;
                card.style.background = color + '10';
            } else {
                card.style.borderColor = 'rgba(255,255,255,.1)';
                card.style.background = 'rgba(255,255,255,.03)';
            }
        });
    }
    document.querySelectorAll('.scenario-radio').forEach(r => r.addEventListener('change', updateCards));
    updateCards();
});
</script>
@endpush
