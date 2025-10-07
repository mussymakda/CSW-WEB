<?php

namespace App\Jobs;

use App\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UserNotification $notification
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Process the notification
            Log::info('Processing notification', [
                'notification_id' => $this->notification->id,
                'title' => $this->notification->title,
            ]);

            // Mark notification as delivered
            $this->notification->update([
                'delivered_at' => now(),
                'status' => 'delivered',
            ]);

            Log::info('Notification processed successfully', [
                'notification_id' => $this->notification->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job failed permanently', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark notification as failed
        $this->notification->update([
            'status' => 'failed',
            'failed_at' => now(),
        ]);
    }
}
