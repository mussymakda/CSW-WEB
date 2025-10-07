<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewParticipant extends ViewRecord
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('profile_picture')
                            ->disk('public')
                            ->size(120)
                            ->circular(),
                        Infolists\Components\TextEntry::make('name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-m-envelope'),
                        Infolists\Components\TextEntry::make('phone')
                            ->icon('heroicon-m-phone'),
                        Infolists\Components\TextEntry::make('dob')
                            ->label('Date of Birth')
                            ->date(),
                        Infolists\Components\TextEntry::make('gender')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'male' => 'blue',
                                'female' => 'pink',
                                'other' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Physical Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('weight')
                            ->suffix(' kg')
                            ->icon('heroicon-m-scale'),
                        Infolists\Components\TextEntry::make('height')
                            ->suffix(' m')
                            ->icon('heroicon-m-arrows-up-down'),
                        Infolists\Components\TextEntry::make('aceds_no')
                            ->label('ACEDS Number')
                            ->icon('heroicon-m-identification'),
                        Infolists\Components\TextEntry::make('goal.name')
                            ->label('Current Goal')
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Course & Exam Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_courses')
                            ->label('Total Enrolled Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->count())
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('completed_courses')
                            ->label('Completed Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->where('progress_percentage', 100)->count())
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('active_courses')
                            ->label('Active Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->where('progress_percentage', '<', 100)->count())
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('average_progress')
                            ->label('Average Progress')
                            ->getStateUsing(fn ($record) => round($record->courseProgress()->avg('progress_percentage') ?? 0, 1).'%')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Current Course Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_course_name')
                            ->label('Program')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->where('progress_percentage', '<', 100)
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $record->program_description : 'No active course';
                            })
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('student_number')
                            ->label('Student Number')
                            ->getStateUsing(fn ($record) => $record->student_number)
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('enrollment_date')
                            ->label('Enrollment Date')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $current->enrollment_date->toDateString() : 'N/A';
                            })
                            ->icon('heroicon-m-calendar-days'),
                        Infolists\Components\TextEntry::make('student_status')
                            ->label('Status')
                            ->getStateUsing(fn ($record) => $record->status ?? 'Active')
                            ->badge()
                            ->color(function ($record) {
                                $status = $record->status ?? 'active';

                                return match (strtolower($status)) {
                                    'completed', 'graduated' => 'success',
                                    'active', 'enrolled' => 'info',
                                    'paused' => 'warning',
                                    'dropped', 'inactive' => 'danger',
                                    default => 'gray',
                                };
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->courseProgress()->exists()),

                Infolists\Components\Section::make('Exam Progress Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('completion_percentage')
                            ->label('Completion Percentage')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $current->progress_percentage.'%' : 'No data';
                            })
                            ->icon('heroicon-m-chart-bar')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();
                                if (! $current) {
                                    return 'gray';
                                }
                                $progress = $current->progress_percentage;

                                return match (true) {
                                    $progress >= 90 => 'success',
                                    $progress >= 70 => 'warning',
                                    $progress >= 50 => 'info',
                                    default => 'danger',
                                };
                            }),
                        Infolists\Components\TextEntry::make('total_exams')
                            ->label('Total Exams')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $current->total_exams : 'No data';
                            })
                            ->icon('heroicon-m-document-text')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('exams_taken')
                            ->label('Exams Taken')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $current->exams_taken : 'No data';
                            })
                            ->icon('heroicon-m-document-check')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('exams_needed')
                            ->label('Exams Remaining')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();

                                return $current ? $current->exams_needed : 'No data';
                            })
                            ->icon('heroicon-m-clipboard-document-list')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->orderBy('enrollment_date', 'desc')
                                    ->first();
                                if (! $current || ! $current->exams_needed) {
                                    return 'success';
                                }

                                return $current->exams_needed <= 3 ? 'warning' : 'info';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->courseProgress()->exists()),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('client_name')
                            ->label('Client')
                            ->getStateUsing(fn ($record) => $record->client_name ?? 'Not specified')
                            ->icon('heroicon-m-building-office'),
                        Infolists\Components\TextEntry::make('graduation_date')
                            ->label('Expected Graduation')
                            ->getStateUsing(fn ($record) => $record->graduation_date ? $record->graduation_date->toDateString() : 'Not set')
                            ->icon('heroicon-m-academic-cap'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime('M d, Y g:i A')
                            ->icon('heroicon-m-user-plus'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M d, Y g:i A')
                            ->icon('heroicon-m-clock'),
                    ])
                    ->columns(2),
            ]);
    }
}
