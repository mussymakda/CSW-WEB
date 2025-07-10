<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\Goal;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
use App\Models\UserNotification;
use App\Models\DailySchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Participants', Participant::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            
            Stat::make('Active Goals', Goal::count())
                ->description('Available fitness goals')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
            
            Stat::make('Workout Categories', WorkoutSubcategory::count())
                ->description('Exercise subcategories')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),
            
            Stat::make('Total Videos', WorkoutVideo::count())
                ->description('Workout video library')
                ->descriptionIcon('heroicon-m-play')
                ->color('primary'),
            
            Stat::make('Unread Notifications', UserNotification::where('is_read', false)->count())
                ->description('Pending user notifications')
                ->descriptionIcon('heroicon-m-bell')
                ->color('danger'),
            
            Stat::make('Daily Schedules', DailySchedule::count())
                ->description('Total scheduled activities')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('gray'),
        ];
    }
}
