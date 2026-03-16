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
}
