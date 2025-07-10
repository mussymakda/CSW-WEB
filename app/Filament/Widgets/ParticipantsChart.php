<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\Goal;
use Filament\Widgets\ChartWidget;

class ParticipantsChart extends ChartWidget
{
    protected static ?string $heading = 'Participants by Goal';

    protected function getData(): array
    {
        $goals = Goal::withCount('participants')->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Participants',
                    'data' => $goals->pluck('participants_count')->toArray(),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB', 
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                    ],
                ],
            ],
            'labels' => $goals->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
