<?php

namespace App\Filament\Resources\GuidanceTipResource\Pages;

use App\Filament\Resources\GuidanceTipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuidanceTips extends ListRecords
{
    protected static string $resource = GuidanceTipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
