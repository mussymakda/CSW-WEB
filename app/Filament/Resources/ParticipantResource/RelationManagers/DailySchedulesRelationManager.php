<?php

namespace App\Filament\Resources\ParticipantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailySchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'dailySchedules';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('task')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TimePicker::make('time')
                    ->required()
                    ->seconds(false)
                    ->format('h:i A'),
                Forms\Components\Select::make('day')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task')
            ->columns([
                Tables\Columns\TextColumn::make('task')
                    ->searchable(),
                Tables\Columns\TextColumn::make('time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'monday' => 'blue',
                        'tuesday' => 'green',
                        'wednesday' => 'yellow',
                        'thursday' => 'purple',
                        'friday' => 'pink',
                        'saturday' => 'orange',
                        'sunday' => 'red',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day')
            ->reorderable();
    }
}
