<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inject catalog — seeded per-scenario, reusable across sessions
        Schema::create('cs_injects', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_key')->default('phantom_grid');
            $table->string('tag', 80);          // "ALERTE MJ #1"
            $table->text('content');            // inject description
            $table->string('color', 20)->default('amber'); // red|amber|purple|cyan
            $table->string('phase_hint', 20)->nullable();  // suggested phase
            $table->boolean('is_surprise')->default(false); // surprise card?
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Which injects have been triggered in a session
        Schema::create('cs_session_injects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_inject_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('phase_index');
            $table->foreignId('triggered_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_session_injects');
        Schema::dropIfExists('cs_injects');
    }
};
