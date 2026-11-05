<?php

namespace App\Support;

use App\Models\Dance;
use App\Models\Difficulty;
use App\Models\Score;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

/**
 * Regras do ranking. Vale para a "dança da competição" escolhida em Configurações:
 * todo mundo dança a mesma coisa, na mesma dificuldade, para a comparação ser justa.
 */
class Ranking
{
    /** A competição atual: ['danca' => Dance, 'dificuldade' => Difficulty] ou null se estiver desligada. */
    public static function competicao(): ?array
    {
        $a = Setting::todas();
        $danca = $a['competition_dance_id'] ? Dance::query()->where('active', true)->whereKey($a['competition_dance_id'])->withCount('steps')->first() : null;
        $dificuldade = $a['competition_difficulty_id'] ? Difficulty::query()->find($a['competition_difficulty_id']) : null;

        if (! $danca || ! $dificuldade || $danca->steps_count === 0) {
            return null;
        }

        return ['danca' => $danca, 'dificuldade' => $dificuldade];
    }

    public static function consulta(array $competicao): Builder
    {
        return Score::query()
            ->where('dance_id', $competicao['danca']->id)
            ->where('difficulty', $competicao['dificuldade']->name);
    }

    /** O máximo que dá para fazer: todas as poses no Perfeito com o combo no máximo. */
    public static function maximoPossivel(array $competicao): int
    {
        $a = Setting::todas();

        return (int) ceil($competicao['danca']->steps_count * (int) $a['points_perfect'] * max(1, (float) $a['combo_max']));
    }

    public static function posicaoDe(array $competicao, int $pontos): int
    {
        return self::consulta($competicao)->where('points', '>', $pontos)->count() + 1;
    }

    /** Os primeiros colocados, no formato que o jogo e o telão usam. */
    public static function top(array $competicao, int $quantos): array
    {
        $lista = self::consulta($competicao)->orderByDesc('points')->orderBy('updated_at')->limit($quantos)->get();
        $saida = [];
        foreach ($lista as $s) {
            $saida[] = [
                'posicao' => self::posicaoDe($competicao, $s->points),
                'nome' => $s->name,
                'pontos' => $s->points,
                'perfeitos' => $s->perfect,
                'comboMax' => $s->combo_max,
                'novo' => $s->updated_at?->gt(now()->subSeconds(20)) ?? false,
            ];
        }

        return $saida;
    }
}
