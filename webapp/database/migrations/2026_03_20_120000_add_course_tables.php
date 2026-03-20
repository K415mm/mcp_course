<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagrams', function (Blueprint $table) {
            if (!Schema::hasColumn('diagrams', 'course_slug')) {
                $table->string('course_slug')->nullable()->after('module_slug');
            }
        });

        // Create courses tracking table for admin validation
        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->string('folder_path');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('diagrams', function (Blueprint $table) {
            $table->dropColumn('course_slug');
        });
        Schema::dropIfExists('courses');
    }
};
