<?php

namespace App\Filament\Resources\ParticipantProgressResource\Pages;

use App\Filament\Resources\ParticipantProgressResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewParticipantProgress extends ViewRecord
{
    protected static string $resource = ParticipantProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Participant Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('participant.name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('participant.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('participant.phone')
                            ->label('Phone'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Course Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('courseBatch.course.name')
                            ->label('Course'),
                        Infolists\Components\TextEntry::make('courseBatch.batch_name')
                            ->label('Batch'),
                        Infolists\Components\TextEntry::make('courseBatch.start_date')
                            ->label('Start Date')
                            ->date(),
                        Infolists\Components\TextEntry::make('courseBatch.end_date')
                            ->label('End Date')
                            ->date(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Progress Information')->schema([
                    Infolists\Components\TextEntry::make('progress_percentage')
                        ->label('Course Progress')
                        ->formatStateUsing(fn ($state) => $state.'%')
                        ->badge()
                        ->color(fn ($state): string => match (true) {
                            $state >= 90 => 'success',
                            $state >= 70 => 'info',
                            $state >= 50 => 'warning',
                            default => 'danger',
                        }),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'enrolled' => 'warning',
                            'active' => 'info',
                            'completed' => 'success',
                            'paused' => 'gray',
                            'dropped' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('enrollment_date')
                        ->label('Enrollment Date')
                        ->date(),
                    Infolists\Components\TextEntry::make('started_at')
                        ->label('Started At')
                        ->date()
                        ->placeholder('Not started yet'),
                    Infolists\Components\TextEntry::make('completed_at')
                        ->label('Completed At')
                        ->date()
                        ->placeholder('Not completed yet'),
                    Infolists\Components\TextEntry::make('grade')
                        ->label('Final Grade')
                        ->suffix(' pts')
                        ->placeholder('Not graded yet'),
                ])
                    ->columns(3),

                Infolists\Components\Section::make('Additional Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes available')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
