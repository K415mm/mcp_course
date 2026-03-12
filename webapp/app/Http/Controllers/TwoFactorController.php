<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Display the 2FA challenge view for login.
     */
    public function challenge(Request $request)
    {
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-challenge');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $request->session()->forget('2fa:user:id');
            \Illuminate\Support\Facades\Auth::login($user, $request->session()->get('2fa:remember', false));
            $request->session()->forget('2fa:remember');
            $request->session()->regenerate();
            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['code' => 'The provided authentication code was invalid.']);
    }

    /**
     * Enable 2FA for the current user.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        // Initialize Google2FA
        $google2fa = new Google2FA();

        // Generate a new secret key
        $user->two_factor_secret = $google2fa->generateSecretKey();
        $user->save();

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Confirm 2FA setup by providing the first valid code.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);
        $user = $request->user();

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_confirmed_at = now();
            $user->save();
            return back()->with('status', 'two-factor-authentication-confirmed');
        }

        return back()->withErrors(['code' => 'The 2FA code is invalid. Please try again.']);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return back()->with('status', 'two-factor-authentication-disabled');
    }
}
