<?php

namespace App\Support;

/**
 * Desenha o boneco de uma pose em SVG (prévia no painel), do mesmo jeito que o jogo desenha o "fantasma":
 * espelhado, como a pessoa se vê na tela.
 */
class BonecoSvg
{
    /** @param array{bracoE?:float|int|string|null, antebracoE?:mixed, bracoD?:mixed, antebracoD?:mixed} $angulos */
    public static function render(array $angulos, int $tamanho = 160, string $cor = '#2de2e6'): string
    {
        $w = $h = 100.0;
        $escala = 26.0;
        $topo = 45.0;
        $ombroE = [$w / 2 + $escala / 2, $topo]; // na imagem original o ombro esquerdo fica à direita
        $ombroD = [$w / 2 - $escala / 2, $topo];

        $ponta = function (array $de, $graus, float $tamanhoOsso) use ($escala): ?array {
            if ($graus === null || $graus === '') {
                return null;
            }
            $r = deg2rad((float) $graus);

            return [$de[0] + cos($r) * $tamanhoOsso * $escala, $de[1] + sin($r) * $tamanhoOsso * $escala];
        };
        $espelho = fn (array $p) => [$w - $p[0], $p[1]];
        $linhas = [];
        $tracar = function (array $a, array $b, float $g) use (&$linhas, $espelho) {
            [$x1, $y1] = $espelho($a);
            [$x2, $y2] = $espelho($b);
            $linhas[] = sprintf('<line x1="%.1f" y1="%.1f" x2="%.1f" y2="%.1f" stroke-width="%.1f"/>', $x1, $y1, $x2, $y2, $g);
        };

        $quadrilE = [$ombroE[0] - $escala * 0.15, $ombroE[1] + $escala * 1.4];
        $quadrilD = [$ombroD[0] + $escala * 0.15, $ombroD[1] + $escala * 1.4];
        $fino = 2.2;
        $grosso = 4.0;
        $tracar($ombroE, $ombroD, $fino);
        $tracar($ombroE, $quadrilE, $fino);
        $tracar($ombroD, $quadrilD, $fino);
        $tracar($quadrilE, $quadrilD, $fino);

        foreach ([[$ombroE, 'bracoE', 'antebracoE'], [$ombroD, 'bracoD', 'antebracoD']] as [$ombro, $braco, $antebraco]) {
            $cotovelo = $ponta($ombro, $angulos[$braco] ?? null, 0.85);
            if (! $cotovelo) {
                continue;
            }
            $tracar($ombro, $cotovelo, $grosso);
            $pulso = $ponta($cotovelo, $angulos[$antebraco] ?? null, 0.75);
            if ($pulso) {
                $tracar($cotovelo, $pulso, $grosso);
            }
        }

        $pescoco = $espelho([($ombroE[0] + $ombroD[0]) / 2, $ombroE[1]]);
        $cabeca = sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="none" stroke-width="%.1f"/>', $pescoco[0], $pescoco[1] - $escala * 0.55, $escala * 0.3, $fino);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="%d" height="%d" style="background:#221d3d;border-radius:12px">'.
            '<g stroke="%s" stroke-linecap="round" fill="none">%s%s</g></svg>',
            $tamanho, $tamanho, htmlspecialchars($cor), $cabeca, implode('', $linhas),
        );
    }
}
