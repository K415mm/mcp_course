<?php

namespace App\Http\Controllers;

use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\NeptuneScenario;
use App\Models\CsSession;
use App\Services\NeptuneContentBankService;
use App\Services\NeptuneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NeptuneController extends Controller
{
    public function __construct(
        protected NeptuneService $cs,
        protected NeptuneContentBankService $contentBank
    ) {}

    // ── Student Lobby: List my active sessions ──────────────────────────────
    public function lobby()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Get Neptune sessions where the user is a player OR the moderator, and the session is not finished
        $mySessions = CsSession::where('scenario_key', 'neptune_strike')
            ->where('status', '!=', 'finished')
            ->where(function($query) use ($user) {
                $query->where('moderator_id', $user->id)
                      ->orWhereHas('players', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->with(['teams', 'moderator'])
            ->orderByDesc('created_at')
            ->get();

        return view('neptune.lobby', [
            'mySessions' => $mySessions,
        ]);
    }

    // ── Admin/Moderator: List sessions ──────────────────────────────
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access reserved for administrators.');
        }
        $sessions = CsSession::where('scenario_key', 'neptune_strike')
            ->with('teams', 'moderator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('neptune.index', [
            'sessions'  => $sessions,
            'scenarios' => NeptuneScenario::list(),
        ]);
    }

    // ── Admin/Moderator: Clean finished sessions ──────────────────────────────
    public function cleanFinished()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access reserved for administrators.');
        }

        CsSession::where('scenario_key', 'neptune_strike')
            ->where('status', 'finished')
            ->delete();

        return redirect()->route('admin.neptune.index')->with('success', 'All finished sessions have been deleted.');
    }

    // ── Admin: Create session ───────────────────────────────────────
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'scenario_key' => 'required|string',
        ]);

        $session = $this->cs->createSession(Auth::user(), $data);

        return redirect()->route('neptune.moderator', $session->code)
            ->with('success', 'Session created — Code: ' . $session->code);
    }

    // ── Participant join page ───────────────────────────────────────
    public function show(string $code)
    {
        if (!Auth::check() || !Auth::user()->canAccessCsTeamView()) {
            abort(403, 'Unauthorized access to team view.');
        }

        $session  = CsSession::where('code', $code)->where('scenario_key', 'neptune_strike')->firstOrFail();
        $scenario = NeptuneScenario::find($session->scenario_key);
        $teams    = $session->teams;

        // Check if the user is already assigned in the database
        $player = CsPlayer::with('team')
            ->where('cs_session_id', $session->id)
            ->where('user_id', Auth::id())
            ->first();

        // Fallback to session check for anonymous players (if applicable)
        if (!$player) {
            $playerId = session('neptune_player_' . $code);
            $player   = $playerId ? CsPlayer::with('team')->find($playerId) : null;
        } else {
            // Update session just in case
            session(['neptune_player_' . $code => $player->id]);
        }

        return response()->view('neptune.participant', [
            'session'  => $session,
            'scenario' => $scenario,
            'teams'    => $teams,
            'player'   => $player,
            'user'     => Auth::user(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    // ── Moderator console ───────────────────────────────────────────
    public function moderator(string $code)
    {
        $session = CsSession::where('code', $code)
            ->where('scenario_key', 'neptune_strike')
            ->with(['teams', 'moderator'])
            ->firstOrFail();

        if (!Auth::check() || !Auth::user()->canModerateCs()) {
            abort(403, 'Access reserved for admins and mentors.');
        }

        $scenario = NeptuneScenario::find($session->scenario_key);
        $injects  = CsInject::where('scenario_key', $session->scenario_key)
            ->orderBy('sort_order')
            ->get();
        $initialBankByPhase = collect($scenario['phases'] ?? [])
            ->mapWithKeys(function ($phase) use ($session) {
                $index = (int) ($phase['index'] ?? 0);
                return [$index => $this->contentBank->getPhaseContent($session->scenario_key, $index)];
            })
            ->all();

        return response()->view('neptune.moderator', [
            'session'  => $session,
            'scenario' => $scenario,
            'teams'    => $session->teams,
            'injects'  => $injects,
            'initialBankByPhase' => $initialBankByPhase,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    // ── Big-screen dashboard ────────────────────────────────────────
    public function dashboard(string $code)
    {
        if (!Auth::check() || !Auth::user()->canAccessCsDashboard()) {
            abort(403, 'Unauthorized access to dashboard.');
        }

        $session  = CsSession::where('code', $code)->where('scenario_key', 'neptune_strike')->firstOrFail();
        $scenario = NeptuneScenario::find($session->scenario_key);

        return response()->view('neptune.dashboard', [
            'session'  => $session,
            'scenario' => $scenario,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }

    // ── Manage Players View ─────────────────────────────────────────
    public function managePlayers(string $code)
    {
        $session = CsSession::where('code', $code)
            ->where('scenario_key', 'neptune_strike')
            ->with(['teams', 'moderator'])
            ->firstOrFail();

        if (!Auth::check() || !Auth::user()->canModerateCs()) {
            abort(403, 'Access reserved for admins and mentors.');
        }

        $scenario = NeptuneScenario::find($session->scenario_key);

        return response()->view('neptune.manage_players', [
            'session'  => $session,
            'scenario' => $scenario,
            'teams'    => $session->teams,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }
}
