<?php

namespace App\Filament\Resources\ParticipantProgressResource\Pages;

use App\Filament\Resources\ParticipantProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParticipantProgress extends EditRecord
{
    protected static string $resource = ParticipantProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
