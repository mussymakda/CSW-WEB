<?php

namespace App\Filament\Resources\DailyScheduleResource\Pages;

use App\Filament\Resources\DailyScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailySchedule extends EditRecord
{
    protected static string $resource = DailyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
