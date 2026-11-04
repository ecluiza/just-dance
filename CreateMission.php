<?php

namespace App\Filament\Resources\Missions\Pages;

use App\Filament\Resources\Missions\MissionResource;
use App\Models\Mission;
use Filament\Resources\Pages\CreateRecord;

class CreateMission extends CreateRecord
{
    protected static string $resource = MissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort'] = (int) Mission::query()->max('sort') + 1; // nova missão entra no fim

        return $data;
    }
}
