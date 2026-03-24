<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_cards', function (Blueprint $table) {
            $table->string('mitre_id')->nullable()->after('effect');
            $table->string('mitre_name')->nullable()->after('mitre_id');
            $table->text('mitre_description')->nullable()->after('mitre_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_cards', function (Blueprint $table) {
            $table->dropColumn(['mitre_id', 'mitre_name', 'mitre_description']);
        });
    }
};
