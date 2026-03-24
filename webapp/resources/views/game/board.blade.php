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
            @if($isMod)
                <a href="{{ route('game.manage', $session) }}" class="btn btn-sm btn-outline-warning" title="Gérer les équipes">
                    <i class="bi bi-gear"></i>
                </a>
            @endif
            <form method="POST" action="{{ route('game.leave', $session) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Quitter la session ?')">
                    <i class="bi bi-box-arrow-left"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- ═══════════════ TURN BANNER ═══════════════ --}}
    <div class="mx-2">
        <div id="turnBanner" class="cb-turn-banner neutral">
            <i class="bi bi-hourglass me-2"></i>EN ATTENTE
        </div>
    </div>

    <div class="row g-2 mx-1 mb-2">
        {{-- ═══════════════ LEFT: BLUE TEAM ═══════════════ --}}
        <div class="col-lg-3 col-md-4">
            <div class="cb-team-panel blue h-100" id="bluePanel">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="cb-team-title blue">
                        <i class="bi bi-shield-lock me-1"></i> BLUE TEAM — Défense
                    </div>
                    <span id="blueTurnBadge" class="cb-turn-badge" style="display:none;background:#3a90e8;color:#fff;">JOUE</span>
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
                    <div id="blueActive"><div class="small text-white-50">Aucune</div></div>
                </div>
            </div>
        </div>

        {{-- ═══════════════ CENTER: BOARD ═══════════════ --}}
        <div class="col-lg-6 col-md-4">
            {{-- Interactive Network Map --}}
            <div class="card mb-2" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-white-50"><i class="bi bi-diagram-3 me-1"></i>Infrastructure DevCo</span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="small text-white-50 me-2">Scénario {{ $session->scenario }}: {{ $session->scenarioTitle() }}</span>
                            <button id="btnLayoutToggle" class="btn btn-xs btn-outline-info me-2" onclick="game.toggleNetworkLayout()" title="Vue Network (Organique)"><i class="bi bi-diagram-3"></i></button>
                            <button class="btn btn-xs btn-outline-secondary" onclick="game.zoomIn()" title="Zoom +"><i class="bi bi-zoom-in"></i></button>
                            <button class="btn btn-xs btn-outline-secondary" onclick="game.zoomOut()" title="Zoom −"><i class="bi bi-zoom-out"></i></button>
                            <button class="btn btn-xs btn-outline-secondary" onclick="game.zoomFit()" title="Ajuster"><i class="bi bi-arrows-fullscreen"></i></button>
                        </div>
                    </div>
                    <div id="networkMap"></div>
                    <div class="d-flex gap-3 mt-2 justify-content-center flex-wrap">
                        <span class="small text-white-50"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#3a90e8;"></span> Sécurisé</span>
                        <span class="small text-white-50"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#e83a3a;"></span> Compromis</span>
                        <span class="small text-white-50"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#2d9f4f;"></span> Défendu</span>
                        <span class="small text-white-50"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#fbbf24;"></span> 100% + Critique</span>
                        <span class="small text-white-50"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#6366f1;"></span> 80%</span>
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
            <div class="cb-team-panel red mb-2" id="redPanel">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="cb-team-title red">
                        <i class="bi bi-bug me-1"></i> RED TEAM — Attaque
                    </div>
                    <span id="redTurnBadge" class="cb-turn-badge" style="display:none;background:#e83a3a;color:#fff;">JOUE</span>
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
                    <div id="redActive"><div class="small text-white-50">Aucune</div></div>
                </div>
            </div>

            {{-- Moderator Panel --}}
            <div id="modPanel" class="cb-mod-panel" style="{{ $isMod ? '' : 'display:none;' }}">
                <div class="cb-mod-title"><i class="bi bi-star-fill me-1"></i> Panneau Modérateur</div>

                <div class="d-grid gap-2 mb-2">
                    <button id="btnDealHands" class="btn btn-sm btn-outline-warning" onclick="game.dealHands().then(r => r.success && game.showToast('Cartes distribuées','success'))">
                        <i class="bi bi-stack me-1"></i>Distribuer les mains
                    </button>
                    <button id="btnStartRound" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modStartRoundModal">
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

                <hr style="border-color:rgba(217,119,6,.2);margin:.75rem 0;">
                <button class="btn btn-sm btn-danger w-100" onclick="if(confirm('Terminer la partie maintenant ? Cette action est irréversible.')) game.endGame()">
                    <i class="bi bi-stop-circle me-1"></i>Terminer la partie
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════ BOTTOM: MY HAND + DROP ZONE ═══════════════ --}}
    <div class="mx-2 mb-2">
        <div class="card" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.08);">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-white-50">
                        <i class="bi bi-hand-index me-1"></i>Ma main
                        @if($player)
                            <span class="badge {{ $player->team->isBlue() ? 'bg-primary' : 'bg-danger' }} ms-1">{{ $player->team->label() }}</span>
                        @endif
                        <span class="text-white-50 ms-2" style="font-size:.7rem;">1. cliquer une carte &middot; 2. cliquer un noeud sur la map &middot; double-clic = retourner</span>
                    </span>
                    <div class="d-flex gap-1">
                        @if($player)
                            <button class="btn btn-sm btn-outline-warning" onclick="game.playWithoutTarget()" title="Jouer sans cible">
                                <i class="bi bi-slash-circle me-1"></i>Sans cible
                            </button>
                            <button class="btn btn-sm btn-outline-theme" onclick="game.drawCard().then(r => r.success ? game.showToast('Carte piochée','success') : game.showToast(r.error || 'Erreur','danger'))">
                                <i class="bi bi-plus-circle me-1"></i>Piocher (1 jeton)
                            </button>
                        @endif
                    </div>
                </div>
                <div id="myHand" class="cb-hand">
                    @if($player)
                        <div class="text-white-50 small p-3">Chargement des cartes...</div>
                    @elseif($isMod)
                        <div class="text-white-50 small p-3"><i class="bi bi-star-fill text-warning me-1"></i> Vous êtes le modérateur</div>
                    @else
                        <div class="text-white-50 small p-3">Vous n'êtes pas assigné à une équipe</div>
                    @endif
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
                <div class="mb-3">
                    <span class="text-white-50">Cible:</span>
                    <strong id="playTargetInfo" class="text-white ms-1">—</strong>
                </div>
                <div class="mb-3 p-2 rounded" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                    <span class="text-white-50 small">Efficacité:</span>
                    <div id="playEffectiveness" class="mt-1"></div>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-white-50">Modifier la cible</label>
                    <select id="targetSystem" class="form-select form-select-sm">
                        <option value="">— Aucune cible —</option>
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

