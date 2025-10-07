<?php

namespace App\Providers;

use App\Services\AINotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AI Notification Service
        $this->app->singleton(AINotificationService::class, function ($app) {
            return new AINotificationService(null);
        });

        // Register Smart Notification Service
        $this->app->singleton(\App\Services\SmartNotificationService::class, function ($app) {
            return new \App\Services\SmartNotificationService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
