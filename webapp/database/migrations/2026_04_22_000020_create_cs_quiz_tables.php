<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->default('single_choice');
            $table->string('question', 500);
            $table->text('prompt')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->unsignedSmallInteger('base_points')->default(0);
            $table->boolean('is_open')->default(true);
            $table->json('results')->nullable();
            $table->unsignedSmallInteger('phase_index');
            $table->timestamps();
        });

        Schema::create('cs_quiz_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cs_team_id')->constrained('cs_teams')->cascadeOnDelete();
            $table->string('answer_key', 100)->nullable();
            $table->text('answer_text')->nullable();
            $table->unsignedSmallInteger('awarded_points')->default(0);
            $table->timestamp('answered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_quiz_entries');
        Schema::dropIfExists('cs_quizzes');
    }
};
