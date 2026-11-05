<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // O jogo mudou de nome: troca o nome salvo em Configurações, se ainda estiver o antigo.
    public function up(): void
    {
        DB::table('settings')->where('key', 'site_title')->where('value', json_encode('Just Dance de Pobre'))
            ->update(['value' => json_encode('Rebola Ai')]);
    }

    public function down(): void
    {
    }
};
