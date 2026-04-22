<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_teams', function (Blueprint $table) {
            $table->boolean('is_scored')->default(true)->after('score');
            $table->boolean('can_vote')->default(true)->after('is_scored');
            $table->boolean('badge_eligible')->default(true)->after('can_vote');
            $table->boolean('show_in_ranking')->default(true)->after('badge_eligible');
            $table->string('role_mode', 20)->default('participant')->after('show_in_ranking');
        });

        Schema::table('cs_votes', function (Blueprint $table) {
            $table->boolean('is_secret')->default(false)->after('is_open');
        });
    }

    public function down(): void
    {
        Schema::table('cs_votes', function (Blueprint $table) {
            $table->dropColumn('is_secret');
        });

        Schema::table('cs_teams', function (Blueprint $table) {
            $table->dropColumn([
                'is_scored',
                'can_vote',
                'badge_eligible',
                'show_in_ranking',
                'role_mode',
            ]);
        });
    }
};
