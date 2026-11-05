<?php

use App\Http\Controllers\JogoController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

// O jogo (aula + jogar). O painel fica em /admin (Filament).
Route::get('/', [JogoController::class, 'index'])->name('jogo');
Route::get('/api/config', [JogoController::class, 'config'])->name('jogo.config');

// Ranking: /ranking fica aberto no telão; o jogo manda o resultado no fim de cada dança.
Route::get('/ranking', [RankingController::class, 'telao'])->name('ranking');
Route::get('/api/ranking', [RankingController::class, 'lista'])->name('ranking.lista');
Route::get('/api/token', [RankingController::class, 'token'])->name('ranking.token');
Route::post('/api/pontuacao', [RankingController::class, 'salvar'])->middleware('throttle:120,1')->name('ranking.salvar');
