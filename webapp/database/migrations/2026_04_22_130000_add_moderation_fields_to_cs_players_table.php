<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_players', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('is_captain');
            $table->timestamp('banned_at')->nullable()->after('is_banned');
            $table->string('banned_reason', 160)->nullable()->after('banned_at');
            $table->foreignId('assigned_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('assignment_source', 20)->default('self')->after('assigned_by');
        });
    }

    public function down(): void
    {
        Schema::table('cs_players', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn([
                'assignment_source',
                'is_banned',
                'banned_at',
                'banned_reason',
            ]);
        });
    }
};
