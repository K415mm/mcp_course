<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cs_injects', function (Blueprint $table) {
            $table->boolean('requires_action')->default(false)->after('is_surprise');
            $table->string('expected_action_type', 40)->nullable()->after('requires_action');
        });

        Schema::table('cs_decisions', function (Blueprint $table) {
            $table->foreignId('cs_inject_id')->nullable()->after('cs_player_id')->constrained('cs_injects')->nullOnDelete();
            $table->string('expected_action_type', 40)->nullable()->after('cs_inject_id');
        });
    }

    public function down(): void
    {
        Schema::table('cs_decisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cs_inject_id');
            $table->dropColumn('expected_action_type');
        });

        Schema::table('cs_injects', function (Blueprint $table) {
            $table->dropColumn(['requires_action', 'expected_action_type']);
        });
    }
};
