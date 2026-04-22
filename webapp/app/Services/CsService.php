<?php

namespace App\Services;

use App\Models\CsBadge;
use App\Models\CsBroadcast;
use App\Models\CsDecision;
use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\CsQuiz;
use App\Models\CsQuizEntry;
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

        // Create teams from scenario definition when available.
        $teamDefinitions = $scenario['teams'] ?? CsTeam::defaultTeams();
        foreach ($teamDefinitions as $teamData) {
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
        if (!$team->is_scored) {
            return;
        }

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

    public function openVote(CsSession $session, ?string $question = null, ?array $options = null, bool $isSecret = false): CsVote
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
            'is_secret'     => $isSecret,
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
        if (!$team->can_vote) return false;
        if ($vote->teamHasVoted($team)) return false;

        CsVoteEntry::create([
            'cs_vote_id'  => $vote->id,
            'cs_team_id'  => $team->id,
            'choice'      => $choice,
            'voted_at'    => now(),
        ]);

        return true;
    }

    // ── Quiz Questions (team-scored, separate from strategic vote) ──

    public function openQuiz(
        CsSession $session,
        string $question,
        array $options,
        string $type = 'single_choice',
        ?string $prompt = null,
        ?array $correctAnswers = null,
        int $basePoints = 0
    ): CsQuiz {
        $session->quizzes()->where('is_open', true)->update(['is_open' => false]);

        return CsQuiz::create([
            'cs_session_id' => $session->id,
            'type' => $type,
            'question' => $question,
            'prompt' => $prompt,
            'options' => $options,
            'correct_answers' => $correctAnswers ?? [],
            'base_points' => max(0, $basePoints),
            'is_open' => true,
            'phase_index' => $session->current_phase_index,
        ]);
    }

    public function submitQuizAnswer(CsQuiz $quiz, CsTeam $team, ?string $answerKey, ?string $answerText = null): bool
    {
        if (!$quiz->is_open) return false;
        if ($quiz->teamHasAnswered($team)) return false;
        if (!$team->isScorable()) return false;

        $awarded = $this->calculateQuizAwardedPoints($quiz, $answerKey, $answerText);
        $entry = CsQuizEntry::create([
            'cs_quiz_id' => $quiz->id,
            'cs_team_id' => $team->id,
            'answer_key' => $answerKey,
            'answer_text' => $answerText,
            'awarded_points' => $awarded,
            'answered_at' => now(),
        ]);

        // Make quiz answers immediately visible in moderator Decisions panel.
        $this->upsertQuizDecision($quiz, $entry, $awarded);

        return true;
    }

    public function closeQuizAndScore(CsQuiz $quiz): array
    {
        $quiz->loadMissing('session');
        $quizType = $this->normalizeQuizType((string) $quiz->type);
        $results = [];

        foreach ($quiz->entries()->with('team')->get() as $entry) {
            $awarded = $this->calculateQuizAwardedPoints($quiz, $entry->answer_key, $entry->answer_text);
            $entry->awarded_points = $awarded;
            $entry->save();

            if ($entry->team) {
                $this->adjustScore($entry->team, $entry->awarded_points);
            }

            $results[] = [
                'teamId' => $entry->cs_team_id,
                'teamName' => $entry->team?->name ?? 'Unknown',
                'answerKey' => $entry->answer_key,
                'answerText' => $entry->answer_text,
                'quizType' => $quizType,
                'awardedPoints' => $entry->awarded_points,
            ];

            // Keep a single decision item per team/quiz and refresh awarded score after auto-scoring.
            $this->upsertQuizDecision($quiz, $entry, (int) $entry->awarded_points, $quizType);
        }

        $quiz->update([
            'is_open' => false,
            'results' => $results,
        ]);

        return $results;
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
        $new = max(0, $points);
        $old = (int) ($decision->score_awarded ?? 0);
        $delta = $new - $old;

        $decision->update(['score_awarded' => $new]);
        if ($delta !== 0) {
            $this->adjustScore($decision->team, $delta);
        }
    }

    // ── Bonus Badges ────────────────────────────────────────────────

    public function awardBadge(CsSession $session, CsTeam $team, string $badgeType, ?User $moderator = null): CsBadge
    {
        if (!$team->badge_eligible) {
            throw new \RuntimeException('This team is not eligible for badges.');
        }

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

    public function getState(CsSession $session, ?CsPlayer $player = null, bool $isModerator = false): array
    {
        $session->load(['teams.players', 'moderator']);
        $scenario    = $session->scenario();
        $phases      = $session->phases();
        $currentPhase = $phases[$session->current_phase_index] ?? null;
        $openVote    = $session->openVote();
        $openQuiz    = $session->openQuiz();
        $phaseContent = app(\App\Services\CsContentBankService::class)
            ->getPhaseContent($session->scenario_key, (int) $session->current_phase_index);

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
            'roleMode'    => $t->role_mode,
            'color'       => $t->color,
            'icon'        => $t->icon,
            'logoPath'    => $t->logo_path ? \Illuminate\Support\Facades\Storage::url($t->logo_path) : null,
            'score'       => $t->score,
            'isScored'    => (bool) $t->is_scored,
            'canVote'     => (bool) $t->can_vote,
            'badgeEligible' => (bool) $t->badge_eligible,
            'showInRanking' => (bool) $t->show_in_ranking,
            'badge'       => $t->badge(),   // includes icon, name, image
            'playerCount' => $t->players->count(),
            'onlineCount' => $t->players->filter(fn($p) => $p->isOnline())->count(),
        ])->values()->all();

        $voteData = null;
        if ($openVote) {
            $isSecret = (bool) $openVote->is_secret;
            $canSeeTally = $isModerator || !$isSecret || !$openVote->is_open;
            $voteData = [
                'id'       => $openVote->id,
                'question' => $openVote->question,
                'options'  => $openVote->options,
                'tally'    => $canSeeTally ? $openVote->tally() : [],
                'isSecret' => $isSecret,
                'is_secret'=> $isSecret,
                'is_open'  => (bool) $openVote->is_open,
                'isOpen'   => (bool) $openVote->is_open,
                'myChoice' => $player ? $openVote->entries()->where('cs_team_id', $player->cs_team_id)->value('choice') : null,
            ];
        }

        $quizData = null;
        if ($openQuiz) {
            $quizData = [
                'id' => $openQuiz->id,
                'type' => $this->normalizeQuizType((string) $openQuiz->type),
                'question' => $openQuiz->question,
                'prompt' => $openQuiz->prompt,
                'options' => $openQuiz->options ?? [],
                'correctAnswers' => $isModerator ? ($openQuiz->correct_answers ?? []) : [],
                'basePoints' => (int) $openQuiz->base_points,
                'isOpen' => (bool) $openQuiz->is_open,
                'myAnswer' => $player ? $openQuiz->entries()->where('cs_team_id', $player->cs_team_id)->value('answer_key') : null,
                'myAnswerText' => $player ? $openQuiz->entries()->where('cs_team_id', $player->cs_team_id)->value('answer_text') : null,
                'answerCount' => $openQuiz->entries()->count(),
                'results' => $openQuiz->results ?? [],
            ];
        }

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
            'phaseContent' => $phaseContent,
            'vote'       => $voteData,
            'quiz'       => $quizData,
            'myTeamType'  => $player?->team?->type,
            'myDecisions' => $myDecisions,
        ];
    }

    /**
     * Get full moderator state including all decisions across all teams.
     */
    public function getModeratorState(CsSession $session): array
    {
        // Backfill/refresh quiz answer decisions so mentor panel stays consistent
        // even for answers submitted before the latest UI/backend updates.
        $this->syncQuizDecisionsForSession($session);

        $base     = $this->getState($session, null, true);
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

    private function normalizeQuizType(string $type): string
    {
        $v = strtolower(trim($type));
        return match ($v) {
            'multi_choice', 'multi choice', 'multichoice', 'multiple_choice', 'multiple choice', 'multi_chice', 'multi chice', 'multi-choise' => 'multi_choice',
            'short_answer', 'short answer', 'shortanswer', 'text', 'open' => 'short_answer',
            'order', 'sort_order', 'sort order', 'ordering', 'rank', 'ranking' => 'order',
            default => 'single_choice',
        };
    }

    private function calculateQuizAwardedPoints(CsQuiz $quiz, ?string $answerKeyRaw, ?string $answerTextRaw): int
    {
        $quizType = $this->normalizeQuizType((string) $quiz->type);
        $options = collect($quiz->options ?? []);
        $correct = collect($quiz->correct_answers ?? [])->map(fn($v) => strtoupper(trim((string) $v)))->values()->all();
        $hasCorrect = !empty($correct);

        $answerKey = strtoupper(trim((string) ($answerKeyRaw ?? '')));
        $answerText = trim((string) ($answerTextRaw ?? ''));
        $awarded = 0;

        if ($quizType === 'short_answer') {
            if ($hasCorrect) {
                $normalizedText = mb_strtolower(preg_replace('/\s+/', ' ', trim($answerText)));
                $normalizedCorrect = collect($quiz->correct_answers ?? [])
                    ->map(fn($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v))))
                    ->filter()
                    ->values()
                    ->all();
                $awarded = in_array($normalizedText, $normalizedCorrect, true) ? max(0, (int) $quiz->base_points) : 0;
            }
        } elseif ($quizType === 'order') {
            $chosenOrder = array_values(array_filter(array_map('trim', explode(',', $answerKey))));
            if ($hasCorrect) {
                $awarded = ($chosenOrder === $correct) ? max(0, (int) $quiz->base_points) : 0;
            } else {
                foreach ($chosenOrder as $k) {
                    $opt = $options->firstWhere('key', $k);
                    $awarded += (int) ($opt['points'] ?? 0);
                }
            }
        } elseif ($quizType === 'multi_choice' || str_contains($answerKey, ',')) {
            $chosenKeys = array_filter(array_map('trim', explode(',', $answerKey)));
            if ($hasCorrect) {
                sort($chosenKeys);
                $sortedCorrect = $correct;
                sort($sortedCorrect);
                $awarded = ($chosenKeys === $sortedCorrect) ? max(0, (int) $quiz->base_points) : 0;
            } else {
                foreach ($chosenKeys as $k) {
                    $opt = $options->firstWhere('key', $k);
                    $awarded += (int) ($opt['points'] ?? 0);
                }
            }
        } else {
            if ($hasCorrect) {
                $awarded = in_array($answerKey, $correct, true) ? max(0, (int) $quiz->base_points) : 0;
            } else {
                $opt = $options->firstWhere('key', $answerKey);
                $awarded = (int) ($opt['points'] ?? 0);
            }
        }

        return max(0, $awarded);
    }

    private function upsertQuizDecision(CsQuiz $quiz, CsQuizEntry $entry, int $scoreAwarded, ?string $quizType = null): void
    {
        $type = $quizType ?? $this->normalizeQuizType((string) $quiz->type);
        $content = sprintf(
            'Quiz (%s): %s | Réponse: %s%s',
            $type,
            $quiz->question,
            $entry->answer_key ?: '—',
            $entry->answer_text ? (' (' . mb_strimwidth($entry->answer_text, 0, 120, '...') . ')') : ''
        );

        $escapedQuestion = addcslashes($quiz->question, '%_\\');
        $existing = CsDecision::where('cs_session_id', $quiz->cs_session_id)
            ->where('cs_team_id', $entry->cs_team_id)
            ->where('type', 'question')
            ->where('phase_index', (int) $quiz->phase_index)
            ->where('content', 'like', sprintf('Quiz (%%): %s | Réponse: %%', $escapedQuestion))
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $existing->update([
                'content' => $content,
                'score_awarded' => max(0, $scoreAwarded),
            ]);
            return;
        }

        CsDecision::create([
            'cs_session_id' => $quiz->cs_session_id,
            'cs_team_id' => $entry->cs_team_id,
            'cs_player_id' => null,
            'type' => 'question',
            'content' => $content,
            'phase_index' => (int) $quiz->phase_index,
            'score_awarded' => max(0, $scoreAwarded),
        ]);
    }

    private function syncQuizDecisionsForSession(CsSession $session): void
    {
        CsQuiz::where('cs_session_id', $session->id)
            ->with('entries')
            ->get()
            ->each(function (CsQuiz $quiz): void {
                $quizType = $this->normalizeQuizType((string) $quiz->type);
                foreach ($quiz->entries as $entry) {
                    $this->upsertQuizDecision($quiz, $entry, (int) ($entry->awarded_points ?? 0), $quizType);
                }
            });
    }
}
