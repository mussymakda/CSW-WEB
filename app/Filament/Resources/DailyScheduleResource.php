<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyScheduleResource\Pages;
use App\Models\DailySchedule;
use App\Models\Participant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class DailyScheduleResource extends Resource
{
    protected static ?string $model = DailySchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Daily Schedules';

    protected static ?string $modelLabel = 'Daily Schedule';

    protected static ?string $pluralModelLabel = 'Daily Schedules';

    protected static ?string $navigationGroup = 'Participant Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('participant_id')
                    ->label('Participant')
                    ->relationship('participant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participant.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
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
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
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
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'High',
                        2 => 'Medium',
                        3 => 'Low',
                        default => 'Unknown',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('location')
                    ->limit(20)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('participant')
                    ->relationship('participant', 'name')
                    ->searchable()
                    ->preload(),
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
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        1 => 'High Priority',
                        2 => 'Medium Priority',
                        3 => 'Low Priority',
                    ]),
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Completion Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_completion')
                    ->label('Toggle Complete')
                    ->icon(fn (DailySchedule $record): string => $record->is_completed ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (DailySchedule $record): string => $record->is_completed ? 'danger' : 'success')
                    ->action(function (DailySchedule $record): void {
                        if ($record->is_completed) {
                            $record->update([
                                'is_completed' => false,
                                'completed_at' => null,
                                'completion_notes' => null,
                            ]);
                        } else {
                            $record->markCompleted('Marked complete via admin panel');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function (DailySchedule $record) {
                                $record->markCompleted('Bulk marked complete via admin panel');
                            });
                        }),
                    Tables\Actions\BulkAction::make('mark_incomplete')
                        ->label('Mark as Incomplete')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function (DailySchedule $record) {
                                $record->update([
                                    'is_completed' => false,
                                    'completed_at' => null,
                                    'completion_notes' => null,
                                ]);
                            });
                        }),
                ]),
            ])
            ->defaultSort('time')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailySchedules::route('/'),
            'create' => Pages\CreateDailySchedule::route('/create'),
            'edit' => Pages\EditDailySchedule::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['participant']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['task', 'participant.name', 'category', 'location'];
    }
}