<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\GameTeamCard;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameApiController extends Controller
{
    public function __construct(private GameService $gameService) {}

    /** Get full game state (for polling / reconnection) */
    public function getGameState(GameSession $session): JsonResponse
    {
        $state = $this->gameService->getGameState($session, Auth::user());
        return response()->json($state);
    }

    /** Play a card from hand */
    public function playCard(Request $request, GameSession $session): JsonResponse
    {
        $request->validate([
            'team_card_id'  => 'required|integer',
            'target_system' => 'nullable|string',
        ]);

        $user   = Auth::user();
        $player = $session->playerFor($user);
        if (!$player) {
            return response()->json(['success' => false, 'error' => 'Vous ne jouez pas dans cette session'], 403);
        }

        $teamCard = GameTeamCard::where('id', $request->team_card_id)
            ->where('game_team_id', $player->game_team_id)
            ->where('status', 'hand')
            ->firstOrFail();

        $result = $this->gameService->playCard(
            $session, $player->team, $teamCard, $user, $request->target_system
        );

        return response()->json($result);
    }

    /** Draw a card (costs 1 token) */
    public function drawCard(GameSession $session): JsonResponse
    {
        $player = $session->playerFor(Auth::user());
        if (!$player) {
            return response()->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }

        $teamCard = $this->gameService->drawCard($player->team);
        if (!$teamCard) {
            return response()->json(['success' => false, 'error' => 'Impossible de piocher']);
        }

        return response()->json([
            'success' => true,
            'card'    => $teamCard->load('card'),
        ]);
    }

    /** Buy from shop */
    public function buyFromShop(Request $request, GameSession $session): JsonResponse
    {
        $request->validate(['card_id' => 'required|integer']);

        $player = $session->playerFor(Auth::user());
        if (!$player) {
            return response()->json(['success' => false, 'error' => 'Non autorisé'], 403);
        }

        $result = $this->gameService->buyFromShop($player->team, $request->card_id);
        return response()->json($result);
    }

    // ── Moderator-only actions ──────────────────────────────────────

    /** Start a new round */
    public function startRound(GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $round = $this->gameService->startRound($session);
        return response()->json([
            'success' => true,
            'round'   => $round->round_number,
            'phase'   => $round->phase,
        ]);
    }

    /** Advance to next phase */
    public function advancePhase(GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $result = $this->gameService->advancePhase($session);
        $session->refresh();

        return response()->json([
            'success'  => true,
            'phase'    => $result,
            'label'    => $result > 0 ? GameSession::phaseLabel($result) : ($result === 0 ? 'Round terminé' : 'Partie terminée'),
            'status'   => $session->status,
        ]);
    }

    /** Draw event card */
    public function drawEvent(GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $event = $this->gameService->drawEventCard($session);
        if (!$event) {
            return response()->json(['success' => false, 'error' => "Plus d'événements disponibles"]);
        }

        return response()->json([
            'success' => true,
            'event'   => [
                'name'        => $event->name,
                'subtype'     => $event->subtype,
                'description' => $event->description,
                'effect'      => $event->effect,
                'cssClass'    => $event->cssClass(),
            ],
        ]);
    }

    /** Adjust team tokens (MJ ±2) */
    public function adjustTokens(Request $request, GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $request->validate([
            'team_type' => 'required|in:blue,red',
            'amount'    => 'required|integer|min:-2|max:2',
        ]);

        $team = $session->teams()->where('type', $request->team_type)->firstOrFail();
        $this->gameService->adjustTokens($team, $request->amount);

        return response()->json([
            'success' => true,
            'tokens'  => $team->fresh()->tokens,
        ]);
    }

    /** Deal initial hands when starting the game */
    public function dealHands(GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $this->gameService->dealInitialHands($session);
        return response()->json(['success' => true]);
    }

    /** Assign a user to a team (moderator only) */
    public function assignPlayer(Request $request, GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $request->validate([
            'user_id'   => 'required|integer|exists:users,id',
            'team_type' => 'required|in:blue,red',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $team = $this->gameService->joinTeam($session, $user, $request->team_type);

        return response()->json([
            'success' => true,
            'team'    => $team->type,
            'player'  => $user->name,
        ]);
    }

    /** Remove a player from the session (moderator only) */
    public function removePlayer(Request $request, GameSession $session): JsonResponse
    {
        if (!$session->isModerator(Auth::user())) {
            return response()->json(['success' => false, 'error' => 'Réservé au modérateur'], 403);
        }

        $request->validate(['user_id' => 'required|integer']);

        $this->gameService->leaveSession($session, \App\Models\User::findOrFail($request->user_id));

        return response()->json(['success' => true]);
    }
}
