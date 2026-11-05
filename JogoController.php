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
        return view('jogo', ['config' => ConfigDoJogo::montar()]);
    }

    /** A mesma configuração em JSON (útil para conferir o que o jogo está recebendo). */
    public function config(): JsonResponse
    {
        return response()->json(ConfigDoJogo::montar());
    }
}
