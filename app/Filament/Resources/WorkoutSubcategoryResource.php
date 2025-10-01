<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkoutSubcategoryResource\Pages;
use App\Filament\Resources\WorkoutSubcategoryResource\RelationManagers;
use App\Models\WorkoutSubcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkoutSubcategoryResource extends Resource
{
    protected static ?string $model = WorkoutSubcategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Workouts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('info')
                    ->required()
                    ->rows(4),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('workout-subcategories')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('300')
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->previewable(false),
                Forms\Components\Select::make('goals')
                    ->label('Associated Goals')
                    ->multiple()
                    ->relationship('goals', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('workoutVideos'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('info')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->disk('public')
                    ->size(50),
                Tables\Columns\TextColumn::make('goals.name')
                    ->badge()
                    ->separator(',')
                    ->label('Goals'),
                Tables\Columns\TextColumn::make('workout_videos_count')
                    ->counts('workoutVideos')
                    ->label('Videos'),
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
            RelationManagers\WorkoutVideosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkoutSubcategories::route('/'),
            'create' => Pages\CreateWorkoutSubcategory::route('/create'),
            'edit' => Pages\EditWorkoutSubcategory::route('/{record}/edit'),
        ];
    }
}
