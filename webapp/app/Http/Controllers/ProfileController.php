<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Services\CourseService;

class ProfileController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    public function show()
    {
        $user = Auth::user();
        $items = $this->courseService->getAllItems();
        $modules = array_filter($items, fn($i) => $i['type'] === 'module');
        $progress = $user->modules_viewed ?? [];

        // Calculate per-module progress
        $moduleProgress = [];
        foreach ($modules as $mod) {
            $lessons = $this->courseService->getLessons($mod['slug']);
            $total = 0;
            $seen = 0;
            foreach ($lessons as $sec => $data) {
                foreach ($data['lessons'] as $lesson) {
                    $total++;
                    $key = $mod['slug'] . '.' . $sec . '.' . $lesson['slug'];
                    if (isset($progress[$key]))
                        $seen++;
                }
            }
            $moduleProgress[$mod['slug']] = [
                'module' => $mod,
                'total' => $total,
                'seen' => $seen,
                'pct' => $total > 0 ? (int) round($seen / $total * 100) : 0,
            ];
        }

        $qrCode = null;
        if ($user->two_factor_secret && !$user->two_factor_confirmed_at) {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $url = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $user->two_factor_secret
            );
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrCode = $writer->writeString($url);
        }

        return view('profile.show', compact('user', 'moduleProgress', 'items', 'qrCode'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'bio' => ['nullable', 'string', 'max:500'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            $filename = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = basename($filename);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }
}
