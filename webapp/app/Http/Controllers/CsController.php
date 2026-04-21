<?php

namespace App\Http\Controllers;

use App\Models\CsInject;
use App\Models\CsPlayer;
use App\Models\CsScenario;
use App\Models\CsSession;
use App\Services\CsContentBankService;
use App\Services\CsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsController extends Controller
{
    public function __construct(
        protected CsService $cs,
        protected CsContentBankService $contentBank
    ) {}

    // ── Admin/Moderator: List sessions ──────────────────────────────
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
        $sessions = CsSession::with('teams', 'moderator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('cs.index', [
            'sessions'  => $sessions,
            'scenarios' => CsScenario::list(),
        ]);
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

        return redirect()->route('cs.moderator', $session->code)
            ->with('success', 'Session créée — code : ' . $session->code);
    }

    // ── Participant join page ───────────────────────────────────────
    public function show(string $code)
    {
        $session  = CsSession::where('code', $code)->firstOrFail();
        $scenario = $session->scenario();
        $teams    = $session->teams;

        $playerId = session('cs_player_' . $code);
        $player   = $playerId ? CsPlayer::with('team')->find($playerId) : null;

        return view('cs.participant', [
            'session'  => $session,
            'scenario' => $scenario,
            'teams'    => $teams,
            'player'   => $player,
            'user'     => Auth::user(),
        ]);
    }

    // ── Moderator console ───────────────────────────────────────────
    public function moderator(string $code)
    {
        $session = CsSession::where('code', $code)
            ->with(['teams', 'moderator'])
            ->firstOrFail();

        // Allow admin or session moderator
        if (!Auth::check() || (!Auth::user()->isAdmin() && $session->moderator_id !== Auth::id())) {
            abort(403, 'Accès réservé au modérateur.');
        }

        $scenario = $session->scenario();
        $injects  = CsInject::where('scenario_key', $session->scenario_key)
            ->orderBy('sort_order')
            ->get();
        $initialBankByPhase = collect($scenario['phases'] ?? [])
            ->mapWithKeys(function ($phase) use ($session) {
                $index = (int) ($phase['index'] ?? 0);
                return [$index => $this->contentBank->getPhaseContent($session->scenario_key, $index)];
            })
            ->all();

        return view('cs.moderator', [
            'session'  => $session,
            'scenario' => $scenario,
            'teams'    => $session->teams,
            'injects'  => $injects,
            'initialBankByPhase' => $initialBankByPhase,
        ]);
    }

    // ── Big-screen dashboard ────────────────────────────────────────
    public function dashboard(string $code)
    {
        $session  = CsSession::where('code', $code)->firstOrFail();
        $scenario = $session->scenario();

        return view('cs.dashboard', [
            'session'  => $session,
            'scenario' => $scenario,
        ]);
    }
}
