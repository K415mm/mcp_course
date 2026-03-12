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
}

