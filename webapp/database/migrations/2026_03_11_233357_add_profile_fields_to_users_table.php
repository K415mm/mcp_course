<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->after('email');
            $table->string('job_title')->nullable()->after('role');
            $table->text('bio')->nullable()->after('job_title');
            $table->string('avatar')->nullable()->after('bio');
            $table->json('modules_viewed')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'job_title', 'bio', 'avatar', 'modules_viewed']);
        });
    }
};
