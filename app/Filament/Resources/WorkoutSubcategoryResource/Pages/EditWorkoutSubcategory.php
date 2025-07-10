<?php

namespace App\Filament\Resources\WorkoutSubcategoryResource\Pages;

use App\Filament\Resources\WorkoutSubcategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkoutSubcategory extends EditRecord
{
    protected static string $resource = WorkoutSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
