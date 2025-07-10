<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

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

                Infolists\Components\Section::make('Course Progress Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_courses')
                            ->label('Total Enrolled Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->count())
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('completed_courses')
                            ->label('Completed Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->where('status', 'completed')->count())
                            ->badge()
                            ->color('success'),
                        Infolists\Components\TextEntry::make('active_courses')
                            ->label('Active Courses')
                            ->getStateUsing(fn ($record) => $record->courseProgress()->whereIn('status', ['enrolled', 'active'])->count())
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('average_progress')
                            ->label('Average Progress')
                            ->getStateUsing(fn ($record) => round($record->courseProgress()->avg('progress_percentage') ?? 0, 1) . '%')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(4),                Infolists\Components\Section::make('Current Course Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('current_course_name')
                            ->label('Course Name')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->with('courseBatch.course')
                                    ->first();
                                return $current ? $current->courseBatch->course->name : 'No active course';
                            })
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('current_batch')
                            ->label('Batch')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->with('courseBatch')
                                    ->first();
                                return $current ? $current->courseBatch->batch_name : 'N/A';
                            })
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('enrollment_date')
                            ->label('Enrollment Date')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                return $current ? $current->enrollment_date->format('M d, Y') : 'N/A';
                            })
                            ->icon('heroicon-m-calendar-days'),
                        Infolists\Components\TextEntry::make('course_status')
                            ->label('Status')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                return $current ? ucfirst($current->status) : 'N/A';
                            })
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'gray';
                                return match ($current->status) {
                                    'completed' => 'success',
                                    'active' => 'info',
                                    'enrolled' => 'warning',
                                    'paused' => 'gray',
                                    'dropped' => 'danger',
                                    default => 'gray',
                                };
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->courseProgress()->whereIn('status', ['enrolled', 'active'])->exists()),

                Infolists\Components\Section::make('Progress Tracking')
                    ->schema([
                        Infolists\Components\TextEntry::make('test_progress')
                            ->label('Test Progress')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'No active course';
                                return $current->tests_passed . ' / ' . $current->total_tests . ' tests passed (' . $current->test_progress_percentage . '%)';
                            })
                            ->icon('heroicon-m-academic-cap')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'gray';
                                $progress = $current->test_progress_percentage;
                                return match (true) {
                                    $progress >= 80 => 'success',
                                    $progress >= 60 => 'warning',
                                    $progress >= 40 => 'info',
                                    default => 'danger',
                                };
                            }),
                        Infolists\Components\TextEntry::make('time_progress')
                            ->label('Time Progress')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'No active course';
                                return $current->time_progress_percentage . '% of course duration completed';
                            })
                            ->icon('heroicon-m-clock')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'gray';
                                $progress = $current->time_progress_percentage;
                                return match (true) {
                                    $progress >= 80 => 'success',
                                    $progress >= 60 => 'warning',
                                    $progress >= 40 => 'info',
                                    default => 'danger',
                                };
                            }),
                        Infolists\Components\TextEntry::make('overall_progress')
                            ->label('Overall Progress')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'No active course';
                                return $current->overall_progress . '% (Combined test & time progress)';
                            })
                            ->icon('heroicon-m-chart-bar')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current) return 'gray';
                                $progress = $current->overall_progress;
                                return match (true) {
                                    $progress >= 80 => 'success',
                                    $progress >= 60 => 'warning',
                                    $progress >= 40 => 'info',
                                    default => 'danger',
                                };
                            }),
                        Infolists\Components\TextEntry::make('average_score')
                            ->label('Average Test Score')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current || !$current->average_score) return 'No scores yet';
                                return number_format($current->average_score, 1) . '% average';
                            })
                            ->icon('heroicon-m-star')
                            ->badge()
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current || !$current->average_score) return 'gray';
                                $score = $current->average_score;
                                return match (true) {
                                    $score >= 90 => 'success',
                                    $score >= 80 => 'warning',
                                    $score >= 70 => 'info',
                                    default => 'danger',
                                };
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->courseProgress()->whereIn('status', ['enrolled', 'active'])->exists()),

                Infolists\Components\Section::make('Course Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('course_start_date')
                            ->label('Course Start Date')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->with('courseBatch')
                                    ->first();
                                return $current ? $current->courseBatch->start_date->format('M d, Y') : 'N/A';
                            })
                            ->icon('heroicon-m-play'),
                        Infolists\Components\TextEntry::make('course_end_date')
                            ->label('Course End Date')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->with('courseBatch')
                                    ->first();
                                return $current ? $current->courseBatch->end_date->format('M d, Y') : 'N/A';
                            })
                            ->icon('heroicon-m-flag'),
                        Infolists\Components\TextEntry::make('days_enrolled')
                            ->label('Days Enrolled')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                return $current ? $current->days_enrolled . ' days' : 'N/A';
                            })
                            ->icon('heroicon-m-calendar'),
                        Infolists\Components\TextEntry::make('days_remaining')
                            ->label('Days Remaining')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                return $current && $current->days_remaining ? $current->days_remaining . ' days' : 'Course ended';
                            })
                            ->icon('heroicon-m-clock')
                            ->color(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                if (!$current || !$current->days_remaining) return 'gray';
                                return $current->days_remaining <= 7 ? 'danger' : 'primary';
                            }),                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->courseProgress()->whereIn('status', ['enrolled', 'active'])->exists()),

                Infolists\Components\Section::make('Visual Progress Overview')
                    ->schema([
                        Infolists\Components\ViewEntry::make('progress_bars')
                            ->label('')
                            ->view('filament.infolists.progress-overview')
                            ->getStateUsing(function ($record) {
                                $current = $record->courseProgress()
                                    ->whereIn('status', ['enrolled', 'active'])
                                    ->first();
                                
                                if (!$current) {
                                    return null;
                                }
                                
                                return [
                                    'test_progress' => $current->test_progress_percentage,
                                    'time_progress' => $current->time_progress_percentage,
                                    'overall_progress' => $current->overall_progress,
                                    'tests_passed' => $current->tests_passed,
                                    'total_tests' => $current->total_tests,
                                    'tests_taken' => $current->tests_taken,
                                    'average_score' => $current->average_score,
                                ];
                            }),
                    ])
                    ->visible(fn ($record) => $record->courseProgress()->whereIn('status', ['enrolled', 'active'])->exists()),
            ]);
    }
}
