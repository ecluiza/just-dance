<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Uma missão da aula = um experimento com a câmera + um desafio que a pessoa cumpre na tela.
 * Os alvos do desafio ficam em `settings` e mudam conforme o tipo.
 */
class Mission extends Model
{
    public const TIPOS = [
        'pixels' => 'Pixels: a imagem é feita de números',
        'cores' => 'Cores e contraste: canais e limiar',
        'bordas' => 'Bordas: filtro que acha contornos',
        'pontos' => 'Pontos do corpo: a IA e a confiança',
        'angulo' => 'Ângulos: do ponto à característica',
        'comparar' => 'Comparar poses: nota e tolerância',
        'livre' => 'Livre: discussão (a pessoa clica em Concluir)',
    ];

    /** Pontos do corpo do MediaPipe que fazem sentido nos desafios. */
    public const PONTOS = [
        0 => 'nariz',
        11 => 'ombro esquerdo', 12 => 'ombro direito',
        13 => 'cotovelo esquerdo', 14 => 'cotovelo direito',
        15 => 'pulso esquerdo', 16 => 'pulso direito',
        23 => 'quadril esquerdo', 24 => 'quadril direito',
    ];

    public const OSSOS = [
        'bracoE' => 'braço esquerdo',
        'antebracoE' => 'antebraço esquerdo',
        'bracoD' => 'braço direito',
        'antebracoD' => 'antebraço direito',
    ];

    protected $fillable = ['title', 'type', 'concept', 'challenge', 'unlock_message', 'settings', 'code_snippet', 'active', 'sort'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'active' => 'boolean',
        ];
    }

    /** O que está faltando nas missões ativas para o desafio funcionar (aparece no painel). */
    public static function problemas(): array
    {
        $lista = [];

        foreach (static::query()->where('active', true)->orderBy('sort')->get() as $m) {
            $a = $m->settings ?? [];
            $falta = match ($m->type) {
                'comparar' => empty($a['pose_id']) || ! Pose::query()->whereKey($a['pose_id'])->exists() ? 'escolha a pose-alvo' : null,
                'angulo' => empty($a['alvos']) ? 'adicione pelo menos uma posição' : null,
                'pontos' => empty($a['pontos']) ? 'marque pelo menos um ponto' : null,
                'cores' => empty($a['modos']) ? 'marque pelo menos um filtro' : null,
                default => array_key_exists($m->type, static::TIPOS) ? null : 'tipo desconhecido',
            };

            if ($falta) {
                $lista[] = "\"{$m->title}\": {$falta}";
            }
        }

        return $lista;
    }
}
