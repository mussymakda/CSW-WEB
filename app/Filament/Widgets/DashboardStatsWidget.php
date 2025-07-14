<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\ParticipantCourseProgress;
use App\Models\Slider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Get students by status
        $activeStudents = Participant::whereHas('courseProgress', function($query) {
            $query->whereBetween('progress_percentage', [1, 99]);
        })->count();
        
        $graduatedStudents = ParticipantCourseProgress::where('progress_percentage', 100)->count();
        
        $strugglingStudents = ParticipantCourseProgress::where('progress_percentage', '<', 30)->count();
        
        // Get program insights
        $averageProgress = ParticipantCourseProgress::avg('progress_percentage') ?? 0;
        
        // Get mobile app content
        $activeSliders = Slider::where('is_active', true)->count();
        
        // Get recent activity
        $thisWeekEnrollments = ParticipantCourseProgress::where('enrollment_date', '>=', now()->subWeek())->count();

        return [
            Stat::make('Active Students', number_format($activeStudents))
                ->description('Students currently enrolled')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
            
            Stat::make('Graduates', number_format($graduatedStudents))
                ->description('Students who completed programs')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
            
            Stat::make('At Risk', number_format($strugglingStudents))
                ->description('Students with <30% progress')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('Avg Progress', number_format($averageProgress, 1) . '%')
                ->description('Overall program completion rate')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($averageProgress >= 70 ? 'success' : ($averageProgress >= 50 ? 'warning' : 'danger')),
            
            Stat::make('This Week', number_format($thisWeekEnrollments))
                ->description('New enrollments this week')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('Mobile Content', number_format($activeSliders))
                ->description('Active mobile app sliders')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('gray'),
        ];
    }
}
