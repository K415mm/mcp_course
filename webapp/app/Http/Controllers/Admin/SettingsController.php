<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard.
     */
    public function index()
    {
        $cinematicSetting = \App\Models\Setting::where('key', 'cinematic_animations_enabled')->first();
        $cinematicAnimationsEnabled = $cinematicSetting ? filter_var($cinematicSetting->value, FILTER_VALIDATE_BOOLEAN) : true;

        return view('admin.settings', compact('cinematicAnimationsEnabled'));
    }

    /**
     * Update the global settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'cinematic_animations_enabled' => 'nullable|string',
        ]);

        $isEnabled = $request->has('cinematic_animations_enabled') ? 'true' : 'false';

        \App\Models\Setting::updateOrCreate(
            ['key' => 'cinematic_animations_enabled'],
            [
                'value' => $isEnabled,
                'type' => 'boolean',
            ]
        );

        return redirect()->route('admin.settings.index')->with('success', 'Global settings updated successfully.');
    }

    /**
     * Display the Role Capabilities settings.
     */
    public function roles()
    {
        $roles = [\App\Models\User::ROLE_STUDENT, \App\Models\User::ROLE_GUEST, \App\Models\User::ROLE_PREENROL];
        
        $roleSettings = [];
        foreach ($roles as $role) {
            $setting = \App\Models\Setting::where('key', "role_capabilities_{$role}")->first();
            
            // Hardcoded defaults fallback if not set
            $default = [
                'max_courses' => -1,
                'workshops_enabled' => false,
            ];
            
            if ($role === \App\Models\User::ROLE_STUDENT) {
                $default['max_courses'] = 3;
            } elseif ($role === \App\Models\User::ROLE_GUEST) {
                $default['max_courses'] = 1;
            } elseif ($role === \App\Models\User::ROLE_PREENROL) {
                $default['max_courses'] = 0;
            }

            $roleSettings[$role] = $setting ? json_decode($setting->value, true) : $default;
        }

        return view('admin.settings.roles', compact('roleSettings', 'roles'));
    }

    /**
     * Update the Role Capabilities settings.
     */
    public function updateRoles(Request $request)
    {
        $roles = [\App\Models\User::ROLE_STUDENT, \App\Models\User::ROLE_GUEST, \App\Models\User::ROLE_PREENROL];

        foreach ($roles as $role) {
            $maxCourses = $request->input("caps.{$role}.max_courses", -1);
            $workshopsEnabled = $request->boolean("caps.{$role}.workshops_enabled", false);

            $payload = [
                'max_courses' => (int) $maxCourses,
                'workshops_enabled' => $workshopsEnabled,
                'allowed_workshops' => [], // Add specific workshop toggles later if needed
                'allowed_modules' => [],
            ];

            \App\Models\Setting::updateOrCreate(
                ['key' => "role_capabilities_{$role}"],
                [
                    'value' => json_encode($payload),
                    'type' => 'json'
                ]
            );

            // Invalidate cache
            \Illuminate\Support\Facades\Cache::forget("role_capabilities_{$role}");
        }

        return redirect()->route('admin.settings.roles')->with('success', 'Role capabilities updated successfully.');
    }
}
