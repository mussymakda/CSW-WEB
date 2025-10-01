<?php

use App\Jobs\GenerateAINotificationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule AI notification generation
Schedule::job(new GenerateAINotificationsJob)
    ->hourly()
    ->withoutOverlapping()
    ->when(function () {
        return config('ollama.notifications.enabled', true);
    });

// Schedule notification delivery every 5 minutes
Schedule::command('notifications:deliver')
    ->everyFiveMinutes()
    ->withoutOverlapping();
Schedule::command('notifications:generate-ai')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->when(function () {
        return config('ollama.notifications.enabled', false);
    });

Schedule::command('notifications:generate-ai')
    ->dailyAt('18:00')
    ->withoutOverlapping()
    ->when(function () {
        return config('ollama.notifications.enabled', false);
    });
