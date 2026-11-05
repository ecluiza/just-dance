<?php

namespace App\Filament\Resources\Missions\Pages;

use App\Filament\Resources\Missions\MissionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditMission extends EditRecord
{
    protected static string $resource = MissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jogo')
                ->label('Testar no jogo')
                ->icon(Heroicon::OutlinedPlayCircle)
                ->color('gray')
                ->url('/', shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
