<?php

namespace App\Http\Controllers;

use App\Models\GameCard;
use App\Models\GameSession;
use App\Models\User;
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

    /** Create game form (admin only) */
    public function create()
    {
        return view('game.create');
    }

    /** Store new game session (admin only) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'scenario'      => 'required|integer|min:1|max:8',
            'max_rounds'    => 'nullable|integer|min:4|max:12',
            'timer_seconds' => 'nullable|integer|min:300|max:1800',
        ]);

        $session = $this->gameService->createSession(Auth::user(), $data);

        return redirect()->route('game.manage', $session)
            ->with('success', 'Session créée ! Assignez les joueurs aux équipes.');
    }

    /** Team management view (admin/moderator only) */
    public function manage(GameSession $session)
    {
        $session->load(['teams.players.user', 'moderator']);

        // Get all verified students not yet in this session
        $assignedIds = $session->players()->pluck('user_id')->toArray();
        $availableUsers = User::where('role', 'student')
            ->whereNotNull('email_verified_at')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        return view('game.manage', compact('session', 'availableUsers'));
    }

    /** Main game board */
    public function show(GameSession $session)
    {
        $session->load(['teams.players.user', 'moderator']);
        $user   = Auth::user();
        $isMod  = $session->isModerator($user);
        $player = $session->playerFor($user);

        // Access control: only moderator or assigned players can see the board
        if (!$isMod && !$player) {
            return redirect()->route('game.lobby')
                ->with('error', 'Vous n\'êtes pas assigné à cette session.');
        }

        $systems = GameService::systems();

        return view('game.board', compact('session', 'isMod', 'player', 'systems'));
    }

    /** Leave a game */
    public function leave(GameSession $session)
    {
        $this->gameService->leaveSession($session, Auth::user());

        return redirect()->route('game.lobby')
            ->with('success', 'Vous avez quitté la session');
    }
}
