<?php

namespace App\Http\Controllers;

use App\Models\CsSession;
use App\Models\CsPlayer;
use App\Services\CsContentBankService;
use App\Services\CsService;
use App\Services\NeptuneContentBankService;
use App\Services\NeptuneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NeptuneApiController extends CsApiController
{
    // NeptuneService extends CsService, so we accept it via the parent type.
    // We do NOT redeclare $cs here — PHP forbids changing the declared type of
    // an inherited property. The parent constructor stores it as CsService $cs,
    // and since NeptuneService IS-A CsService this is fully type-safe.
    public function __construct(
        NeptuneService $neptune,
        NeptuneContentBankService $contentBank
    ) {
        parent::__construct($neptune, $contentBank);
    }

    // Override join to use neptune_player_ session key
    public function join(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->where('scenario_key', 'neptune_strike')->firstOrFail();

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
        session(['neptune_player_' . $session->code => $player->id]);

        return response()->json([
            'success'    => true,
            'playerId'   => $player->id,
            'teamType'   => $player->team->type,
            'teamName'   => $player->team->name,
            'playerName' => $player->display_name,
        ]);
    }

    // Override resolvePlayer helper to use neptune_player_ session key
    protected function resolvePlayer(Request $request, CsSession $session): ?CsPlayer
    {
        $playerId = session('neptune_player_' . $session->code);
        if ($playerId) {
            return CsPlayer::where('id', $playerId)
                ->where('cs_session_id', $session->id)
                ->first();
        }
        return null;
    }

    // Ensure session queries are scoped to neptune_strike
    protected function getModeratorSession(string $code): CsSession
    {
        $session = CsSession::where('code', $code)->where('scenario_key', 'neptune_strike')->firstOrFail();
        if (!Auth::check() || !Auth::user()->canModerateCs()) {
            abort(403);
        }
        return $session;
    }

    // Override state to scope session query and append teamDecisions
    public function state(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)
            ->where('scenario_key', 'neptune_strike')
            ->with(['teams.players', 'moderator'])
            ->firstOrFail();

        $player = $this->resolvePlayer($request, $session);

        $stateData = $this->isModerator($session)
            ? $this->cs->getModeratorState($session)
            : $this->cs->getState($session, $player);

        $decisions = \App\Models\CsDecision::where('cs_session_id', $session->id)
            ->where('type', 'decision')
            ->get()
            ->map(function($d) {
                $injectId = null;
                $choice = null;
                $points = 0;

                // 1. Try parsing structured format: "Inject: D-01 | Option: A chosen. (100 pts)"
                if (preg_match('/Inject:\s*([A-Z0-9_\-]+)/i', $d->content, $m)) {
                    $injectId = strtoupper($m[1]);
                }
                if (preg_match('/Option:\s*([A-D])/i', $d->content, $m)) {
                    $choice = strtolower($m[1]);
                } elseif (preg_match('/Option\s+([A-D])/i', $d->content, $m)) {
                    // Fallback to legacy format: "Option A chosen."
                    $choice = strtolower($m[1]);
                }
                if (preg_match('/\((\-?\d+)\s*pts\)/i', $d->content, $m)) {
                    $points = (int)$m[1];
                } else {
                    $points = (int)($d->score_awarded ?? 0);
                }

                if (!$injectId) {
                    $injectId = 'D-0' . ($d->phase_index ?? 1);
                }

                return [
                    'team_type' => $d->team->type,
                    'inject_id' => $injectId,
                    'choice'    => $choice,
                    'points'    => $points,
                ];
            })->all();

        $stateData['teamDecisions'] = $decisions;

        return response()->json($stateData);
    }

    // Override decision to support auto-scored multiple-choice inject questions
    public function decision(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)->where('scenario_key', 'neptune_strike')->firstOrFail();

        $data = $request->validate([
            'choice'    => 'required|string|in:a,b,c,d,A,B,C,D',
            'inject_id' => 'required|string',
            'player_id' => 'required|integer',
        ]);

        $resolved = $this->resolvePlayer($request, $session);
        if (!$resolved || (int) $resolved->id !== (int) $data['player_id']) {
            return response()->json(['ok' => false, 'error' => 'Invalid player session context.'], 403);
        }

        $player = CsPlayer::where('id', $data['player_id'])
            ->where('cs_session_id', $session->id)
            ->with('team')
            ->firstOrFail();

        // Check if this team has already submitted a decision for this inject in this session
        $existing = \App\Models\CsDecision::where('cs_session_id', $session->id)
            ->where('cs_team_id', $player->cs_team_id)
            ->where('type', 'decision')
            ->where('settings->inject_id', strtoupper($data['inject_id']))
            ->first();

        if ($existing) {
            return response()->json(['ok' => false, 'error' => 'Decision already submitted'], 422);
        }

        // Score mapping
        $choice = strtolower($data['choice']);
        $injectId = strtoupper($data['inject_id']);
        
        $scores = [];
        if ($injectId === 'D-01') {
            $scores = ['a' => 100, 'b' => -20, 'c' => -30, 'd' => 80];
        } elseif ($injectId === 'D-02') {
            $scores = ['a' => 20, 'b' => 100, 'c' => 60, 'd' => -50];
        } elseif ($injectId === 'D-03') {
            $scores = ['a' => 60, 'b' => 60, 'c' => 120, 'd' => -80];
        } elseif ($injectId === 'D-04') {
            $scores = ['a' => 100, 'b' => 50, 'c' => 70, 'd' => 100];
        }

        $pts = $scores[$choice] ?? 0;

        // Save decision in cs_decisions table
        $decision = \App\Models\CsDecision::create([
            'cs_session_id' => $session->id,
            'cs_team_id'    => $player->cs_team_id,
            'cs_player_id'  => $player->id,
            'type'          => 'decision',
            'phase_index'   => $session->current_phase_index,
            'score_awarded' => max(0, $pts),
            'content'       => "Inject: " . $injectId . " | Option: " . strtoupper($choice) . " chosen. (" . $pts . " pts)",
        ]);

        // Update team score in database
        $team = $player->team;
        $team->score = max(0, $team->score + $pts);
        $team->save();

        return response()->json([
            'ok' => true,
            'decision_id' => $decision->id,
            'points' => $pts,
            'team_score' => $team->score
        ]);
    }
}
