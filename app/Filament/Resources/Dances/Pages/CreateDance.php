<?php

namespace App\Filament\Resources\Dances\Pages;

use App\Filament\Resources\Dances\DanceResource;
use App\Models\Dance;
use Filament\Resources\Pages\CreateRecord;

class CreateDance extends CreateRecord
{
    protected static string $resource = DanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort'] = (int) Dance::query()->max('sort') + 1; // entra no fim da lista

        return $data;
    }
}
