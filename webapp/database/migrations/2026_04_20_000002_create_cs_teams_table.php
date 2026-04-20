<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cs_session_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // ancs|cert|finance|transport|egov|comm
            $table->string('name', 60);
            $table->string('role_label', 100);
            $table->string('color', 20)->default('#00b4d8');
            $table->string('icon', 10)->default('🛡️');
            $table->integer('score')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_teams');
    }
};
