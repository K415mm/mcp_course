<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    public function updateRole(User $user, Request $request)
    {
        $roles = implode(',', User::ROLES);
        $data = $request->validate(['role' => "required|in:{$roles}"]);
        $user->update(['role' => $data['role']]);
        return back()->with('success', "Role updated to {$data['role']} for {$user->name}.");
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'caps' => ['nullable', 'array'],
        ]);

        $caps = $validated['caps'] ?? [];
        $processedCaps = $user->capabilities ?? [];
        
        // Process max courses
        if (isset($caps['max_courses']) && $caps['max_courses'] !== '') {
            $processedCaps['max_courses'] = (int) $caps['max_courses'];
        } else {
            unset($processedCaps['max_courses']);
        }

        // Process workshops limit
        if (isset($caps['workshops_enabled']) && $caps['workshops_enabled'] !== '') {
            $processedCaps['workshops_enabled'] = (bool) $caps['workshops_enabled'];
        } else {
            unset($processedCaps['workshops_enabled']);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'capabilities' => empty($processedCaps) ? null : $processedCaps,
        ]);

        return back()->with('success', "User {$user->name} updated successfully.");
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function toggleBan(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You cannot ban yourself.");
        }

        if ($user->banned_at) {
            $user->update(['banned_at' => null]);
            return back()->with('success', "User {$user->name} unbanned.");
        } else {
            $user->update(['banned_at' => now()]);
            return back()->with('success', "User {$user->name} has been banned.");
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You cannot delete your own account.");
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "User {$name} deleted permanently.");
    }

    public function progress(User $user)
    {
        $completions = \App\Models\ModuleCompletion::where('user_id', $user->id)
            ->orderBy('completed_at', 'desc')
            ->get();
            
        $lessonProgress = \App\Models\LessonProgress::where('user_id', $user->id)
            ->orderBy('completed_at', 'desc')
            ->get();
            
        $totalTimeSeconds = $lessonProgress->sum('time_spent_seconds');
        
        return view('admin.users.progress', compact('user', 'completions', 'lessonProgress', 'totalTimeSeconds'));
    }
}

