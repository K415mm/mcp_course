<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GlobalSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share cinematic animations setting globally if the table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $cinematicSetting = \App\Models\Setting::where('key', 'cinematic_animations_enabled')->first();
            $cinematicAnimationsEnabled = $cinematicSetting ? $cinematicSetting->value : true;
            
            \Illuminate\Support\Facades\View::share('cinematicAnimationsEnabled', $cinematicAnimationsEnabled);
        } else {
            // Default to true during migrations or if table is missing
            \Illuminate\Support\Facades\View::share('cinematicAnimationsEnabled', true);
        }
    }
}
