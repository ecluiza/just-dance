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
        $problemas = Mission::problemas();

        return $problemas === []
            ? 'Arraste para mudar a ordem. A turma faz os experimentos ativos nesta ordem e o jogo libera no fim.'
            : '⚠️ '.implode(' · ', $problemas);
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
