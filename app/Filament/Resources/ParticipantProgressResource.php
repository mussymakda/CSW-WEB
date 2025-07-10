<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantProgressResource\Pages;
use App\Models\ParticipantCourseProgress;
use App\Models\Participant;
use App\Models\CourseBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantProgressResource extends Resource
{
    protected static ?string $model = ParticipantCourseProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected static ?string $navigationGroup = 'Course Management';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $navigationLabel = 'Progress Tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('participant_id')
                    ->relationship('participant', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('course_batch_id')
                    ->relationship('courseBatch', 'batch_name')
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('enrollment_date')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('started_at'),
                Forms\Components\DatePicker::make('completed_at'),
                Forms\Components\TextInput::make('progress_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->suffix('%')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'enrolled' => 'Enrolled',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                        'paused' => 'Paused',
                    ])
                    ->default('enrolled')
                    ->required(),
                Forms\Components\TextInput::make('grade')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix(' points'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participant.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('courseBatch.course.name')
                    ->label('Course')
                    ->searchable(),
                Tables\Columns\TextColumn::make('courseBatch.batch_name')
                    ->label('Batch')
                    ->searchable(),
                Tables\Columns\ProgressColumn::make('progress_percentage')
                    ->label('Progress'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'enrolled',
                        'info' => 'active',
                        'success' => 'completed',
                        'secondary' => 'paused',
                        'danger' => 'dropped',
                    ]),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->suffix(' pts')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'enrolled' => 'Enrolled',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'dropped' => 'Dropped',
                        'paused' => 'Paused',
                    ]),
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('courseBatch.course', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('enrollment_date', 'desc');
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
            'index' => Pages\ListParticipantProgress::route('/'),
            'create' => Pages\CreateParticipantProgress::route('/create'),
            'view' => Pages\ViewParticipantProgress::route('/{record}'),
            'edit' => Pages\EditParticipantProgress::route('/{record}/edit'),
        ];
    }
}
