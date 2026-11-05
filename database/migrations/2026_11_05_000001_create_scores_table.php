<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ranking: uma linha por pessoa (nome) na dança + dificuldade da competição, com o melhor resultado.
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('name_key', 30);              // nome em minúsculas, sem espaços extras (para não duplicar)
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('perfect')->default(0);
            $table->unsignedInteger('combo_max')->default(0);
            $table->unsignedInteger('poses')->default(0);
            $table->foreignId('dance_id')->nullable()->constrained()->nullOnDelete();
            $table->string('difficulty', 40);
            $table->timestamps();

            $table->unique(['name_key', 'dance_id', 'difficulty']);
            $table->index(['dance_id', 'difficulty', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
