<?php

namespace App\Services;

use App\Models\GameCard;
use App\Models\GameCardPlay;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\GameTeamCard;
use App\Models\User;
use App\Services\CardEffectivenessMatrix;

class GameService
{
    // ── Session Management ──────────────────────────────────────────

    public function createSession(User $moderator, array $data): GameSession
    {
        $session = GameSession::create([
            'name'         => $data['name'],
            'scenario'     => $data['scenario'] ?? 1,
            'moderator_id' => $moderator->id,
            'settings'     => [
                'max_rounds'    => $data['max_rounds'] ?? 8,
                'timer_seconds' => $data['timer_seconds'] ?? 900,
            ],
        ]);

        // Create both teams
        $session->teams()->createMany([
            ['type' => 'blue', 'tokens' => 4, 'shop_tokens' => 20, 'score' => 0],
            ['type' => 'red',  'tokens' => 4, 'shop_tokens' => 20, 'score' => 0],
        ]);

        return $session->load('teams');
    }

    public function joinTeam(GameSession $session, User $user, string $teamType): GameTeam
    {
        $team = $session->teams()->where('type', $teamType)->firstOrFail();

        // Remove from any existing team in this session
        $session->players()->where('user_id', $user->id)->delete();

        $team->players()->create([
            'game_session_id' => $session->id,
            'user_id'         => $user->id,
            'is_captain'      => $team->players()->count() === 0, // first member is captain
        ]);

        return $team;
    }

    public function leaveSession(GameSession $session, User $user): void
    {
        $session->players()->where('user_id', $user->id)->delete();
    }

    // ── Round Management ────────────────────────────────────────────

    public function startRound(GameSession $session): GameRound
    {
        $nextRound = $session->current_round + 1;

        $round = GameRound::create([
            'game_session_id' => $session->id,
            'round_number'    => $nextRound,
            'phase'           => 1,
            'started_at'      => now(),
        ]);

        $session->update([
            'status'        => 'active',
            'current_round' => $nextRound,
            'current_phase' => 1,
        ]);

        // Distribute resources to both teams
        foreach ($session->teams as $team) {
            $team->distributeRoundResources();
        }

        // Tick down active card durations
        $this->tickActiveCardDurations($session);

        return $round;
    }

    public function advancePhase(GameSession $session): int
    {
        $nextPhase = $session->current_phase + 1;

        if ($nextPhase > 5) {
            // Round is over
            $round = $session->currentRound();
            if ($round) {
                $round->update(['ended_at' => now()]);
            }

            // Check if game is finished
            if ($session->current_round >= $session->maxRounds()) {
                $session->update(['status' => 'finished', 'current_phase' => 5]);
                return -1; // game over
            }

            return 0; // round over, need to start next round
        }

        $session->update(['current_phase' => $nextPhase]);

        $round = $session->currentRound();
        if ($round) {
            $round->update(['phase' => $nextPhase]);
        }

        return $nextPhase;
    }

    // ── Card Play ───────────────────────────────────────────────────

    public function playCard(
        GameSession $session,
        GameTeam $team,
        GameTeamCard $teamCard,
        User $player,
        ?string $targetSystem = null
    ): array {
        $card = $teamCard->card;

        // Validate cost
        if (!$team->canAfford($card->cost)) {
            return ['success' => false, 'error' => 'Jetons insuffisants'];
        }

        // Validate phase
        if (!$this->isValidPhaseForTeam($session, $team)) {
            return ['success' => false, 'error' => "Ce n'est pas le tour de votre équipe"];
        }

        // Spend tokens
        $team->spendTokens($card->cost);

        // Calculate points using effectiveness matrix
        $calc = CardEffectivenessMatrix::calculate(
            $card->name, $card->type, $card->points, $targetSystem, $session->scenario
        );
        $points = $calc['points'];
        $team->addScore($points);

        // Update node state if target specified
        if ($targetSystem) {
            $nodeId = CardEffectivenessMatrix::nodeId($targetSystem);
            if ($nodeId && $calc['effectiveness'] > 0) {
                $newState = $team->isRed() ? 'compromised' : 'defended';
                $this->setNodeState($session, $nodeId, $newState);
            }
        }

        // Move card from hand to active/used
        if ($card->duration) {
            $duration = $this->parseDuration($card->duration);
            $teamCard->markActive($duration);
        } else {
            $teamCard->markUsed();
        }

        // Log the play
        $round = $session->currentRound();
        $play = GameCardPlay::create([
            'game_round_id' => $round->id,
            'game_team_id'  => $team->id,
            'game_card_id'  => $card->id,
            'user_id'       => $player->id,
            'target_system' => $targetSystem,
            'points_earned' => $points,
            'played_at'     => now(),
        ]);

        return [
            'success'       => true,
            'points'        => $points,
            'effectiveness' => $calc['effectiveness'],
            'isCritical'    => $calc['isCriticalPath'],
            'message'       => $calc['message'],
            'play'          => $play,
        ];
    }

