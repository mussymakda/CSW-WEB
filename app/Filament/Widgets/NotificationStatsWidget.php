<?php

namespace App\Filament\Widgets;

use App\Models\ParticipantCourseProgress;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NotificationStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Students Requiring Attention';

    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ParticipantCourseProgress::query()
                    ->with('participant')
                    ->where('progress_percentage', '<', 50)
                    ->whereNotNull('enrollment_date')
                    ->where('enrollment_date', '<=', now()->subDays(30)) // Enrolled for at least 30 days
                    ->orderBy('progress_percentage', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('participant.student_number')
                    ->label('Student #')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('participant.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant.program_description')
                    ->label('Program')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state < 10 => 'danger',
                        $state < 25 => 'warning',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Enrolled')
                    ->date('M j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_enrolled')
                    ->label('Days Since Enrollment')
                    ->getStateUsing(fn ($record) => $record->enrollment_date ? $record->enrollment_date->diffInDays(now()) : 'N/A')
                    ->badge()
                    ->color(fn ($state) => $state > 90 ? 'danger' : ($state > 60 ? 'warning' : 'gray')),
            ])
            ->defaultSort('progress_percentage', 'asc')
            ->emptyStateHeading('Great News!')
            ->emptyStateDescription('All students are making good progress (50%+)')
            ->emptyStateIcon('heroicon-o-face-smile')
            ->paginated([10, 25, 50]);
    }
}
