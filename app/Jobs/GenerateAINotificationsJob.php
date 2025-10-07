<?php

namespace App\Jobs;

use App\Services\AINotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAINotificationsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes timeout

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AINotificationService $aiNotificationService): void
    {
        Log::info('🤖 Starting scheduled AI notification generation job');

        try {
            $results = $aiNotificationService->generateNotifications();

            Log::info('AI notification generation completed', [
                'generated' => $results['generated'],
                'errors' => count($results['errors']),
            ]);

            if (! empty($results['errors'])) {
                Log::warning('AI notification generation had errors', [
                    'errors' => $results['errors'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('AI notification generation job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI notification generation job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