    // ── Draw Card ───────────────────────────────────────────────────

    public function drawCard(GameTeam $team): ?GameTeamCard
    {
        // Costs 1 token to draw
        if (!$team->spendTokens(1)) {
            return null;
        }

        // Check hand size limit
        if ($team->handCount() >= GameTeam::MAX_HAND_SIZE) {
            $team->addTokens(1); // refund
            return null;
        }

        // Get a random card from the catalog for this team type
        $card = GameCard::where(function ($q) use ($team) {
            $q->where('type', $team->type);
        })
        ->whereNotIn('id', $team->cards()->pluck('game_card_id'))
        ->inRandomOrder()
        ->first();

        if (!$card) {
            $team->addTokens(1); // refund
            return null;
        }

        return $team->cards()->create([
            'game_card_id'   => $card->id,
            'status'         => 'hand',
            'acquired_round' => $team->session->current_round,
        ]);
    }

    // ── Draw Event Card (Moderator) ─────────────────────────────────

    public function drawEventCard(GameSession $session): ?GameCard
    {
        $round = $session->currentRound();
        if (!$round) return null;

        // Get a random event card not yet drawn this game
        $drawnIds = $session->rounds()
            ->whereNotNull('event_card')
            ->get()
            ->pluck('event_card.id')
            ->filter();

        $event = GameCard::events()
            ->whereNotIn('id', $drawnIds)
            ->inRandomOrder()
            ->first();

        if (!$event) return null;

        // Store event in round
        $round->update([
            'event_card' => [
                'id'          => $event->id,
                'name'        => $event->name,
                'subtype'     => $event->subtype,
                'description' => $event->description,
                'effect'      => $event->effect,
            ],
        ]);

        return $event;
    }

    // ── Shop Buy ────────────────────────────────────────────────────

    public function buyFromShop(GameTeam $team, int $cardId): array
    {
        $card = GameCard::find($cardId);
        if (!$card) {
            return ['success' => false, 'error' => 'Carte introuvable'];
        }

        // Shop items have their cost in the 'data.shop_price' field or we use cost
        $price = $card->data['shop_price'] ?? $card->cost;

        if (!$team->spendShopTokens($price)) {
            return ['success' => false, 'error' => 'Jetons boutique insuffisants'];
        }

        if ($team->handCount() >= GameTeam::MAX_HAND_SIZE) {
            $team->addShopTokens($price); // refund
            return ['success' => false, 'error' => 'Main pleine (max 10 cartes)'];
        }

        $teamCard = $team->cards()->create([
            'game_card_id'   => $card->id,
            'status'         => 'hand',
            'acquired_round' => $team->session->current_round,
        ]);

        return ['success' => true, 'card' => $teamCard->load('card')];
    }

    // ── Token Adjustment (MJ) ───────────────────────────────────────

    public function adjustTokens(GameTeam $team, int $amount): void
    {
        // MJ can adjust ±2
        $amount = max(-2, min(2, $amount));
        if ($amount > 0) {
            $team->addTokens($amount);
        } else {
            $team->decrement('tokens', abs($amount));
            $team->tokens = max(0, $team->fresh()->tokens);
            $team->save();
        }
    }

    // ── Deal Initial Hands ──────────────────────────────────────────

    public function dealInitialHands(GameSession $session): void
    {
        foreach ($session->teams as $team) {
            $cards = GameCard::where('type', $team->type)
                ->inRandomOrder()
                ->limit(GameTeam::STARTING_HAND_SIZE)
                ->get();

            foreach ($cards as $card) {
                $team->cards()->create([
                    'game_card_id'   => $card->id,
                    'status'         => 'hand',
                    'acquired_round' => 0,
                ]);
            }
        }
    }

    // ── Full Game State (for API/reconnect) ─────────────────────────

