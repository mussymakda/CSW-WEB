<?php

namespace App\Filament\Widgets;

use App\Models\UserNotification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NotificationStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Notifications';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UserNotification::query()->with('participant')->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('participant.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notification_text')
                    ->label('Notification')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('Read')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }
}
