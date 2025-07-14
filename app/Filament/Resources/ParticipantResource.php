<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Participant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\DatePicker::make('dob')
                    ->label('Date of Birth'),
                Forms\Components\FileUpload::make('profile_picture')
                    ->disk('public')
                    ->directory('profiles'),
                Forms\Components\Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                Forms\Components\TextInput::make('weight')
                    ->numeric(),
                Forms\Components\TextInput::make('height')
                    ->numeric(),
                Forms\Components\TextInput::make('aceds_no')
                    ->label('ACEDS Number'),
                Forms\Components\Select::make('goal_id')
                    ->relationship('goal', 'name')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Student #')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('program_description')
                    ->label('Program')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('courseProgress.progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : 'No data')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'info',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('courseProgress.exams_taken')
                    ->label('Exams Taken')
                    ->formatStateUsing(function ($record) {
                        $progress = $record->courseProgress()->orderBy('enrollment_date', 'desc')->first();
                        if (!$progress) return 'No data';
                        return $progress->exams_taken . '/' . $progress->total_exams;
                    })
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'completed', 'graduated' => 'success',
                        'active', 'enrolled' => 'info',
                        'paused' => 'warning',
                        'dropped', 'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->default('Active'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'paused' => 'Paused',
                        'dropped' => 'Dropped',
                    ]),
                Tables\Filters\Filter::make('has_progress')
                    ->label('Has Course Progress')
                    ->query(fn ($query) => $query->whereHas('courseProgress'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
            'view' => Pages\ViewParticipant::route('/{record}'),
        ];
    }
}
