<?php

namespace App\Filament\Resources\ParticipantProgressResource\Pages;

use App\Filament\Resources\ParticipantProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParticipantProgress extends ListRecords
{
    protected static string $resource = ParticipantProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import Progress Data')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->url('/admin/import-participant-progress'),
        ];
    }
}
