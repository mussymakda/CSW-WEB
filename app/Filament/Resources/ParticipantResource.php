<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
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
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                    ]),

                Forms\Components\Section::make('Physical Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('weight')
                                    ->numeric()
                                    ->suffix('kg'),
                                Forms\Components\TextInput::make('height')
                                    ->numeric()
                                    ->suffix('m'),
                            ]),
                    ]),

                Forms\Components\Section::make('Academic Information')
                    ->schema([
                        Forms\Components\TextInput::make('student_number')
                            ->label('Student Number'),
                        Forms\Components\TextInput::make('aceds_no')
                            ->label('ACEDS Number'),
                        Forms\Components\Select::make('goal_id')
                            ->relationship('goal', 'name')
                            ->label('Primary Goal'),
                    ]),

                Forms\Components\Section::make('Onboarding Status')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('email_verified_at')
                                    ->label('Email Verified')
                                    ->disabled()
                                    ->formatStateUsing(fn($state) => !is_null($state)),
                                Forms\Components\Toggle::make('password_changed_from_default')
                                    ->label('Password Changed')
                                    ->disabled(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('terms_accepted')
                                    ->label('Terms Accepted')
                                    ->disabled(),
                                Forms\Components\Toggle::make('onboarding_completed')
                                    ->label('Onboarding Completed')
                                    ->disabled(),
                            ]),
                    ]),

                Forms\Components\Section::make('Profile Picture')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_picture')
                            ->disk('public')
                            ->directory('profiles')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->previewable(false),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['goal', 'courseProgress']))
            ->defaultPaginationPageOption(25)
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
                Tables\Columns\IconColumn::make('onboarding_completed')
                    ->label('Onboarded')
                    ->boolean()
                    ->toggleable()
                    ->tooltip('Onboarding completed'),
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
                    ->formatStateUsing(fn ($state) => $state ? $state.'%' : 'No data')
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
                        if (! $progress) {
                            return 'No data';
                        }

                        return $progress->exams_taken.'/'.$progress->total_exams;
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
            RelationManagers\DailySchedulesRelationManager::class,
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
