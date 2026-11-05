<?php

namespace App\Filament\Resources\Poses\Pages;

use App\Filament\Resources\Poses\PoseResource;
use App\Models\Pose;
use Filament\Resources\Pages\CreateRecord;

class CreatePose extends CreateRecord
{
    protected static string $resource = PoseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort'] = (int) Pose::query()->max('sort') + 1;

        return $data;
    }
}
