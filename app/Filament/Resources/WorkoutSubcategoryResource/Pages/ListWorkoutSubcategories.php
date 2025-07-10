<?php

namespace App\Filament\Resources\WorkoutSubcategoryResource\Pages;

use App\Filament\Resources\WorkoutSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutSubcategories extends ListRecords
{
    protected static string $resource = WorkoutSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
