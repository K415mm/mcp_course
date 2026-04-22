<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_entities', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('type');
            $table->boolean('is_scored')->default(true)->after('logo_path');
            $table->boolean('can_vote')->default(true)->after('is_scored');
            $table->boolean('badge_eligible')->default(true)->after('can_vote');
            $table->boolean('show_in_ranking')->default(true)->after('badge_eligible');
            $table->string('role_mode', 20)->default('participant')->after('show_in_ranking');
        });

        // Keep ANCS mentor defaults aligned with role restrictions.
        \Illuminate\Support\Facades\DB::table('cs_entities')
            ->where('type', 'ancs')
            ->update([
                'is_scored' => false,
                'can_vote' => false,
                'badge_eligible' => false,
                'show_in_ranking' => false,
                'role_mode' => 'mentor',
            ]);
    }

    public function down(): void
    {
        Schema::table('cs_entities', function (Blueprint $table) {
            $table->dropColumn([
                'sort_order',
                'is_scored',
                'can_vote',
                'badge_eligible',
                'show_in_ranking',
                'role_mode',
            ]);
        });
    }
};