    public function getGameState(GameSession $session, User $user): array
    {
        $session->load([
            'teams.players.user',
            'teams.cards' => fn($q) => $q->with('card'),
            'moderator',
        ]);

        $player   = $session->playerFor($user);
        $isMod    = $session->isModerator($user);
        $myTeam   = $player?->team;
        $round    = $session->currentRound();

        // Players only see their own team's hand
        $blueTeam = $session->teams->firstWhere('type', 'blue');
        $redTeam  = $session->teams->firstWhere('type', 'red');

        $state = [
            'session' => [
                'id'            => $session->id,
                'name'          => $session->name,
                'code'          => $session->code,
                'scenario'      => $session->scenario,
                'scenarioTitle' => $session->scenarioTitle(),
                'status'        => $session->status,
                'currentRound'  => $session->current_round,
                'currentPhase'  => $session->current_phase,
                'phaseLabel'    => GameSession::phaseLabel($session->current_phase),
                'maxRounds'     => $session->maxRounds(),
                'timerSeconds'  => $session->timerSeconds(),
            ],
            'role'  => $isMod ? 'moderator' : ($player ? 'player' : 'spectator'),
            'myTeamType' => $myTeam?->type,
            'blueTeam' => $this->teamState($blueTeam, $myTeam?->id === $blueTeam?->id || $isMod),
            'redTeam'  => $this->teamState($redTeam, $myTeam?->id === $redTeam?->id || $isMod),
            'round' => $round ? [
                'number'    => $round->round_number,
                'phase'     => $round->phase,
                'startedAt' => $round->started_at?->toIso8601String(),
                'eventCard' => $round->event_card,
            ] : null,
            'actionLog'  => $this->getActionLog($session, $round),
            'nodeStates' => $this->getNodeStates($session),
        ];

        return $state;
    }

    // ── Infrastructure Systems ──────────────────────────────────────

    public static function allowedNodesForScenario(int $scenario): array
    {
        return match($scenario) {
            1 => ['API Gateway', 'CI/CD Pipeline', 'GitHub Repos', 'Secrets Vault', 'AWS Production', 'Slack/Comms', 'Jira/Tickets', 'DB Production'],
            2 => ['API Gateway', 'Kubernetes Cluster', 'Docker Registry', 'npm Registry', 'CI/CD Pipeline', 'GitHub Repos', 'AWS Production', 'DB Production'],
            3 => ['API Gateway', 'AWS Production', 'Secrets Vault', 'GitHub Repos', 'CI/CD Pipeline', 'Slack/Comms', 'Jira/Tickets', 'DB Production'],
            4 => ['API Gateway', 'Kubernetes Cluster', 'DB Production', 'AWS Production', 'Slack/Comms', 'Secrets Vault', 'Docker Registry'],
            5 => ['API Gateway', 'Slack/Comms', 'Jira/Tickets', 'Secrets Vault', 'GitHub Repos', 'DB Production'],
            6 => ['API Gateway', 'Kubernetes Cluster', 'Docker Registry', 'DB Production', 'DB Dev/Test', 'GitHub Repos', 'CI/CD Pipeline', 'AWS Production'],
            7 => ['API Gateway', 'Kubernetes Cluster', 'AWS Production', 'GitHub Repos', 'CI/CD Pipeline', 'Docker Registry', 'Secrets Vault', 'DB Production', 'Slack/Comms', 'Jira/Tickets'],
            8 => ['API Gateway', 'Kubernetes Cluster', 'SCADA System', 'PLC Controllers', 'HMI Interface', 'Safety Systems (SIS)', 'Slack/Comms', 'AWS Production', 'DB Production'],
            default => [],
        };
    }

    public static function systems(?int $scenario = null): array
    {
        $all = [
            'devops' => [
                'name'  => 'Zone DevOps',
                'nodes' => ['GitHub Repos', 'CI/CD Pipeline', 'Docker Registry', 'npm Registry'],
            ],
            'cloud' => [
                'name'  => 'Zone Cloud',
                'nodes' => ['Kubernetes Cluster', 'AWS Production'],
            ],
            'data' => [
                'name'  => 'Zone Data',
                'nodes' => ['DB Production', 'DB Dev/Test'],
            ],
            'infra' => [
                'name'  => 'Zone Infra/Collab',
                'nodes' => ['API Gateway', 'Secrets Vault', 'Slack/Comms', 'Jira/Tickets'],
            ],
            'ot' => [
                'name'  => 'Zone OT/ICS',
                'nodes' => ['SCADA System', 'PLC Controllers', 'HMI Interface', 'Safety Systems (SIS)'],
            ],
        ];

        if (!$scenario) return $all;

        $allowed = self::allowedNodesForScenario($scenario);
        $filtered = [];

        foreach ($all as $key => $zone) {
            $validNodes = array_values(array_intersect($zone['nodes'], $allowed));
            if (!empty($validNodes)) {
                $filtered[$key] = [
                    'name'  => $zone['name'],
                    'nodes' => $validNodes,
                ];
            }
        }

        return $filtered;
    }

