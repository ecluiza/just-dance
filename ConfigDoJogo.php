<?php

namespace App\Support;

use App\Models\Dance;
use App\Models\Difficulty;
use App\Models\Mission;
use App\Models\Pose;
use App\Models\Setting;
use App\Models\Song;

/**
 * Monta a configuração que o jogo (resources/views/jogo.blade.php) recebe.
 * Tudo o que aparece na tela vem daqui: missões, testes, poses, músicas, danças, dificuldades e pontuação.
 */
class ConfigDoJogo
{
    /** Lê do banco. */
    public static function montar(): array
    {
        return self::formatar(
            ajustes: Setting::todas(),
            poses: Pose::query()->orderBy('sort')->get()->toArray(),
            musicas: Song::query()->where('active', true)->orderBy('sort')->get()
                ->map(fn (Song $m) => [...$m->toArray(), 'audio_url' => $m->url()])
                ->all(),
            dancas: Dance::query()->where('active', true)->orderBy('sort')->with('steps')->get()->toArray(),
            dificuldades: Difficulty::query()->orderBy('sort')->get()->toArray(),
            missoes: Mission::query()->where('active', true)->orderBy('sort')->with('tests')->get()->toArray(),
        );
    }

    /**
     * Transforma os dados (arrays simples) no formato do jogo.
     * Separado do banco para poder ser testado sem Laravel rodando.
     */
    public static function formatar(array $ajustes, array $poses, array $musicas, array $dancas, array $dificuldades, array $missoes): array
    {
        $a = fn (string $chave) => $ajustes[$chave] ?? Setting::PADROES[$chave];

        $posesPorId = [];
        foreach ($poses as $p) {
            $posesPorId[$p['id']] = [
                'id' => $p['id'],
                'rotulo' => $p['label'],
                'assinatura' => Pose::assinaturaDe($p),
            ];
        }

        $config = [
            'site' => [
                'titulo' => (string) $a('site_title'),
                'boasVindas' => (string) $a('welcome_text'),
                'liberarTudo' => (bool) $a('allow_unlock_all'),
            ],
            'aula' => [
                'tolerancia' => (float) $a('class_tolerance'),
                'suavizacao' => (float) $a('class_smoothing'),
            ],
            'tecnico' => [
                'visibilidadeMin' => (float) $a('min_visibility'),
                'antecedenciaMs' => (int) $a('lead_time_ms'),
                'modelo' => (string) $a('pose_model'),
                'mediapipeVersao' => (string) $a('mediapipe_version'),
            ],
            'pontuacao' => [
                // da melhor para a pior; a última classe vale para qualquer nota abaixo das outras
                'classes' => [
                    ['rotulo' => (string) $a('label_perfect'), 'minimo' => (float) $a('threshold_perfect'), 'pontos' => (int) $a('points_perfect'), 'css' => 'perfeito'],
                    ['rotulo' => (string) $a('label_great'), 'minimo' => (float) $a('threshold_great'), 'pontos' => (int) $a('points_great'), 'css' => 'otimo'],
                    ['rotulo' => (string) $a('label_good'), 'minimo' => (float) $a('threshold_good'), 'pontos' => (int) $a('points_good'), 'css' => 'bom'],
                    ['rotulo' => (string) $a('label_miss'), 'minimo' => 0.0, 'pontos' => 0, 'css' => 'errou'],
                ],
                'combo' => [
                    'passo' => max(1, (int) $a('combo_step')),
                    'incremento' => (float) $a('combo_increment'),
                    'maximo' => (float) $a('combo_max'),
                ],
            ],
            'poses' => array_values($posesPorId),
            'musicas' => array_map(fn (array $m) => [
                'id' => $m['id'],
                'nome' => $m['name'],
                'artista' => $m['artist'] ?? null,
                'url' => $m['audio_url'] ?? null,
                'bpm' => max(30, (int) $m['bpm']),
                'offsetMs' => (int) ($m['offset_ms'] ?? 0),
            ], array_values(array_filter($musicas, fn (array $m) => ! empty($m['audio_url'])))),
            'dancas' => [],
            'dificuldades' => array_map(fn (array $d) => [
                'nome' => $d['name'],
                'tolerancia' => (int) $d['tolerance'],
                'janelaMs' => (int) $d['window_ms'],
            ], $dificuldades),
            'missoes' => array_map(fn (array $m) => [
                'id' => $m['id'],
                'titulo' => $m['title'],
                'conceito' => (string) ($m['concept'] ?? ''),
                'destrava' => (string) ($m['unlock_message'] ?? ''),
                'verificacao' => $m['check_type'] === 'camera' ? 'camera' : 'testes',
                'exporta' => array_values(array_filter(array_map('trim', $m['exports'] ?? []))),
                'inicial' => (string) $m['starter_code'],
                'resposta' => (string) $m['answer_code'],
                'testes' => array_map(fn (array $t) => [
                    'descricao' => $t['description'],
                    'expressao' => $t['expression'],
                ], $m['tests'] ?? []),
            ], $missoes),
        ];

        foreach ($dancas as $d) {
            $passos = [];
            foreach ($d['steps'] ?? [] as $passo) {
                if (! isset($posesPorId[$passo['pose_id']])) {
                    continue;
                }
                $passos[] = [
                    'poseId' => $passo['pose_id'],
                    'batidas' => (int) ($passo['beats'] ?: $d['beats_per_pose']),
                ];
            }
            if ($passos === []) {
                continue; // dança sem poses não aparece no jogo
            }
            $config['dancas'][] = [
                'id' => $d['id'],
                'nome' => $d['name'],
                'bpm' => max(30, (int) $d['bpm']), // ritmo da "Batida do jogo"
                'introBatidas' => (int) $d['intro_beats'],
                'musicaSugerida' => $d['song_id'] ?? null,
                'passos' => $passos,
            ];
        }

        $config['versao'] = substr(md5(json_encode($config)), 0, 10);

        return $config;
    }
}
