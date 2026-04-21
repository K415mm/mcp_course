<?php

namespace App\Services;

use App\Models\CsBadge;
use App\Models\CsBroadcast;
use App\Models\CsDecision;
use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\CsScenario;
use App\Models\CsSession;
use App\Models\CsSessionInject;
use App\Models\CsTeam;
use App\Models\CsVote;
use App\Models\CsVoteEntry;
use App\Models\User;

class CsService
{
    // ── Session Management ──────────────────────────────────────────

    public function createSession(User $moderator, array $data): CsSession
    {
        $scenario = CsScenario::find($data['scenario_key'] ?? 'phantom_grid');

        $session = CsSession::create([
            'name'         => $data['name'],
            'scenario_key' => $scenario['key'],
            'moderator_id' => $moderator->id,
            'status'       => 'lobby',
            'atmosphere'   => 'calm',
        ]);

        // Create the 6 teams
        foreach (CsTeam::defaultTeams() as $teamData) {
            $session->teams()->create($teamData);
        }

        return $session->load('teams');
    }

    /**
     * Join or re-join a team. Supports both authenticated users and anonymous guests.
     * Returns the CsPlayer record.
     */
    public function joinTeam(
        CsSession $session,
        string $teamType,
        string $displayName,
        ?User $user = null
    ): CsPlayer {
        $team = $session->teams()->where('type', $teamType)->firstOrFail();

        // If same user/name already in session, just update their team
        $existing = $session->players()
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('display_name', $displayName))
            ->first();

        if ($existing) {
            $existing->update([
                'cs_team_id'   => $team->id,
                'display_name' => $displayName,
                'last_seen_at' => now(),
            ]);
            return $existing;
        }

        $isCaptain = $team->players()->count() === 0;

