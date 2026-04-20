<?php

namespace App\Http\Controllers;

use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\CsSession;
use App\Models\CsTeam;
use App\Models\CsVote;
use App\Services\CsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsApiController extends Controller
{
    public function __construct(protected CsService $cs) {}

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
        $vote    = $this->cs->openVote(
            $session,
            $request->input('question'),
            $request->input('options')
        );
        return response()->json(['ok' => true, 'voteId' => $vote->id]);
    }

    // ── POST /api/cs/{code}/vote/close ─────────────────────────────
    public function voteClose(string $code): JsonResponse
    {
        $session = $this->getModeratorSession($code);
        $vote    = CsVote::where('cs_session_id', $session->id)->where('is_open', true)->first();
        if (!$vote) return response()->json(['ok' => false, 'error' => 'No open vote']);
        $tally = $this->cs->closeVote($vote);
        return response()->json(['ok' => true, 'tally' => $tally]);
    }

    // ── POST /api/cs/{code}/vote/submit ────────────────────────────
    public function voteSubmit(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
        $data    = $request->validate([
            'choice'  => 'required|string|max:10',
            'team_id' => 'required|integer',
        ]);
        $vote = CsVote::where('cs_session_id', $session->id)->where('is_open', true)->firstOrFail();
        $team = CsTeam::where('id', $data['team_id'])->where('cs_session_id', $session->id)->firstOrFail();
        $ok   = $this->cs->submitVote($vote, $team, $data['choice']);
        return response()->json(['ok' => $ok]);
    }

    // ── POST /api/cs/{code}/decision ───────────────────────────────
    public function decision(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->firstOrFail();
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

    // ── POST /api/cs/{code}/decision/{id}/award ─────────────────────
    public function awardScore(Request $request, string $code, int $decisionId): JsonResponse
    {
        $session  = $this->getModeratorSession($code);
        $decision = \App\Models\CsDecision::findOrFail($decisionId);
        $data     = $request->validate(['points' => 'required|integer|min:0|max:100']);
        $this->cs->awardDecisionScore($decision, $data['points']);
        return response()->json(['ok' => true]);
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
}
