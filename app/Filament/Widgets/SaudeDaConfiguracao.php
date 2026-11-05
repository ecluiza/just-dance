<?php

namespace App\Filament\Widgets;

use App\Models\Dance;
use App\Models\Mission;
use App\Models\Pose;
use App\Models\Song;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Resumo no início do painel: o que está configurado e se falta algo para o jogo funcionar. */
class SaudeDaConfiguracao extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getHeading(): ?string
    {
        return 'Como está o jogo';
    }

    protected function getStats(): array
    {
        $problemas = Mission::problemas();
        $dancasVazias = Dance::query()->where('active', true)->doesntHave('steps')->count();

        return [
            Stat::make('Missões ativas', Mission::query()->where('active', true)->count())
                ->description('etapas da aula'),
            Stat::make('Danças no jogo', Dance::query()->where('active', true)->has('steps')->count())
                ->description($dancasVazias ? "{$dancasVazias} ativa(s) sem poses (não aparecem)" : 'prontas para jogar')
                ->color($dancasVazias ? 'warning' : null),
            Stat::make('Músicas', Song::query()->where('active', true)->count())
                ->description('+ a "Batida do jogo" (sempre disponível)'),
            Stat::make('Poses', Pose::query()->count())
                ->description('na biblioteca'),
            Stat::make('Experimentos', $problemas === [] ? 'Tudo certo' : count($problemas).' para ajustar')
                ->description($problemas === [] ? 'todas as missões ativas têm alvo configurado' : implode(' · ', $problemas))
                ->color($problemas === [] ? 'success' : 'danger'),
        ];
    }
}
