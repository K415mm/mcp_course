<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_entities', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->unique();
            $table->string('name', 60);
            $table->string('role_label', 100);
            $table->string('color', 20)->default('#00b4d8');
            $table->string('icon', 10)->default('🛡️');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_entities');
    }
};
