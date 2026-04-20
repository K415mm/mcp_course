<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_team_id')->constrained()->cascadeOnDelete();
            // Optional user link — null for anonymous participants
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name', 80);
            $table->boolean('is_captain')->default(false);
            $table->timestamp('last_seen_at')->nullable(); // heartbeat tracking
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_players');
    }
};
