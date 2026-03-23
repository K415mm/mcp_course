<?php

namespace App\Http\Controllers;

use App\Models\GameCard;
use App\Models\GameSession;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function __construct(private GameService $gameService) {}

    /** Game lobby — list sessions, create/join */
    public function lobby()
    {
        $sessions = GameSession::active()
            ->with(['moderator', 'teams.players'])
            ->latest()
            ->get();

        $mySessions = GameSession::where('moderator_id', Auth::id())
            ->orWhereHas('players', fn($q) => $q->where('user_id', Auth::id()))
            ->with(['moderator', 'teams.players'])
            ->latest()
            ->limit(10)
            ->get();

        return view('game.lobby', compact('sessions', 'mySessions'));
    }

    /** Create game form */
    public function create()
    {
        return view('game.create');
    }

    /** Store new game session */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'scenario'      => 'required|integer|min:1|max:4',
            'max_rounds'    => 'nullable|integer|min:4|max:12',
            'timer_seconds' => 'nullable|integer|min:300|max:1800',
        ]);

        $session = $this->gameService->createSession(Auth::user(), $data);

        return redirect()->route('game.show', $session)
            ->with('success', 'Session de jeu créée ! Partagez le code : ' . $session->code);
    }

    /** Main game board */
    public function show(GameSession $session)
    {
        $session->load(['teams.players.user', 'moderator']);
        $user      = Auth::user();
        $isMod     = $session->isModerator($user);
        $player    = $session->playerFor($user);
        $systems   = GameService::systems();

        return view('game.board', compact('session', 'isMod', 'player', 'systems'));
    }

    /** Join a team */
    public function join(Request $request, GameSession $session)
    {
        $request->validate(['team' => 'required|in:blue,red']);

        if ($session->isModerator(Auth::user())) {
            return back()->with('error', 'Le modérateur ne peut pas rejoindre une équipe');
        }

        if (!$session->isLobby() && !$session->isActive()) {
            return back()->with('error', 'Impossible de rejoindre cette session');
        }

        $this->gameService->joinTeam($session, Auth::user(), $request->team);

        return redirect()->route('game.show', $session)
            ->with('success', 'Vous avez rejoint la ' . ($request->team === 'blue' ? 'Blue Team' : 'Red Team'));
    }

    /** Leave a game */
    public function leave(GameSession $session)
    {
        $this->gameService->leaveSession($session, Auth::user());

        return redirect()->route('game.lobby')
            ->with('success', 'Vous avez quitté la session');
    }
}
