<?php

namespace App\Filament\Resources\Poses\Pages;

use App\Filament\Resources\Poses\PoseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPoses extends ListRecords
{
    protected static string $resource = PoseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
