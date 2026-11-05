<?php

namespace App\Filament\Resources\Dances\Pages;

use App\Filament\Resources\Dances\DanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDances extends ListRecords
{
    protected static string $resource = DanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
