<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    /** Funções que o jogo precisa que alguma missão ativa defina. */
    public const FUNCOES_DO_JOGO = [
        'abrirCamera' => 'abrirCamera(video): abre a câmera (missão do tipo "câmera")',
        'encontrarCorpo' => 'encontrarCorpo(detector, video, agoraMs): devolve os pontos da 1ª pessoa ou null',
        'direcao' => 'direcao(a, b, largura, altura): direção do osso em graus',
        'comparar' => 'comparar(aluno, alvo, tolerancia): devolve { nota, porOsso }',
    ];

    protected $fillable = ['title', 'concept', 'unlock_message', 'check_type', 'exports', 'starter_code', 'answer_code', 'active', 'sort'];

    protected function casts(): array
    {
        return [
            'exports' => 'array',
            'active' => 'boolean',
        ];
    }

    public function tests(): HasMany
    {
        return $this->hasMany(MissionTest::class)->orderBy('sort');
    }

    /** Funções do jogo que nenhuma missão ativa define (a configuração está incompleta se não for vazio). */
    public static function funcoesFaltando(): array
    {
        $definidas = static::query()->where('active', true)->get()
            ->flatMap(fn (Mission $m) => $m->exports ?? [])
            ->all();

        return array_values(array_diff(array_keys(self::FUNCOES_DO_JOGO), $definidas));
    }
}
