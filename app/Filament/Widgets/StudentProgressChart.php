<?php

namespace App\Filament\Widgets;

use App\Models\ParticipantCourseProgress;
use Filament\Widgets\ChartWidget;

class StudentProgressChart extends ChartWidget
{
    protected static ?string $heading = 'Student Progress Distribution';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get progress distribution in meaningful ranges
        $notStarted = ParticipantCourseProgress::where('progress_percentage', 0)->count();
        $beginning = ParticipantCourseProgress::whereBetween('progress_percentage', [1, 25])->count();
        $progressing = ParticipantCourseProgress::whereBetween('progress_percentage', [26, 50])->count();
        $advancing = ParticipantCourseProgress::whereBetween('progress_percentage', [51, 75])->count();
        $nearCompletion = ParticipantCourseProgress::whereBetween('progress_percentage', [76, 99])->count();
        $completed = ParticipantCourseProgress::where('progress_percentage', 100)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Students',
                    'data' => [$notStarted, $beginning, $progressing, $advancing, $nearCompletion, $completed],
                    'backgroundColor' => [
                        '#ef4444', // red - not started
                        '#f97316', // orange - beginning
                        '#eab308', // yellow - progressing
                        '#3b82f6', // blue - advancing
                        '#8b5cf6', // purple - near completion
                        '#10b981', // green - completed
                    ],
                ],
            ],
            'labels' => [
                'Not Started (0%)',
                'Beginning (1-25%)',
                'Progressing (26-50%)',
                'Advancing (51-75%)',
                'Near Completion (76-99%)',
                'Completed (100%)',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
