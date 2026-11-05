<?php

namespace App\Filament\Resources\Difficulties\Pages;

use App\Filament\Resources\Difficulties\DifficultyResource;
use App\Models\Difficulty;
use Filament\Resources\Pages\CreateRecord;

class CreateDifficulty extends CreateRecord
{
    protected static string $resource = DifficultyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort'] = (int) Difficulty::query()->max('sort') + 1; // entra no fim da lista

        return $data;
    }
}
