<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Game Sessions ───────────────────────────────────────────
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique();              // join code
            $table->unsignedTinyInteger('scenario')->default(1); // 1-4
            $table->enum('status', ['lobby', 'active', 'paused', 'finished'])->default('lobby');
            $table->unsignedSmallInteger('current_round')->default(0);
            $table->unsignedTinyInteger('current_phase')->default(0); // 0-5
            $table->foreignId('moderator_id')->constrained('users')->cascadeOnDelete();
            $table->json('settings')->nullable();              // max_rounds, timer_seconds, etc.
            $table->timestamps();
        });

        // ── Game Teams ──────────────────────────────────────────────
        Schema::create('game_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['blue', 'red']);
            $table->unsignedSmallInteger('tokens')->default(4);
            $table->unsignedSmallInteger('shop_tokens')->default(20);
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->unique(['game_session_id', 'type']);
        });

        // ── Game Players ────────────────────────────────────────────
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_captain')->default(false);
            $table->timestamps();

            $table->unique(['game_session_id', 'user_id']); // one team per session
        });

        // ── Game Cards (catalog – seeded) ───────────────────────────
        Schema::create('game_cards', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['blue', 'red', 'resource', 'event']);
            $table->string('subtype')->nullable();             // danger, success, joker, situation, alerte
            $table->string('name');
            $table->string('phase')->nullable();               // Reconnaissance, Intrusion, etc.
            $table->text('description');
            $table->text('effect')->nullable();
            $table->unsignedTinyInteger('cost')->default(0);
            $table->smallInteger('points')->default(0);
            $table->string('duration')->nullable();            // "2 tours", "Usage unique", etc.
            $table->enum('team', ['blue', 'red', 'all'])->default('all');
            $table->json('data')->nullable();                  // extra structured data
            $table->timestamps();
        });

        // ── Game Rounds ─────────────────────────────────────────────
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number');
            $table->unsignedTinyInteger('phase')->default(1);  // 1-5
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('event_card')->nullable();            // event drawn this round
            $table->timestamps();

            $table->unique(['game_session_id', 'round_number']);
        });

        // ── Game Card Plays (action log) ────────────────────────────
        Schema::create('game_card_plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_system')->nullable();       // DevOps, Cloud, Data, Infra
            $table->json('result')->nullable();                // outcome details
            $table->integer('points_earned')->default(0);
            $table->timestamp('played_at')->useCurrent();
            $table->timestamps();
        });

        // ── Game Team Cards (hand / active / used) ──────────────────
        Schema::create('game_team_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_card_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['hand', 'active', 'used', 'shop'])->default('hand');
            $table->unsignedSmallInteger('acquired_round')->default(0);
            $table->unsignedTinyInteger('remaining_turns')->nullable(); // for duration-based cards
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_team_cards');
        Schema::dropIfExists('game_card_plays');
        Schema::dropIfExists('game_rounds');
        Schema::dropIfExists('game_cards');
        Schema::dropIfExists('game_players');
        Schema::dropIfExists('game_teams');
        Schema::dropIfExists('game_sessions');
    }
};
