<?php

namespace App\Filament\Resources\WorkoutVideoResource\Pages;

use App\Filament\Resources\WorkoutVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutVideos extends ListRecords
{
    protected static string $resource = WorkoutVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
