<?php

use App\Http\Controllers\JogoController;
use Illuminate\Support\Facades\Route;

// O jogo (aula + jogar). O painel fica em /admin (Filament).
Route::get('/', [JogoController::class, 'index'])->name('jogo');
Route::get('/api/config', [JogoController::class, 'config'])->name('jogo.config');
