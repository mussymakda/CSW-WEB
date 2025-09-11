<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\GenerateAINotificationsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule AI notification generation
Schedule::job(new GenerateAINotificationsJob())
    ->hourly()
    ->withoutOverlapping()
    ->when(function () {
        return config('ollama.notifications.enabled', false);
    });

// Alternative: Schedule the artisan command directly
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
