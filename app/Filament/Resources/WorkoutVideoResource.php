<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkoutVideoResource\Pages;
use App\Models\WorkoutVideo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkoutVideoResource extends Resource
{
    protected static ?string $model = WorkoutVideo::class;

    protected static ?string $navigationIcon = 'heroicon-o-play';

    protected static ?string $navigationGroup = 'Workouts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('workout-videos')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('300')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->previewable(false),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('Duration (minutes)')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->required()
                    ->maxLength(500),
                Forms\Components\Select::make('workout_subcategory_id')
                    ->label('Workout Subcategory')
                    ->relationship('workoutSubcategory', 'title')
                    ->required()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('workoutSubcategory'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->disk('public')
                    ->size(50),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn (string $state): string => $state.' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('video_url')
                    ->label('Video URL')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getState();
                    }),
                Tables\Columns\TextColumn::make('workoutSubcategory.title')
                    ->label('Subcategory')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListWorkoutVideos::route('/'),
            'create' => Pages\CreateWorkoutVideo::route('/create'),
            'edit' => Pages\EditWorkoutVideo::route('/{record}/edit'),
        ];
    }
}