    // ── Private helpers ─────────────────────────────────────────────

    private function teamState(?GameTeam $team, bool $showHand): array
    {
        if (!$team) return [];

        $data = [
            'id'         => $team->id,
            'type'       => $team->type,
            'tokens'     => $team->tokens,
            'shopTokens' => $team->shop_tokens,
            'score'      => $team->score,
            'players'    => $team->players->map(fn($p) => [
                'id'        => $p->id,
                'name'      => $p->user->name,
                'initials'  => $p->user->initials(),
                'isCaptain' => $p->is_captain,
            ])->values(),
            'handCount' => $team->cards->where('status', 'hand')->count(),
            'activeCards' => $team->cards->where('status', 'active')->map(fn($tc) => [
                'id'             => $tc->id,
                'card'           => $this->cardToArray($tc->card),
                'remainingTurns' => $tc->remaining_turns,
            ])->values(),
        ];

        // Only show hand cards to own team or moderator
        if ($showHand) {
            $data['hand'] = $team->cards->where('status', 'hand')->map(fn($tc) => [
                'id'   => $tc->id,
                'card' => $this->cardToArray($tc->card),
            ])->values();
        }

        return $data;
    }

    private function cardToArray(GameCard $card): array
    {
        return [
            'id'          => $card->id,
            'type'        => $card->type,
            'subtype'     => $card->subtype,
            'name'        => $card->name,
            'phase'       => $card->phase,
            'description' => $card->description,
            'effect'      => $card->effect,
            'cost'        => $card->cost,
            'points'      => $card->points,
            'duration'    => $card->duration,
            'cssClass'    => $card->cssClass(),
            'typeLabel'   => $card->typeLabel(),
        ];
    }

    private function getActionLog(GameSession $session, ?GameRound $round): array
    {
        if (!$round) return [];

        return GameCardPlay::where('game_round_id', $round->id)
            ->with(['card', 'team', 'player'])
            ->orderBy('played_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($play) => [
                'id'           => $play->id,
                'teamType'     => $play->team->type,
                'playerName'   => $play->player->name,
                'cardName'     => $play->card->name,
                'targetSystem' => $play->target_system,
                'points'       => $play->points_earned,
                'playedAt'     => $play->played_at->toIso8601String(),
            ])->values()->toArray();
    }

    private function isValidPhaseForTeam(GameSession $session, GameTeam $team): bool
    {
        // Phase 2 = Red plays, Phase 3 = Blue plays
        if ($session->current_phase === 2 && $team->isRed()) return true;
        if ($session->current_phase === 3 && $team->isBlue()) return true;
        return false;
    }

    private function parseDuration(?string $duration): ?int
    {
        if (!$duration) return null;
        if (preg_match('/(\d+)\s*tour/', $duration, $m)) {
            return (int) $m[1];
        }
        if (str_contains(strtolower($duration), 'unique')) return 1;
        if (str_contains(strtolower($duration), 'persistent')) return 99;
        return null;
    }

    private function tickActiveCardDurations(GameSession $session): void
    {
        foreach ($session->teams as $team) {
            foreach ($team->activeCards()->get() as $tc) {
                $tc->tickDuration();
            }
        }
    }

    // ── Node State Management ───────────────────────────────────────

    private function setNodeState(GameSession $session, string $nodeId, string $state): void
    {
        $settings = $session->settings ?? [];
        $nodeStates = $settings['nodeStates'] ?? [];
        $nodeStates[$nodeId] = $state;
        $settings['nodeStates'] = $nodeStates;
        $session->update(['settings' => $settings]);
    }

    private function getNodeStates(GameSession $session): array
    {
        return $session->settings['nodeStates'] ?? [];
    }
}
