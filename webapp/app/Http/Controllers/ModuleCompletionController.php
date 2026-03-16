<?php

namespace App\Http\Controllers;

use App\Mail\ModuleCompletionMail;
use App\Models\ModuleCompletion;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ModuleCompletionController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    /**
     * Mark a module as complete for the authenticated user.
     * Sends the completion email if this is the first time.
     */
    public function mark(string $moduleSlug)
    {
        $user = Auth::user();
        $module = $this->courseService->getItem($moduleSlug);

        if (!$module) {
            return response()->json(['error' => 'Module not found.'], 404);
        }

        $completion = ModuleCompletion::firstOrCreate(
            ['user_id' => $user->id, 'module_slug' => $moduleSlug],
            ['completed_at' => now()]
        );

        // Send email only on first completion (wasRecentlyCreated)
        if ($completion->wasRecentlyCreated && !$completion->email_sent_at) {
            try {
                Mail::to($user->email)->send(new ModuleCompletionMail($user, $module));
                $completion->update(['email_sent_at' => now()]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ModuleCompletionMail failed', [
                    'user' => $user->id, 'module' => $moduleSlug, 'error' => $e->getMessage()
                ]);
            }
        }

        // Build next module suggestion
        $allItems = $this->courseService->getAllItems();
        $currentOrder = $module['order'] ?? 0;
        $next = collect($allItems)->first(fn($i) => ($i['order'] ?? 0) > $currentOrder);

        return response()->json([
            'status'      => $completion->wasRecentlyCreated ? 'completed' : 'already_completed',
            'module'      => $module['title'],
            'next_module' => $next ? ['title' => $next['title'], 'slug' => $next['slug']] : null,
        ]);
    }

    /**
     * Return all completions for the current user (used on the profile page).
     */
    public function index()
    {
        $completions = ModuleCompletion::where('user_id', Auth::id())
            ->orderBy('completed_at')
            ->pluck('module_slug')
            ->toArray();

        return response()->json($completions);
    }
}
