<?php

namespace App\Http\Controllers;

use App\Models\CsSession;
use App\Models\CsPlayer;
use App\Services\NeptuneContentBankService;
use App\Services\NeptuneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NeptuneApiController extends CsApiController
{
    public function __construct(
        NeptuneService $cs,
        NeptuneContentBankService $contentBank
    ) {
        parent::__construct($cs, $contentBank);
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

    // Override state to scope session query
    public function state(Request $request, string $code): JsonResponse
    {
        $session = CsSession::where('code', $code)
            ->where('scenario_key', 'neptune_strike')
            ->with(['teams.players', 'moderator'])
            ->firstOrFail();

        $player = $this->resolvePlayer($request, $session);

        if ($this->isModerator($session)) {
            return response()->json($this->cs->getModeratorState($session));
        }

        return response()->json($this->cs->getState($session, $player));
    }
}
