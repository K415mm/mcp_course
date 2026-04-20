<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Moderator broadcast messages
        Schema::create('cs_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('moderator_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->text('message');
            $table->string('type', 20)->default('info'); // info|warn|alert|success
            $table->unsignedSmallInteger('phase_index');
            $table->boolean('is_phantom')->default(false); // PHANTOM GRID message flag
            $table->timestamps();
        });

        // Team decisions
        Schema::create('cs_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40); // decision|escalade|communication|question
            $table->text('content');
            $table->unsignedSmallInteger('phase_index');
            $table->unsignedSmallInteger('score_awarded')->default(0); // moderator can award points
            $table->timestamps();
        });

        // Votes
        Schema::create('cs_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->string('question', 200)->nullable();
            $table->json('options'); // [{ key: 'A', label: 'Défensive' }, ...]
            $table->boolean('is_open')->default(false);
            $table->json('results')->nullable(); // { A: 3, B: 1, C: 2 }
            $table->unsignedSmallInteger('phase_index');
            $table->timestamps();

            // Individual team votes
        });

        Schema::create('cs_vote_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_vote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_team_id')->constrained('cs_teams')->cascadeOnDelete();
            $table->string('choice', 10);
            $table->timestamp('voted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_vote_entries');
        Schema::dropIfExists('cs_votes');
        Schema::dropIfExists('cs_decisions');
        Schema::dropIfExists('cs_broadcasts');
    }
};
