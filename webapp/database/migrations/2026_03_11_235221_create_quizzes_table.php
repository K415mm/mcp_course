<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('module_slug');
            $table->string('lesson_slug');
            $table->json('questions');   // [{q, options[], answer, explanation}]
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['module_slug', 'lesson_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
