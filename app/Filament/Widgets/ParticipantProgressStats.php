<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\ParticipantCourseProgress;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParticipantProgressStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get total participants
        $totalParticipants = Participant::count();

        // Get participants with high completion rates (80%+)
        $highPerformers = ParticipantCourseProgress::where('progress_percentage', '>=', 80)->count();

        // Get participants needing attention (below 50% completion)
        $needsAttention = ParticipantCourseProgress::where('progress_percentage', '<', 50)->count();

        // Get participants who haven't started (0% completion)
        $notStarted = ParticipantCourseProgress::where('progress_percentage', 0)->count();

        // Get recent activity (enrollments in last 7 days)
        $recentActivity = ParticipantCourseProgress::where('enrollment_date', '>=', now()->subDays(7))->count();

        // Get completion rate
        $totalWithProgress = ParticipantCourseProgress::count();
        $completed = ParticipantCourseProgress::where('progress_percentage', 100)->count();
        $completionRate = $totalWithProgress > 0 ? round(($completed / $totalWithProgress) * 100, 1) : 0;

        return [
            Stat::make('Total Students', number_format($totalParticipants))
                ->description('Registered students in system')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('High Performers', number_format($highPerformers))
                ->description('Students with 80%+ completion')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('Need Attention', number_format($needsAttention))
                ->description('Students below 50% completion')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Not Started', number_format($notStarted))
                ->description('Students with 0% progress')
                ->descriptionIcon('heroicon-m-pause')
                ->color('danger'),

            Stat::make('Recent Activity', number_format($recentActivity))
                ->description('New enrollments this week')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Completion Rate', $completionRate.'%')
                ->description('Overall program completion rate')
                ->descriptionIcon('heroicon-m-trophy')
                ->color($completionRate >= 70 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger')),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
