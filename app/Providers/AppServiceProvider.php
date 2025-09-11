<?php

namespace App\Providers;

use App\Services\OllamaService;
use App\Services\AINotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Ollama Service as singleton
        $this->app->singleton(OllamaService::class, function ($app) {
            return new OllamaService();
        });

        // Register AI Notification Service
        $this->app->singleton(AINotificationService::class, function ($app) {
            return new AINotificationService($app->make(OllamaService::class));
        });

        // Register Smart Notification Service
        $this->app->singleton(\App\Services\SmartNotificationService::class, function ($app) {
            return new \App\Services\SmartNotificationService();
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
