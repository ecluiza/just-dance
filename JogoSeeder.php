<?php

namespace Database\Seeders;

use App\Models\Dance;
use App\Models\Difficulty;
use App\Models\Mission;
use App\Models\Pose;
use Illuminate\Database\Seeder;

/**
 * Grava o conteúdo inicial do jogo (database/seeders/dados/jogo.php).
 * Só cria o que ainda não existe, então pode rodar de novo sem duplicar.
 */
class JogoSeeder extends Seeder
{
    public function run(): void
    {
        $dados = require __DIR__.'/dados/jogo.php';

        foreach ($dados['poses'] as $i => $pose) {
            Pose::query()->firstOrCreate(['label' => $pose['label']], [...$pose, 'sort' => $i + 1]);
        }

        foreach ($dados['difficulties'] as $i => $dificuldade) {
            Difficulty::query()->firstOrCreate(['name' => $dificuldade['name']], [...$dificuldade, 'sort' => $i + 1]);
        }

        $poses = Pose::query()->pluck('id', 'label');
        foreach ($dados['dances'] as $i => $danca) {
            $passos = $danca['steps'];
            unset($danca['steps']);
            $modelo = Dance::query()->firstOrCreate(['name' => $danca['name']], [...$danca, 'sort' => $i + 1]);
            if ($modelo->wasRecentlyCreated) {
                foreach ($passos as $j => $rotulo) {
                    $modelo->steps()->create(['pose_id' => $poses[$rotulo], 'sort' => $j + 1]);
                }
            }
        }

        foreach ($dados['missions'] as $i => $missao) {
            $testes = $missao['tests'];
            unset($missao['tests']);
            $modelo = Mission::query()->firstOrCreate(['title' => $missao['title']], [...$missao, 'sort' => $i + 1]);
            if ($modelo->wasRecentlyCreated) {
                foreach ($testes as $j => [$descricao, $expressao]) {
                    $modelo->tests()->create(['description' => $descricao, 'expression' => $expressao, 'sort' => $j + 1]);
                }
            }
        }
    }
}