        return CsPlayer::create([
            'cs_session_id' => $session->id,
            'cs_team_id'    => $team->id,
            'user_id'       => $user?->id,
            'display_name'  => $displayName,
            'is_captain'    => $isCaptain,
            'last_seen_at'  => now(),
        ]);
    }

    // ── Timer (Server-Authoritative) ────────────────────────────────

    public function startTimer(CsSession $session): void
    {
        $phase = $session->currentPhase();
        if (!$phase) return;

        $remaining = $session->timer_paused_remaining ?? $phase['duration_seconds'];

        $session->update([
            'status'                 => 'active',
            'timer_ends_at'          => now()->addSeconds($remaining),
            'timer_paused_remaining' => null,
        ]);
    }

    public function pauseTimer(CsSession $session): void
    {
        if (!$session->timerIsRunning()) return;

        $session->update([
            'status'                 => 'paused',
            'timer_paused_remaining' => $session->timerRemainingSeconds(),
            'timer_ends_at'          => null,
        ]);
    }

    public function resetTimer(CsSession $session): void
    {
        $phase = $session->currentPhase();
        $session->update([
            'timer_ends_at'          => null,
            'timer_paused_remaining' => $phase['duration_seconds'] ?? null,
            'status'                 => $session->isActive() ? 'paused' : $session->status,
        ]);
    }

    public function setTimerSeconds(CsSession $session, int $seconds): void
    {
        $isRunning = $session->timerIsRunning();
        $session->update([
            'timer_ends_at'          => $isRunning ? now()->addSeconds($seconds) : null,
            'timer_paused_remaining' => $isRunning ? null : $seconds,
        ]);
    }

    // ── Phase Control ───────────────────────────────────────────────

    public function advancePhase(CsSession $session): int
    {
        $phases   = $session->phases();
        $nextIdx  = $session->current_phase_index + 1;

        if ($nextIdx >= count($phases)) {
            // End of exercise
            $session->update([
                'status'                 => 'finished',
                'ended_at'               => now(),
                'timer_ends_at'          => null,
                'timer_paused_remaining' => null,
            ]);
            // Auto-set victory atmosphere
            $this->setAtmosphere($session, 'victory');
            return -1; // signals game over
        }

        $nextPhase = $phases[$nextIdx];

        $session->update([
            'current_phase_index'    => $nextIdx,
            'status'                 => 'paused', // start paused, moderator hits play
            'timer_ends_at'          => null,
            'timer_paused_remaining' => $nextPhase['duration_seconds'],
        ]);

        // Auto atmosphere
        $atmoMap = [1 => 'calm', 2 => 'tension', 3 => 'crisis', 4 => 'tension', 5 => 'victory'];
        if (isset($atmoMap[$nextIdx])) {
            $this->setAtmosphere($session, $atmoMap[$nextIdx]);
        }

        return $nextIdx;
    }

    public function goToPhase(CsSession $session, int $phaseIndex): void
    {
        $phases = $session->phases();
        if (!isset($phases[$phaseIndex])) return;

        $phase = $phases[$phaseIndex];
        $session->update([
            'current_phase_index'    => $phaseIndex,
            'status'                 => 'paused',
            'timer_ends_at'          => null,
            'timer_paused_remaining' => $phase['duration_seconds'],
        ]);
    }

    // ── Score Management ────────────────────────────────────────────

    public function adjustScore(CsTeam $team, int $delta): void
    {
        $team->increment('score', $delta);
        $team->score = max(0, $team->fresh()->score);
        $team->save();
    }

    public function setScore(CsTeam $team, int $value): void
    {
        $team->update(['score' => max(0, $value)]);
    }

    // ── Broadcasts ──────────────────────────────────────────────────

    public function broadcast(
        CsSession $session,
        string $message,
        string $type = 'info',
        ?User $moderator = null
    ): CsBroadcast {
        return CsBroadcast::create([
            'cs_session_id' => $session->id,
            'moderator_id'  => $moderator?->id,
            'message'       => $message,
            'type'          => $type,
            'phase_index'   => $session->current_phase_index,
            'is_phantom'    => false,
        ]);
    }

    public function triggerPhantom(CsSession $session, ?string $message = null, ?User $moderator = null): CsBroadcast
    {
        $scenario = $session->scenario();
        $msgs     = $scenario['phantom_messages'] ?? ['PHANTOM GRID is watching.'];
        $msg      = $message ?? $msgs[array_rand($msgs)];

        $this->setAtmosphere($session, 'hacked');

        return CsBroadcast::create([
            'cs_session_id' => $session->id,
            'moderator_id'  => $moderator?->id,
            'message'       => $msg,
            'type'          => 'alert',
            'phase_index'   => $session->current_phase_index,
            'is_phantom'    => true,
        ]);
    }

    // ── Injects ─────────────────────────────────────────────────────

    public function triggerInject(CsSession $session, CsInject $inject, ?User $moderator = null): CsSessionInject
    {
        return CsSessionInject::create([
            'cs_session_id' => $session->id,
            'cs_inject_id'  => $inject->id,
            'phase_index'   => $session->current_phase_index,
            'triggered_by'  => $moderator?->id,
            'triggered_at'  => now(),
        ]);
    }

    // ── Votes ───────────────────────────────────────────────────────

    public function openVote(CsSession $session, ?string $question = null, ?array $options = null): CsVote
    {
        // Close any existing open vote first
        $session->votes()->where('is_open', true)->update(['is_open' => false]);

        $scenario       = $session->scenario();
        $defaultOptions = $scenario['vote_options'] ?? [
            ['key' => 'A', 'label' => 'Option A', 'color' => '#00b4d8'],
            ['key' => 'B', 'label' => 'Option B', 'color' => '#f4a261'],
            ['key' => 'C', 'label' => 'Option C', 'color' => '#2dc653'],
        ];

        return CsVote::create([
            'cs_session_id' => $session->id,
            'question'      => $question,
            'options'       => $options ?? $defaultOptions,
            'is_open'       => true,
            'phase_index'   => $session->current_phase_index,
        ]);
    }

    public function closeVote(CsVote $vote): array
    {
        $tally = $vote->tally();
        $vote->update(['is_open' => false, 'results' => $tally]);
        return $tally;
    }

    public function submitVote(CsVote $vote, CsTeam $team, string $choice): bool
    {
        if (!$vote->is_open) return false;
        if ($vote->teamHasVoted($team)) return false;

        CsVoteEntry::create([
            'cs_vote_id'  => $vote->id,
            'cs_team_id'  => $team->id,
            'choice'      => $choice,
            'voted_at'    => now(),
        ]);

        return true;
    }

    // ── Decisions ───────────────────────────────────────────────────

    public function submitDecision(
        CsSession $session,
        CsTeam $team,
        CsPlayer $player,
        string $type,
        string $content
    ): CsDecision {
        return CsDecision::create([
            'cs_session_id' => $session->id,
            'cs_team_id'    => $team->id,
            'cs_player_id'  => $player->id,
            'type'          => $type,
            'content'       => $content,
            'phase_index'   => $session->current_phase_index,
        ]);
    }

    public function awardDecisionScore(CsDecision $decision, int $points): void
    {
        $decision->update(['score_awarded' => $points]);
        $decision->team->addScore($points);
    }

    // ── Bonus Badges ────────────────────────────────────────────────

    public function awardBadge(CsSession $session, CsTeam $team, string $badgeType, ?User $moderator = null): CsBadge
    {
        $catalog = CsBadge::catalog();
        $info    = $catalog[$badgeType] ?? ['icon' => '🏅', 'label' => $badgeType, 'points' => 5];

        $badge = CsBadge::create([
            'cs_session_id' => $session->id,
            'cs_team_id'    => $team->id,
            'badge_type'    => $badgeType,
            'badge_label'   => $info['label'],
            'badge_icon'    => $info['icon'],
            'bonus_points'  => $info['points'],
            'awarded_by'    => $moderator?->id,
        ]);

        // Award the bonus points to the team
        $this->adjustScore($team, $info['points']);

        return $badge;
    }

    // ── Atmosphere ──────────────────────────────────────────────────

    public function setAtmosphere(CsSession $session, string $mode): void
    {
        $allowed = ['calm', 'tension', 'crisis', 'hacked', 'victory', 'neutral'];
        $mode    = in_array($mode, $allowed) ? $mode : 'calm';
        $session->update(['atmosphere' => $mode]);
    }

    // ── Game State (main API payload) ───────────────────────────────

    public function getState(CsSession $session, ?CsPlayer $player = null): array
    {
        $session->load(['teams.players', 'moderator']);
        $scenario    = $session->scenario();
        $phases      = $session->phases();
        $currentPhase = $phases[$session->current_phase_index] ?? null;
        $openVote    = $session->openVote();

        // Last 5 broadcasts (include phantom separately)
        $broadcasts = $session->broadcasts()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn($b) => [
                'id'        => $b->id,
                'message'   => $b->message,
                'type'      => $b->type,
                'isPhantom' => (bool) $b->is_phantom,
                'at'        => $b->created_at->toIso8601String(),
            ])->values()->all();

        // Last 10 injects — filtered by player's team type if applicable
        $playerTeamType = $player?->team?->type;
        $injects = CsSessionInject::where('cs_session_id', $session->id)
            ->with('inject')
            ->orderByDesc('triggered_at')
            ->limit(20)
            ->get()
            ->filter(fn($si) =>
                is_null($si->inject->target_team_type)
                || $si->inject->target_team_type === $playerTeamType
            )
            ->take(10)
            ->map(fn($si) => [
                'id'            => $si->id,
                'tag'           => $si->inject->tag,
                'content'       => $si->inject->content,
                'color'         => $si->inject->color,
                'isSuprise'     => (bool) $si->inject->is_surprise,
                'targetTeam'    => $si->inject->target_team_type,
                'at'            => $si->triggered_at->toIso8601String(),
            ])->values()->all();

        // Teams state
        $teams = $session->teams->map(fn(CsTeam $t) => [
            'id'          => $t->id,
            'type'        => $t->type,
            'name'        => $t->name,
            'roleLabel'   => $t->role_label,
            'color'       => $t->color,
            'icon'        => $t->icon,
            'logoPath'    => $t->logo_path ? \Illuminate\Support\Facades\Storage::url($t->logo_path) : null,
            'score'       => $t->score,
            'badge'       => $t->badge(),   // includes icon, name, image
            'playerCount' => $t->players->count(),
            'onlineCount' => $t->players->filter(fn($p) => $p->isOnline())->count(),
        ])->values()->all();

        // Player's own team decisions (if in a team)
        $myDecisions = $player ? CsDecision::where('cs_team_id', $player->cs_team_id)
            ->where('cs_session_id', $session->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($d) => [
                'id'      => $d->id,
                'type'    => $d->type,
                'content' => $d->content,
                'at'      => $d->created_at->toIso8601String(),
            ])->values()->all() : [];

        return [
            'session' => [
                'id'               => $session->id,
                'code'             => $session->code,
                'name'             => $session->name,
                'status'           => $session->status,
                'scenarioKey'      => $session->scenario_key,
                'scenarioTitle'    => $scenario['title'] ?? 'Unknown',
                'attackerName'     => $scenario['attacker_name'] ?? 'UNKNOWN',
                'attackerIcon'     => $scenario['attacker_icon'] ?? '☠️',
                'atmosphere'       => $session->atmosphere,
                'currentPhaseIndex'=> $session->current_phase_index,
                'currentPhase'     => $currentPhase,
                'totalPhases'      => count($phases),
                'phases'           => $phases,
            ],
            'timer' => [
                'remainingSeconds' => $session->timerRemainingSeconds(),
                'isRunning'        => $session->timerIsRunning(),
                'endsAt'           => $session->timer_ends_at?->toIso8601String(),
            ],
            'teams'      => $teams,
            'broadcasts' => $broadcasts,
            'injects'    => $injects,
            'vote'       => $openVote ? [
                'id'      => $openVote->id,
                'question'=> $openVote->question,
                'options' => $openVote->options,
                'tally'   => $openVote->tally(),
                'is_open' => (bool) $openVote->is_open,
                'isOpen'  => (bool) $openVote->is_open,
                'myChoice'=> $player ? $openVote->entries()->where('cs_team_id', $player->cs_team_id)->value('choice') : null,
            ] : null,
            'myTeamType'  => $player?->team?->type,
            'myDecisions' => $myDecisions,
        ];
    }

    /**
     * Get full moderator state including all decisions across all teams.
     */
    public function getModeratorState(CsSession $session): array
    {
        $base     = $this->getState($session);
        $scenario = $session->scenario();

        // All decisions for review
        $decisions = $session->decisions()
            ->with(['team', 'player'])
            ->limit(50)
            ->get()
            ->map(fn($d) => [
                'id'           => $d->id,
                'teamName'     => $d->team->name,
                'teamType'     => $d->team->type,
                'playerName'   => $d->player->display_name ?? '—',
                'type'         => $d->type,
                'content'      => $d->content,
                'phaseIndex'   => $d->phase_index,
                'scoreAwarded' => $d->score_awarded,
                'at'           => $d->created_at->toIso8601String(),
            ])->values()->all();

        // All injects for the scenario (catalog)
        $injectCatalog = CsInject::forScenario($session->scenario_key)->get()
            ->map(fn($i) => [
                'id'         => $i->id,
                'tag'        => $i->tag,
                'content'    => $i->content,
                'color'      => $i->color,
                'phaseHint'  => $i->phase_hint,
                'isSurprise' => (bool) $i->is_surprise,
            ])->values()->all();

        // Phantom messages from scenario
        $phantomMessages = $scenario['phantom_messages'] ?? [];

        // Active players online
        $onlinePlayers = CsPlayer::where('cs_session_id', $session->id)
            ->where('last_seen_at', '>=', now()->subSeconds(15))
            ->with('team')
            ->get()
            ->map(fn($p) => [
                'name'     => $p->display_name,
                'teamType' => $p->team->type,
                'teamName' => $p->team->name,
            ])->values()->all();

        // Awarded badges
        $badges = CsBadge::where('cs_session_id', $session->id)
            ->with('team')
            ->orderByDesc('awarded_at')
            ->get()
            ->map(fn($b) => [
                'id'        => $b->id,
                'teamName'  => $b->team->name,
                'teamType'  => $b->team->type,
                'teamId'    => $b->cs_team_id,
                'type'      => $b->badge_type,
                'label'     => $b->badge_label,
                'icon'      => $b->badge_icon,
                'image'     => CsBadge::catalog()[$b->badge_type]['image'] ?? null,
                'points'    => $b->bonus_points,
                'at'        => $b->awarded_at->toIso8601String(),
            ])->values()->all();

        // Current phase decision matrix
        $phases       = $session->phases();
        $currentPhase = $phases[$session->current_phase_index] ?? null;
        $decisionMatrix = $currentPhase['decision_matrix'] ?? null;

        // Badge catalog for the UI
        $badgeCatalog = CsBadge::catalog();

        return array_merge($base, [
            'decisions'       => $decisions,
            'injectCatalog'   => $injectCatalog,
            'phantomMessages' => $phantomMessages,
            'onlinePlayers'   => $onlinePlayers,
            'badges'          => $badges,
            'badgeCatalog'    => $badgeCatalog,
            'decisionMatrix'  => $decisionMatrix,
        ]);
    }
}
