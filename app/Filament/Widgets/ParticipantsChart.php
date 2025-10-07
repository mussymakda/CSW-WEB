<?php

namespace App\Filament\Widgets;

use App\Models\ParticipantCourseProgress;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ParticipantsChart extends ChartWidget
{
    protected static ?string $heading = 'Student Enrollment Trends (Last 6 Months)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $enrollments = [];
        $labels = [];

        // Get enrollment data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = ParticipantCourseProgress::whereYear('enrollment_date', $month->year)
                ->whereMonth('enrollment_date', $month->month)
                ->count();

            $enrollments[] = $count;
            $labels[] = $month->year.'-'.str_pad($month->month, 2, '0', STR_PAD_LEFT);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Enrollments',
                    'data' => $enrollments,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
