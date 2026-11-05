<?php

namespace App\Http\Controllers;

use App\Support\ConfigDoJogo;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class JogoController extends Controller
{
    /** A página do jogo, já com a configuração dentro (sem requisição extra). */
    public function index(): View
    {
        $config = ConfigDoJogo::montar();

        // com servidor, o resultado da dança vai para o ranking (o GitHub Pages não tem isso)
        if ($config['competicao']) {
            $config['ranking'] = ['token' => csrf_token(), 'tamanho' => 5];
        }

        return view('jogo', ['config' => $config]);
    }

    /** A mesma configuração em JSON (útil para conferir o que o jogo está recebendo). */
    public function config(): JsonResponse
    {
        return response()->json(ConfigDoJogo::montar());
    }
}
