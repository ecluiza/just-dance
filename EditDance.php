<?php

namespace App\Filament\Resources\Dances\Pages;

use App\Filament\Resources\Dances\DanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDance extends EditRecord
{
    protected static string $resource = DanceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
