<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tudo o que o jogo usa e que antes estava escrito direto no código:
 * poses, músicas, danças (e seus passos), dificuldades, missões (experimentos) e configurações gerais.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Biblioteca de poses: a direção (em graus) de cada osso do braço.
        Schema::create('poses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->smallInteger('arm_left');      // ombro esq. -> cotovelo esq.
            $table->smallInteger('forearm_left');  // cotovelo esq. -> pulso esq.
            $table->smallInteger('arm_right');
            $table->smallInteger('forearm_right');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Músicas: arquivo de áudio + BPM. A pessoa escolhe a música no menu do jogo.
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('artist')->nullable();
            $table->string('audio_path');
            $table->unsignedSmallInteger('bpm');
            $table->integer('offset_ms')->default(0); // atraso até a primeira batida da música
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Danças: a sequência de poses, contada em batidas (funciona com qualquer música).
        Schema::create('dances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('bpm')->default(120);  // ritmo da batida sintetizada ("Batida do jogo")
            $table->unsignedTinyInteger('beats_per_pose')->default(4);
            $table->unsignedTinyInteger('intro_beats')->default(8);
            $table->foreignId('song_id')->nullable()->constrained()->nullOnDelete(); // música sugerida (vem marcada no menu)
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('dance_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pose_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('beats')->nullable(); // vazio = usa o padrão da dança
            $table->unsignedInteger('sort')->default(0);
        });

        Schema::create('difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('tolerance');  // graus
            $table->unsignedSmallInteger('window_ms'); // janela de tempo de cada pose
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Missões (etapas da aula): cada uma é um experimento com a câmera e um desafio.
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');                    // pixels | cores | bordas | pontos | angulo | comparar | livre
            $table->text('concept')->nullable();       // o que a turma aprende
            $table->text('challenge')->nullable();     // o que a pessoa tem que fazer
            $table->string('unlock_message')->nullable();
            $table->json('settings')->nullable();      // alvos do desafio (variam por tipo)
            $table->text('code_snippet')->nullable();  // "Ver como é feito"
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Configurações gerais (chave -> valor).
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('difficulties');
        Schema::dropIfExists('dance_steps');
        Schema::dropIfExists('dances');
        Schema::dropIfExists('songs');
        Schema::dropIfExists('poses');
    }
};
