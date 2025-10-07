<?php

namespace App\Filament\Resources\ParticipantResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->seconds(false),
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
                Forms\Components\Select::make('priority')
                    ->options([
                        1 => 'High Priority',
                        2 => 'Medium Priority',
                        3 => 'Low Priority',
                    ])
                    ->default(2)
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'fitness' => 'Fitness',
                        'nutrition' => 'Nutrition',
                        'work' => 'Work',
                        'education' => 'Education',
                        'family' => 'Family',
                        'social' => 'Social',
                        'routine' => 'Routine',
                        'wellness' => 'Wellness',
                        'travel' => 'Travel',
                        'planning' => 'Planning',
                        'errands' => 'Errands',
                        'hobby' => 'Hobby',
                        'leisure' => 'Leisure',
                        'general' => 'General',
                    ])
                    ->default('general')
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_completed')
                    ->label('Completed'),
                Forms\Components\Textarea::make('completion_notes')
                    ->label('Completion Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task')
            ->columns([
                Tables\Columns\TextColumn::make('day')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'monday' => 'info',
                        'tuesday' => 'success',
                        'wednesday' => 'warning',
                        'thursday' => 'danger',
                        'friday' => 'primary',
                        'saturday' => 'gray',
                        'sunday' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('task')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fitness' => 'success',
                        'nutrition' => 'info',
                        'work' => 'warning',
                        'education' => 'primary',
                        'family' => 'secondary',
                        'social' => 'gray',
                        'routine' => 'info',
                        'wellness' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => match ($state) {
                        1 => 'High',
                        2 => 'Medium',
                        3 => 'Low',
                        default => 'Medium',
                    })
                    ->color(fn (?int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('✓')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'fitness' => 'Fitness',
                        'nutrition' => 'Nutrition',
                        'work' => 'Work',
                        'education' => 'Education',
                        'family' => 'Family',
                        'social' => 'Social',
                        'routine' => 'Routine',
                        'wellness' => 'Wellness',
                        'travel' => 'Travel',
                        'planning' => 'Planning',
                        'errands' => 'Errands',
                        'hobby' => 'Hobby',
                        'leisure' => 'Leisure',
                        'general' => 'General',
                    ]),
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Completion Status'),
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
