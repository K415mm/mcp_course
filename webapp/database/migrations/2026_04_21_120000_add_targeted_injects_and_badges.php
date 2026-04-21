<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add target_team_type to cs_injects for team-specific inject delivery
        Schema::table('cs_injects', function (Blueprint $table) {
            $table->string('target_team_type', 50)->nullable()->after('is_surprise')
                ->comment('If set, this inject is only shown to this team type (e.g. ancs, cert, finance)');
        });

        // cs_badges — bonus badges awarded by the moderator
        Schema::create('cs_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_team_id')->constrained()->cascadeOnDelete();
            $table->string('badge_type', 50); // first_responder | crisis_communicator | zero_silo | innovation
            $table->string('badge_label', 100)->nullable();
            $table->string('badge_icon', 20)->nullable();
            $table->integer('bonus_points')->default(5);
            $table->foreignId('awarded_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_badges');
        Schema::table('cs_injects', function (Blueprint $table) {
            $table->dropColumn('target_team_type');
        });
    }
};
