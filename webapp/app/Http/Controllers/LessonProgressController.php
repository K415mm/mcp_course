<?php

namespace App\Http\Controllers;

use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonProgressController extends Controller
{
    /**
     * Handle the background heartbeat ping from the lesson frontend.
     */
    public function ping(Request $request)
    {
        $request->validate([
            'course_slug' => 'required|string',
            'module_slug' => 'required|string',
            'lesson_slug' => 'required|string',
            'increment'   => 'required|integer|min:1|max:60',
        ]);

        $user = Auth::user();

        $progress = LessonProgress::firstOrCreate(
            [
                'user_id'     => $user->id,
                'course_slug' => $request->course_slug,
                'module_slug' => $request->module_slug,
                'lesson_slug' => $request->lesson_slug,
            ]
        );

        if (!$progress->is_completed) {
            $progress->increment('time_spent_seconds', $request->increment);
        }

        return response()->json([
            'status' => 'success',
            'time_spent' => $progress->time_spent_seconds,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Mark a lesson as fully completed.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'course_slug' => 'required|string',
            'module_slug' => 'required|string',
            'lesson_slug' => 'required|string',
        ]);

        $minSeconds = config('course.min_lesson_time', 60); // 60 seconds default threshold

        $user = Auth::user();

        $progress = LessonProgress::where([
            'user_id'     => $user->id,
            'course_slug' => $request->course_slug,
            'module_slug' => $request->module_slug,
            'lesson_slug' => $request->lesson_slug,
        ])->first();

        if (!$progress) {
            return response()->json(['status' => 'error', 'message' => 'No progress tracked.'], 400);
        }

        if ($progress->is_completed) {
            return response()->json(['status' => 'already_completed']);
        }

        if ($progress->time_spent_seconds < $minSeconds) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must spend at least ' . $minSeconds . ' seconds reading this lesson. Currently at ' . $progress->time_spent_seconds . ' seconds.',
            ], 403);
        }

        $progress->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        return response()->json(['status' => 'completed', 'time_spent' => $progress->time_spent_seconds]);
    }
}
