@extends('layouts.app')
@section('title', 'CyberBreach — ' . $session->name)

@push('head')
    <link rel="stylesheet" href="{{ asset('css/cyberbreach-game.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div id="gameContainer">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-2 mt-2 mb-0">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-2 mt-2 mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Toast Container --}}
    <div id="toastContainer" style="position:fixed;top:70px;right:20px;z-index:9999;max-width:350px;"></div>

    {{-- ═══════════════ GAME HEADER BAR ═══════════════ --}}
    <div class="cb-header mx-2 mt-2">
        <div class="d-flex align-items-center gap-3">
            <div class="cb-logo"><span class="blue">CYBER</span><span class="red">BREACH</span></div>
            <span id="cbStatus" class="badge bg-warning text-dark">{{ strtoupper($session->status) }}</span>
            <span class="text-white-50 small"><i class="bi bi-key me-1"></i><span id="cbCode">{{ $session->code }}</span></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="cb-phase-indicator">
                <i class="bi bi-layers"></i>
                <span id="cbRound">Round {{ $session->current_round }}/{{ $session->maxRounds() }}</span>
                <span class="text-white-50">|</span>
                <span id="cbPhase">{{ \App\Models\GameSession::phaseLabel($session->current_phase) }}</span>
            </div>
            <div class="cb-timer" id="cbTimer">--:--</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-1">
                <span class="fw-bold" style="color:#3a90e8;" id="cbBlueScore">{{ $session->blueTeam?->score ?? 0 }}</span>
                <span class="text-white-50 small">vs</span>
                <span class="fw-bold" style="color:#e83a3a;" id="cbRedScore">{{ $session->redTeam?->score ?? 0 }}</span>
            </div>
            <form method="POST" action="{{ route('game.leave', $session) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Quitter la session ?')">
                    <i class="bi bi-box-arrow-left"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-2 mx-1 mb-2">
        {{-- ═══════════════ LEFT: BLUE TEAM ═══════════════ --}}
        <div class="col-lg-3 col-md-4">
            <div class="cb-team-panel blue h-100">
                <div class="cb-team-title blue mb-2">
                    <i class="bi bi-shield-lock me-1"></i> BLUE TEAM — Défense
                </div>

                <div class="d-flex gap-3 mb-2">
                    <div class="cb-stat">
                        <i class="bi bi-coin"></i>
                        <span class="cb-stat-value" id="blueTokens">{{ $session->blueTeam?->tokens ?? 4 }}</span>
                        <span class="small">jetons</span>
                    </div>
                    <div class="cb-stat">
                        <i class="bi bi-shop"></i>
                        <span class="cb-stat-value" id="blueShopTokens">{{ $session->blueTeam?->shop_tokens ?? 20 }}</span>
                        <span class="small">shop</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="cb-stat">
                        <i class="bi bi-trophy"></i>
                        <span class="cb-stat-value" id="blueScore">{{ $session->blueTeam?->score ?? 0 }}</span>
                        <span class="small">pts</span>
                    </div>
                    <div class="cb-stat">
                        <i class="bi bi-stack"></i>
                        <span class="cb-stat-value" id="blueHandCount">0</span>
                        <span class="small">cartes</span>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="small text-white-50 mb-1"><i class="bi bi-people me-1"></i>Joueurs</div>
                    <div id="bluePlayers">
                        @foreach($session->blueTeam?->players ?? [] as $p)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="cb-player-avatar" style="background:#3a90e8;">{{ $p->user->initials() }}</div>
                                <span class="small">{{ $p->user->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="small text-white-50 mb-1"><i class="bi bi-shield-check me-1"></i>Cartes actives</div>
                    <div id="blueActive"></div>
                </div>
            </div>
        </div>

        {{-- ═══════════════ CENTER: BOARD ═══════════════ --}}
        <div class="col-lg-6 col-md-4">
            {{-- Infrastructure Map --}}
            <div class="card mb-2" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-white-50"><i class="bi bi-diagram-3 me-1"></i>Infrastructure DevCo</span>
                        <span class="small text-white-50">Scénario {{ $session->scenario }}: {{ $session->scenarioTitle() }}</span>
                    </div>
                    <div class="cb-infra-grid">
                        @foreach($systems as $key => $zone)
                            <div class="cb-zone" id="zone-{{ $key }}">
                                <div class="cb-zone-title">{{ $zone['name'] }}</div>
                                @foreach($zone['nodes'] as $node)
                                    <div class="cb-node" data-node="{{ $node }}">
                                        <span>{{ $node }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Event Display --}}
            <div id="eventDisplay" style="display:none;" class="mb-2"></div>

            {{-- Score Comparison Bar --}}
            <div class="card mb-2" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small" style="color:#3a90e8;"><i class="bi bi-shield me-1"></i>Blue <strong id="blueScoreBar">0</strong></span>
                        <span class="small text-white-50">Score</span>
                        <span class="small" style="color:#e83a3a;"><strong id="redScoreBar">0</strong> Red <i class="bi bi-bug ms-1"></i></span>
                    </div>
                    <div class="d-flex gap-1">
                        <div class="cb-score-bar flex-fill">
                            <div class="cb-score-fill blue" id="blueScoreFill" style="width:50%;"></div>
                        </div>
                        <div class="cb-score-bar flex-fill" style="direction:rtl;">
                            <div class="cb-score-fill red" id="redScoreFill" style="width:50%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Log --}}
            <div class="card" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
                <div class="card-body p-2">
                    <div class="small fw-bold text-white-50 mb-1"><i class="bi bi-clock-history me-1"></i>Journal d'actions</div>
                    <div id="actionLog" class="cb-log">
                        <div class="text-white-50 small p-2 text-center">Aucune action ce round</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════ RIGHT: RED TEAM + MOD ═══════════════ --}}
        <div class="col-lg-3 col-md-4">
            {{-- Red Team Panel --}}
            <div class="cb-team-panel red mb-2">
                <div class="cb-team-title red mb-2">
                    <i class="bi bi-bug me-1"></i> RED TEAM — Attaque
                </div>

                <div class="d-flex gap-3 mb-2">
                    <div class="cb-stat">
                        <i class="bi bi-coin"></i>
                        <span class="cb-stat-value" id="redTokens">{{ $session->redTeam?->tokens ?? 4 }}</span>
                        <span class="small">jetons</span>
                    </div>
                    <div class="cb-stat">
                        <i class="bi bi-shop"></i>
                        <span class="cb-stat-value" id="redShopTokens">{{ $session->redTeam?->shop_tokens ?? 20 }}</span>
                        <span class="small">shop</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="cb-stat">
                        <i class="bi bi-trophy"></i>
                        <span class="cb-stat-value" id="redScore">{{ $session->redTeam?->score ?? 0 }}</span>
                        <span class="small">pts</span>
                    </div>
                    <div class="cb-stat">
                        <i class="bi bi-stack"></i>
                        <span class="cb-stat-value" id="redHandCount">0</span>
                        <span class="small">cartes</span>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="small text-white-50 mb-1"><i class="bi bi-people me-1"></i>Joueurs</div>
                    <div id="redPlayers">
                        @foreach($session->redTeam?->players ?? [] as $p)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="cb-player-avatar" style="background:#e83a3a;">{{ $p->user->initials() }}</div>
                                <span class="small">{{ $p->user->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="small text-white-50 mb-1"><i class="bi bi-bug me-1"></i>Cartes actives</div>
                    <div id="redActive"></div>
                </div>
            </div>

            {{-- Moderator Panel --}}
            <div id="modPanel" class="cb-mod-panel" style="{{ $isMod ? '' : 'display:none;' }}">
                <div class="cb-mod-title"><i class="bi bi-star-fill me-1"></i> Panneau Modérateur</div>

                <div class="d-grid gap-2 mb-2">
                    <button id="btnDealHands" class="btn btn-sm btn-outline-warning" onclick="game.dealHands().then(r => r.success && game.showToast('Cartes distribuées','success'))">
                        <i class="bi bi-stack me-1"></i>Distribuer les mains
                    </button>
                    <button id="btnStartRound" class="btn btn-sm btn-outline-success" onclick="game.startRound().then(r => r.success && game.showToast('Round démarré','success'))">
                        <i class="bi bi-play-fill me-1"></i>Démarrer le round
                    </button>
                    <button id="btnAdvancePhase" class="btn btn-sm btn-outline-info" onclick="game.advancePhase().then(r => game.showToast(r.label || r.error || 'Phase avancée', r.success ? 'info' : 'danger'))">
                        <i class="bi bi-skip-forward me-1"></i>Phase suivante
                    </button>
                    <button id="btnDrawEvent" class="btn btn-sm btn-outline-danger" onclick="game.drawEvent().then(r => r.success && game.showToast('Événement tiré !','warning'))">
                        <i class="bi bi-lightning me-1"></i>Tirer événement
                    </button>
                </div>

                <div class="small text-white-50 mb-1">Ajuster jetons (±2)</div>
                <div class="d-flex gap-1 mb-1">
                    <button class="btn btn-xs btn-outline-primary flex-fill" onclick="game.adjustTokens('blue',2)" style="font-size:.7rem;">Blue +2</button>
                    <button class="btn btn-xs btn-outline-primary flex-fill" onclick="game.adjustTokens('blue',-2)" style="font-size:.7rem;">Blue -2</button>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-xs btn-outline-danger flex-fill" onclick="game.adjustTokens('red',2)" style="font-size:.7rem;">Red +2</button>
                    <button class="btn btn-xs btn-outline-danger flex-fill" onclick="game.adjustTokens('red',-2)" style="font-size:.7rem;">Red -2</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════ BOTTOM: MY HAND ═══════════════ --}}
    <div class="mx-2 mb-2">
        <div class="card" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-white-50">
                        <i class="bi bi-hand-index me-1"></i>Ma main
                        @if($player)
                            <span class="badge {{ $player->team->isBlue() ? 'bg-primary' : 'bg-danger' }} ms-1">{{ $player->team->label() }}</span>
                        @endif
                    </span>
                    <div class="d-flex gap-1">
                        @if($player)
                            <button class="btn btn-sm btn-outline-theme" onclick="game.drawCard().then(r => r.success ? game.showToast('Carte piochée','success') : game.showToast(r.error || 'Erreur','danger'))">
                                <i class="bi bi-plus-circle me-1"></i>Piocher (1 jeton)
                            </button>
                        @endif
                    </div>
                </div>
                <div id="myHand" class="cb-hand">
                    <div class="text-white-50 small p-3">
                        @if($player)
                            Chargement des cartes...
                        @elseif($isMod)
                            <i class="bi bi-star-fill text-warning me-1"></i> Vous êtes le modérateur
                        @else
                            Rejoignez une équipe pour jouer
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /gameContainer --}}

