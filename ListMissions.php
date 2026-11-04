<?php

namespace App\Filament\Resources\Missions\Pages;

use App\Filament\Resources\Missions\MissionResource;
use App\Models\Mission;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ListMissions extends ListRecords
{
    protected static string $resource = MissionResource::class;

    public function getSubheading(): string | Htmlable | null
    {
        $faltando = Mission::funcoesFaltando();

        return $faltando === []
            ? 'Arraste para mudar a ordem das etapas. A turma vê as missões nesta ordem.'
            : '⚠️ Nenhuma missão ativa define: '.implode(', ', $faltando).'. O jogo não libera sem elas.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jogo')
                ->label('Abrir o jogo')
                ->icon(Heroicon::OutlinedPlayCircle)
                ->color('gray')
                ->url('/', shouldOpenInNewTab: true),
            CreateAction::make(),
        ];
    }
}
