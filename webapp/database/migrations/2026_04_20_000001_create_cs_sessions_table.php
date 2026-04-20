<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique(); // short join code e.g. "SHIELD42"
            $table->string('scenario_key')->default('phantom_grid'); // slug of scenario
            $table->string('status', 20)->default('lobby'); // lobby|active|paused|finished
            $table->unsignedBigInteger('moderator_id')->nullable();
            $table->foreign('moderator_id')->references('id')->on('users')->nullOnDelete();

            // Phase tracking
            $table->unsignedSmallInteger('current_phase_index')->default(0); // 0–5

            // Server-side timer: when the current phase timer expires (null = not running)
            $table->timestamp('timer_ends_at')->nullable();
            $table->unsignedInteger('timer_paused_remaining')->nullable(); // seconds left when paused

            // Atmosphere: calm|tension|crisis|hacked|victory
            $table->string('atmosphere', 20)->default('calm');

            // Flexible config: max_phases, custom phase names, etc.
            $table->json('settings')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_sessions');
    }
};
