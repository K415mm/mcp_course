<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /** Show the invitation registration form */
    public function accept(string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            abort(404, 'This invitation link is invalid or has already been used.');
        }

        return view('auth.invite-register', compact('invitation', 'token'));
    }

    /** Complete registration via invitation */
    public function register(string $token, Request $request)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            abort(404, 'This invitation link is invalid or has already been used.');
        }

        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $invitation->email,
            'password' => Hash::make($data['password']),
        ]);

        // Mark invitation as accepted (token is now spent)
        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect()->route('home')->with('success',
            'Welcome to RAISEGUARD Academy! Your account has been created successfully.');
    }
}
