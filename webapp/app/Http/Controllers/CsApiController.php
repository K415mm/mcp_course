<?php

namespace App\Http\Controllers;

use App\Models\CsBadge;
use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\CsQuiz;
use App\Models\CsSession;
use App\Models\CsTeam;
use App\Models\CsVote;
use App\Services\CsContentBankService;
use App\Services\CsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CsApiController extends Controller
{
    public function __construct(
        protected CsService $cs,
        protected CsContentBankService $contentBank
    ) {}

    // ── GET /api/cs/{code}/state ────────────────────────────────────
    // Main polling endpoint — used by all 3 views
    public function state(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)
            ->with(['teams.players', 'moderator'])
            ->firstOrFail();

        // Resolve player from session token (cookie-based)
        $player = $this->resolvePlayer($request, $session);

        // Moderator gets extra data
        if ($this->isModerator($session)) {
            return response()->json($this->cs->getModeratorState($session));
        }

        return response()->json($this->cs->getState($session, $player));
    }

    // ── GET /api/cs/{code}/bank ─────────────────────────────────────
    public function getBank(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $phaseIndex = (int) ($request->query('phase_index', $session->current_phase_index));
        $phaseIndex = max(0, $phaseIndex);

        $content = $this->contentBank->getPhaseContent($session->scenario_key, $phaseIndex);

        return response()->json([
            'ok' => true,
            'scenarioKey' => $session->scenario_key,
            'phaseIndex' => $phaseIndex,
            'messages' => $content['messages'],
            'questions' => $content['questions'],
            'media' => $content['media'] ?? [],
        ]);
    }

    // ── POST /api/cs/{code}/join ────────────────────────────────────
    public function join(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'team_type'    => 'required|string',
            'display_name' => 'required|string|max:80',
        ]);

        $player = $this->cs->joinTeam(
            $session,
            $data['team_type'],
            $data['display_name'],
            Auth::user()
        );

        // Store player ID in session cookie for reconnect
        session(['cs_player_' . $session->code => $player->id]);

        return response()->json([
            'success'    => true,
            'playerId'   => $player->id,
            'teamType'   => $player->team->type,
            'teamName'   => $player->team->name,
            'playerName' => $player->display_name,
        ]);
    }

    // ── POST /api/cs/{code}/heartbeat ──────────────────────────────
    public function heartbeat(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        $player  = $this->resolvePlayer($request, $session);
        $player?->heartbeat();

        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/timer/start ────────────────────────────
    public function timerStart(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $this->cs->startTimer($session);
        return response()->json(['ok' => true, 'action' => 'started']);
    }

    // ── POST /api/cs/{code}/timer/pause ────────────────────────────
    public function timerPause(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $this->cs->pauseTimer($session);
        return response()->json(['ok' => true, 'action' => 'paused']);
    }

    // ── POST /api/cs/{code}/timer/reset ────────────────────────────
    public function timerReset(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $this->cs->resetTimer($session);
        return response()->json(['ok' => true, 'action' => 'reset']);
    }

    // ── POST /api/cs/{code}/timer/set ──────────────────────────────
    public function timerSet(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data    = $request->validate(['seconds' => 'required|integer|min:0|max:7200']);
        $this->cs->setTimerSeconds($session, $data['seconds']);
        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/phase/advance ──────────────────────────
    public function phaseAdvance(string $code): JsonResponse
    {
        $session  = $this->getModeratorSession($code);
        $newPhase = $this->cs->advancePhase($session);
        return response()->json([
            'ok'         => true,
            'phaseIndex' => $newPhase,
            'finished'   => $newPhase === -1,
        ]);
    }

    // ── POST /api/cs/{code}/phase/goto ─────────────────────────────
    public function phaseGoto(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data    = $request->validate(['index' => 'required|integer|min:0']);
        $this->cs->goToPhase($session, $data['index']);
        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/score/{teamId} ─────────────────────────
    public function scoreAdjust(Request $request, string $code, int $teamId): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data    = $request->validate(['delta' => 'required|integer|min:-999|max:999']);
        $team    = CsTeam::where('id', $teamId)->where('cs_session_id', $session->id)->firstOrFail();
        $this->cs->adjustScore($team, $data['delta']);
        return response()->json(['ok' => true, 'score' => $team->fresh()->score]);
    }

    // ── POST /api/cs/{code}/broadcast ──────────────────────────────
    public function broadcast(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data    = $request->validate([
            'message' => 'required|string|max:500',
            'type'    => 'nullable|in:info,warn,alert,success',
        ]);
        $bc = $this->cs->broadcast($session, $data['message'], $data['type'] ?? 'info', Auth::user());
        return response()->json(['ok' => true, 'id' => $bc->id]);
    }

    // ── POST /api/cs/{code}/phantom ────────────────────────────────
    public function phantom(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $bc = $this->cs->triggerPhantom($session, $request->input('message'), Auth::user());
        return response()->json(['ok' => true, 'message' => $bc->message]);
    }

    // ── POST /api/cs/{code}/inject/{injectId} ──────────────────────
    public function inject(string $code, int $injectId): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $inject  = CsInject::findOrFail($injectId);
        $si      = $this->cs->triggerInject($session, $inject, Auth::user());
        return response()->json(['ok' => true, 'id' => $si->id]);
    }

    // ── POST /api/cs/{code}/atmosphere ─────────────────────────────
    public function atmosphere(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data    = $request->validate(['mode' => 'required|in:calm,tension,crisis,hacked,victory,neutral']);
        $this->cs->setAtmosphere($session, $data['mode']);
        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/vote/open ──────────────────────────────
    public function voteOpen(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:6',
            'options.*.key' => 'required|string|max:10',
            'options.*.label' => 'required|string|max:255',
            'options.*.color' => 'nullable|string|max:20',
            'options.*.points' => 'nullable|integer|min:0|max:100',
            'options.*.note' => 'nullable|string|max:1000',
            'is_secret' => 'nullable|boolean',
        ]);

        $normalizedOptions = collect($data['options'])->map(function ($option) {
            return [
                'key' => strtoupper(trim((string) ($option['key'] ?? ''))),
                'label' => trim((string) ($option['label'] ?? '')),
                'color' => $option['color'] ?? null,
                'points' => isset($option['points']) ? (int) $option['points'] : 0,
                'note' => $option['note'] ?? null,
            ];
        })->values()->all();

        $scenario = $session->scenario();
        $autoSecretPhases = collect($scenario['secret_vote_phases'] ?? [])->map(fn($v) => (int) $v)->all();
        $isAutoSecret = in_array((int) $session->current_phase_index, $autoSecretPhases, true);
        $isSecret = array_key_exists('is_secret', $data) ? (bool) $data['is_secret'] : $isAutoSecret;

        $vote    = $this->cs->openVote(
            $session,
            $data['question'],
            $normalizedOptions,
            $isSecret
        );
        return response()->json(['ok' => true, 'voteId' => $vote->id, 'isSecret' => (bool) $vote->is_secret]);
    }

    // ── POST /api/cs/{code}/vote/close ─────────────────────────────────
    public function voteClose(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $vote    = CsVote::where('cs_session_id', $session->id)->where('is_open', true)->first();
        if (!$vote) return response()->json(['ok' => false, 'error' => 'No open vote']);

        $tally = $this->cs->closeVote($vote);
        $maxVotes = empty($tally) ? 0 : max($tally);
        $winners = collect($tally)
            ->filter(fn($count) => (int) $count === (int) $maxVotes)
            ->keys()
            ->values()
            ->all();
        $isTie = count($winners) > 1;
        $winner = $isTie ? null : ($winners[0] ?? null);
        $awardedPoints = 0;
        $winnerLabel = null;
        $winnerNote = null;

        if ($winner) {
            $option = collect($vote->options ?? [])->firstWhere('key', $winner);
            if ($option && isset($option['points'])) {
                $winnerLabel = $option['label'] ?? $winner;
                $winnerNote = $option['note'] ?? null;
                // Award points to all teams (collective vote)
                foreach ($session->teams as $team) {
                    $this->cs->adjustScore($team, (int) $option['points']);
                }
                $awardedPoints = (int) $option['points'];
            }
        }

        return response()->json([
            'ok'           => true,
            'tally'        => $tally,
            'winner'       => $winner,
            'winnerLabel'  => $winnerLabel,
            'winnerNote'   => $winnerNote,
            'isTie'        => $isTie,
            'tiedKeys'     => $isTie ? $winners : [],
            'awardedPoints'=> $awardedPoints,
        ]);
    }

    // ── POST /api/cs/{code}/vote/submit ────────────────────────────
    public function voteSubmit(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        if ($this->isSubmissionWindowClosed($session)) {
            return response()->json(['ok' => false, 'error' => 'Phase time is over; vote is locked.'], 422);
        }

        $data    = $request->validate([
            'choice'  => 'required|string|max:10',
            'team_id' => 'required|integer',
        ]);

        $player = $this->resolvePlayer($request, $session);
        if (!$player || !$player->team || (int) $player->cs_team_id !== (int) $data['team_id']) {
            return response()->json(['ok' => false, 'error' => 'Invalid voting context.'], 403);
        }

        $vote = CsVote::where('cs_session_id', $session->id)->where('is_open', true)->firstOrFail();
        $team = CsTeam::where('id', $data['team_id'])->where('cs_session_id', $session->id)->firstOrFail();
        $ok   = $this->cs->submitVote($vote, $team, $data['choice']);
        if (!$ok) {
            return response()->json(['ok' => false, 'error' => 'Vote rejected. Team may be ineligible or already voted.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/quiz/open ──────────────────────────────
    public function quizOpen(Request $request, string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'type' => 'nullable|in:single_choice,multi_choice,order,short_answer',
            'prompt' => 'nullable|string|max:1000',
            'options' => 'required|array|min:2|max:8',
            'options.*.key' => 'required|string|max:10',
            'options.*.label' => 'required|string|max:255',
            'options.*.color' => 'nullable|string|max:20',
            'options.*.points' => 'nullable|integer|min:0|max:100',
            'correct_answers' => 'nullable|array',
            'correct_answers.*' => 'nullable|string|max:20',
            'base_points' => 'nullable|integer|min:0|max:100',
        ]);

        $normalizedOptions = collect($data['options'])->map(function ($option) {
            return [
                'key' => strtoupper(trim((string) ($option['key'] ?? ''))),
                'label' => trim((string) ($option['label'] ?? '')),
                'color' => $option['color'] ?? null,
                'points' => isset($option['points']) ? (int) $option['points'] : 0,
            ];
        })->values()->all();

        $quiz = $this->cs->openQuiz(
            $session,
            trim((string) $data['question']),
            $normalizedOptions,
            (string) ($data['type'] ?? 'single_choice'),
            $data['prompt'] ?? null,
            collect($data['correct_answers'] ?? [])->map(fn($v) => strtoupper(trim((string) $v)))->filter()->values()->all(),
            (int) ($data['base_points'] ?? 0)
        );

        return response()->json(['ok' => true, 'quizId' => $quiz->id]);
    }

    // ── POST /api/cs/{code}/quiz/submit ────────────────────────────
    public function quizSubmit(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        if ($this->isSubmissionWindowClosed($session)) {
            return response()->json(['ok' => false, 'error' => 'Phase time is over; quiz is locked.'], 422);
        }

        $data = $request->validate([
            'choice'  => 'nullable',
            'answer_text' => 'nullable|string|max:1000',
            'team_id' => 'required|integer',
        ]);

        $player = $this->resolvePlayer($request, $session);
        if (!$player || !$player->team || (int) $player->cs_team_id !== (int) $data['team_id']) {
            return response()->json(['ok' => false, 'error' => 'Invalid quiz context.'], 403);
        }

        $quiz = CsQuiz::where('cs_session_id', $session->id)->where('is_open', true)->firstOrFail();

        // Handle array of choices for multi_choice
        $choiceVal = $data['choice'] ?? null;
        if (is_array($choiceVal)) {
            $choiceStr = implode(',', array_map(fn($v) => strtoupper(trim((string) $v)), $choiceVal));
        } else {
            $choiceStr = $choiceVal ? strtoupper(trim((string) $choiceVal)) : null;
        }

        $team = CsTeam::where('id', $data['team_id'])->where('cs_session_id', $session->id)->firstOrFail();
        $ok = $this->cs->submitQuizAnswer(
            $quiz,
            $team,
            $choiceStr,
            $data['answer_text'] ?? null
        );

        if (!$ok) {
            return response()->json(['ok' => false, 'error' => 'Quiz answer rejected.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/quiz/close ─────────────────────────────
    public function quizClose(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $quiz = CsQuiz::where('cs_session_id', $session->id)->where('is_open', true)->first();
        if (!$quiz) return response()->json(['ok' => false, 'error' => 'No open quiz']);

        $results = $this->cs->closeQuizAndScore($quiz);
        return response()->json([
            'ok' => true,
            'results' => $results,
            'answeredTeams' => count($results),
        ]);
    }

    // ── POST /api/cs/{code}/decision ───────────────────────────────
    public function decision(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        if ($this->isSubmissionWindowClosed($session)) {
            return response()->json(['ok' => false, 'error' => 'Phase time is over; submissions are locked.'], 422);
        }

        $data    = $request->validate([
            'type'      => 'required|in:decision,escalade,communication,question',
            'content'   => 'required|string|max:1000',
            'player_id' => 'required|integer',
        ]);

        $player = CsPlayer::where('id', $data['player_id'])
            ->where('cs_session_id', $session->id)
            ->with('team')
            ->firstOrFail();

        $decision = $this->cs->submitDecision(
            $session,
            $player->team,
            $player,
            $data['type'],
            $data['content']
        );

        return response()->json(['ok' => true, 'id' => $decision->id]);
    }

    // ── POST /api/cs/{code}/decision/{id}/award ───────────────────────
    public function awardScore(Request $request, string $code, int $decisionId): JsonResponse
    {
        $session  = $this->getModeratorSession($code);
        $decision = \App\Models\CsDecision::where('id', $decisionId)
            ->where('cs_session_id', $session->id)
            ->firstOrFail();
        $data     = $request->validate(['points' => 'required|integer|min:0|max:100']);
        $this->cs->awardDecisionScore($decision, $data['points']);
        return response()->json(['ok' => true]);
    }

    // ── POST /api/cs/{code}/badge/{teamId} ────────────────────────────────
    public function badgeAward(Request $request, string $code, int $teamId): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $team    = CsTeam::where('id', $teamId)->where('cs_session_id', $session->id)->firstOrFail();
        $data    = $request->validate([
            'badge_type' => 'required|in:first_responder,crisis_communicator,zero_silo,innovation',
        ]);
        try {
            $badge = $this->cs->awardBadge($session, $team, $data['badge_type'], Auth::user());
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['team_id' => $e->getMessage()]);
        }

        return response()->json([
            'ok'     => true,
            'badge'  => $badge->badge_icon . ' ' . $badge->badge_label,
            'points' => $badge->bonus_points,
        ]);
    }

    // ── POST /api/cs/{code}/end ─────────────────────────────────────
    public function end(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $phases  = $session->phases();
        $lastIdx = count($phases) - 1;
        $this->cs->goToPhase($session, $lastIdx);
        $session->update(['status' => 'finished', 'ended_at' => now()]);
        $this->cs->setAtmosphere($session, 'victory');
        return response()->json(['ok' => true]);
    }

    // ── Private helpers ─────────────────────────────────────────────

    private function getModeratorSession(string $code): CsSession
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        if (!Auth::check() || (!Auth::user()->isAdmin() && $session->moderator_id !== Auth::id())) {
            abort(403);
        }
        return $session;
    }

    private function isModerator(CsSession $session): bool
    {
        return Auth::check() && (Auth::user()->isAdmin() || $session->moderator_id === Auth::id());
    }

    private function resolvePlayer(Request $request, CsSession $session): ?CsPlayer
    {
        $playerId = session('cs_player_' . $session->code);
        if ($playerId) {
            return CsPlayer::find($playerId);
        }
        return null;
    }

    private function isSubmissionWindowClosed(CsSession $session): bool
    {
        $remaining = $session->timerRemainingSeconds();
        return $remaining !== null && $remaining <= 0;
    }
}
