<?php

namespace App\Filament\Resources\Poses\Pages;

use App\Filament\Resources\Poses\PoseResource;
use App\Models\Pose;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPose extends EditRecord
{
    protected static string $resource = PoseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->disabled(fn (Pose $record): bool => $record->steps()->exists()),
        ];
    }
}
