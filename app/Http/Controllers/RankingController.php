<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Setting;
use App\Support\Ranking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RankingController extends Controller
{
    /** O jogo manda o resultado aqui quando a dança termina. Guarda só o melhor de cada nome. */
    public function salvar(Request $request): JsonResponse
    {
        $competicao = Ranking::competicao();
        if (! $competicao) {
            return response()->json(['erro' => 'O ranking está desligado (escolha a dança da competição no painel).'], 422);
        }

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:20'],
            'pontos' => ['required', 'integer', 'min:0', 'max:'.Ranking::maximoPossivel($competicao)],
            'perfeitos' => ['required', 'integer', 'min:0', 'max:'.$competicao['danca']->steps_count],
            'comboMax' => ['required', 'integer', 'min:0', 'max:'.$competicao['danca']->steps_count],
        ]);

        $nome = trim(preg_replace('/\s+/u', ' ', strip_tags($dados['nome'])));
        if ($nome === '') {
            return response()->json(['erro' => 'Digite um nome.'], 422);
        }

        $chave = ['name_key' => Score::chaveDoNome($nome), 'dance_id' => $competicao['danca']->id, 'difficulty' => $competicao['dificuldade']->name];
        $anterior = Score::query()->where($chave)->first();
        $recorde = ! $anterior || $dados['pontos'] > $anterior->points;

        if ($recorde) {
            Score::query()->updateOrCreate($chave, [
                'name' => $nome,
                'points' => $dados['pontos'],
                'perfect' => $dados['perfeitos'],
                'combo_max' => $dados['comboMax'],
                'poses' => $competicao['danca']->steps_count,
            ]);
        }

        $melhor = $recorde ? $dados['pontos'] : $anterior->points;

        return response()->json([
            'recorde' => $recorde,
            'primeiraVez' => ! $anterior,
            'melhor' => $melhor,
            'posicao' => Ranking::posicaoDe($competicao, $melhor),
            'total' => Ranking::consulta($competicao)->count(),
            'top' => Ranking::top($competicao, 5),
        ]);
    }

    /** Lista para o telão e para o fim da dança. */
    public function lista(): JsonResponse
    {
        $competicao = Ranking::competicao();
        if (! $competicao) {
            return response()->json(['ativo' => false, 'top' => [], 'total' => 0]);
        }

        return response()->json([
            'ativo' => true,
            'danca' => $competicao['danca']->name,
            'dificuldade' => $competicao['dificuldade']->name,
            'total' => Ranking::consulta($competicao)->count(),
            'top' => Ranking::top($competicao, max(3, (int) Setting::todas()['ranking_size'])),
        ]);
    }

    /** Página para deixar aberta no telão. */
    public function telao(): View
    {
        return view('ranking', ['titulo' => Setting::todas()['site_title']]);
    }

    /** Token novo para quando a página ficou aberta muito tempo e a sessão expirou. */
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }
}