{{-- ═══════════════ PLAY CARD MODAL ═══════════════ --}}
<div class="modal fade" id="playCardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-bottom-0">
                <h6 class="modal-title" style="font-family:'Space Mono',monospace;">
                    <i class="bi bi-play-circle me-2 text-theme"></i>Jouer une carte
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="text-white-50">Carte:</span>
                    <strong id="playCardName" class="text-white ms-1"></strong>
                </div>
                <div class="mb-3">
                    <span class="text-white-50">Coût:</span>
                    <strong id="playCardCost" class="text-warning ms-1"></strong>
                    <span class="text-white-50 ms-1">jetons</span>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-white-50">Système cible (optionnel)</label>
                    <select id="targetSystem" class="form-select form-select-sm">
                        <option value="">— Aucun —</option>
                        @foreach($systems as $key => $zone)
                            <optgroup label="{{ $zone['name'] }}">
                                @foreach($zone['nodes'] as $node)
                                    <option value="{{ $node }}">{{ $node }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-theme btn-sm" onclick="game.confirmPlay()">
                    <i class="bi bi-check-lg me-1"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cyberbreach-game.js') }}"></script>
<script>
    const game = new CyberBreachGame({{ $session->id }}, '{{ csrf_token() }}');
    document.addEventListener('DOMContentLoaded', () => game.init());
    window.addEventListener('beforeunload', () => game.destroy());
</script>
@endpush
