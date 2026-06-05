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

    public function verify(User $user)
    {
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return back()->with('success', "User {$user->name} has been manually verified.");
        }
        
        return back()->with('info', "User {$user->name} is already verified.");
    }

    public function store(Request $request)
    {
        $roles = implode(',', User::ROLES);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', "in:{$roles}"],
            'verify_email' => ['nullable', 'boolean'],
            'caps' => ['nullable', 'array'],
        ]);

        $caps = $validated['caps'] ?? [];
        $processedCaps = [];
        if (isset($caps['max_courses']) && $caps['max_courses'] !== '') {
            $processedCaps['max_courses'] = (int) $caps['max_courses'];
        }
        if (isset($caps['workshops_enabled']) && $caps['workshops_enabled'] !== '') {
            $processedCaps['workshops_enabled'] = (bool) $caps['workshops_enabled'];
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_admin' => $validated['role'] === User::ROLE_ADMIN,
            'email_verified_at' => $request->has('verify_email') ? now() : null,
            'capabilities' => empty($processedCaps) ? null : $processedCaps,
        ]);

        return back()->with('success', "User {$user->name} created successfully.");
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'users_data' => ['required', 'string'],
            'verify_email' => ['nullable', 'boolean'],
            'default_role' => ['required', 'string', 'in:' . implode(',', User::ROLES)],
        ]);

        $lines = preg_split('/[\r\n]+/', $request->input('users_data'));
        $created = 0;
        $skipped = [];
        $verify = $request->has('verify_email');
        $defaultRole = $request->input('default_role');

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = array_map('trim', str_getcsv($line));
            
            if (count($parts) < 2) {
                $skipped[] = "Line " . ($lineNum + 1) . ": Invalid format (Must have Name and Email).";
                continue;
            }

            $name = $parts[0];
            $email = $parts[1];
            
            if (User::where('email', $email)->exists()) {
                $skipped[] = "Line " . ($lineNum + 1) . ": Email '{$email}' already exists.";
                continue;
            }

            $password = (count($parts) >= 3 && $parts[2] !== '') ? $parts[2] : 'Defensy123!';
            $role = (count($parts) >= 4 && in_array($parts[3], User::ROLES)) ? $parts[3] : $defaultRole;

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'role' => $role,
                'is_admin' => $role === User::ROLE_ADMIN,
                'email_verified_at' => $verify ? now() : null,
            ]);
            $created++;
        }

        $msg = "Successfully created {$created} users.";
        if (!empty($skipped)) {
            $msg .= " Skipped: " . implode(' | ', $skipped);
        }

        return back()->with($skipped ? 'warning' : 'success', $msg);
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