{{-- ═══════════════ MODERATOR START ROUND MODAL ═══════════════ --}}
<div class="modal fade" id="modStartRoundModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1a1a2e;border:1px solid rgba(255,255,255,.1);">
            <div class="modal-header border-bottom-0">
                <h6 class="modal-title" style="font-family:'Space Mono',monospace;">
                    <i class="bi bi-gear me-2 text-warning"></i>Configuration du Round
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-white-50 mb-3">Définissez les ressources qui seront distribuées aux deux équipes pour ce round.</p>
                
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Nombre de cartes à distribuer (par équipe)</label>
                    <input type="number" class="form-control" id="modCardsToDeal" value="2" min="0" max="10" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);color:white;">
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-white-50 small">Nombre de jetons d'action à distribuer (par équipe)</label>
                    <input type="number" class="form-control" id="modTokensToDeal" value="3" min="0" max="20" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.1);color:white;">
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success btn-sm" onclick="game.confirmStartRound()">
                    <i class="bi bi-play-fill me-1"></i>Lancer le round
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ VICTORY MODAL ═══════════════ --}}
<div class="modal fade" id="victoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-center" style="background:#0a0a0f;border:2px solid;border-color:#3a90e8;box-shadow:0 0 40px rgba(0,0,0,0.8);" id="victoryModalContent">
            <div class="modal-body py-5">
                <div class="mb-4">
                    <span class="blue" style="font-size:2rem;font-weight:900;">CYBER</span><span class="red" style="font-size:2rem;font-weight:900;">BREACH</span>
                </div>
                
                <h1 class="display-3 fw-bold mb-2 text-white" style="font-family:'Space Mono',monospace;text-transform:uppercase;" id="victoryTitle">
                    Gagnant !
                </h1>
                <p class="lead text-white-50 mb-5" id="victorySubtitle">Le scénario est terminé.</p>

                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(58,144,232,.1);border:1px solid #3a90e8;">
                            <h4 class="text-white mb-1"><i class="bi bi-shield-lock me-2" style="color:#3a90e8;"></i> BLUE TEAM</h4>
                            <div class="display-5 fw-bold" style="color:#3a90e8;" id="victoryBlueScore">0</div>
                            <div class="small text-white-50">Points</div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <div class="fw-bold text-white-50" style="font-size:1.5rem;">VS</div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:rgba(232,58,58,.1);border:1px solid #e83a3a;">
                            <h4 class="text-white mb-1">RED TEAM <i class="bi bi-bug ms-2" style="color:#e83a3a;"></i></h4>
                            <div class="display-5 fw-bold" style="color:#e83a3a;" id="victoryRedScore">0</div>
                            <div class="small text-white-50">Points</div>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <a href="{{ route('game.lobby') }}" class="btn btn-outline-light px-4">Retour au lobby</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- vis-network for infrastructure graph --}}
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
{{-- Game engine --}}
<script src="{{ asset('js/cyberbreach-game.js') }}?v={{ time() }}"></script>
<script>
    const game = new CyberBreachGame({{ $session->id }}, '{{ csrf_token() }}', '{{ $isMod ? "moderator" : ($player ? "player" : "spectator") }}', {{ $session->scenario ?? 1 }});
    document.addEventListener('DOMContentLoaded', () => game.init());
    window.addEventListener('beforeunload', () => game.destroy());
</script>
@endpush
