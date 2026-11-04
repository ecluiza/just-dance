<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Configurações gerais em formato chave -> valor.
 * Os padrões ficam em PADROES; o que for salvo no painel sobrescreve.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const PADROES = [
        // site
        'site_title' => 'Just Dance de Pobre',
        'welcome_text' => 'Escreva cada função no quadro, clique em Testar e veja o jogo ganhar vida.',
        'allow_unlock_all' => true,
        // aula
        'class_tolerance' => 45,
        'class_smoothing' => 0.5,
        // técnico
        'min_visibility' => 0.5,
        'lead_time_ms' => 3000,
        'pose_model' => 'lite',
        'mediapipe_version' => '1.2.2',
        // pontuação
        'threshold_perfect' => 0.85,
        'threshold_great' => 0.7,
        'threshold_good' => 0.5,
        'points_perfect' => 100,
        'points_great' => 70,
        'points_good' => 40,
        'label_perfect' => 'Perfeito',
        'label_great' => 'Ótimo',
        'label_good' => 'Bom',
        'label_miss' => 'Errou',
        'combo_step' => 5,
        'combo_increment' => 0.5,
        'combo_max' => 3,
    ];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    /** Todas as configurações (padrões + o que foi salvo). */
    public static function todas(): array
    {
        $salvas = static::query()->get()->mapWithKeys(fn (Setting $s) => [$s->key => $s->value])->all();

        return array_merge(self::PADROES, array_intersect_key($salvas, self::PADROES));
    }

    public static function salvar(array $dados): void
    {
        foreach (array_intersect_key($dados, self::PADROES) as $chave => $valor) {
            static::query()->updateOrCreate(['key' => $chave], ['value' => $valor]);
        }
    }
}
