<?php

namespace App\Filament\Resources\WorkoutVideoResource\Pages;

use App\Filament\Resources\WorkoutVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutVideo extends EditRecord
{
    protected static string $resource = WorkoutVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
