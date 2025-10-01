<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeliverPendingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:deliver {--dry-run : Show what would be delivered without actually delivering}';

    /**
     * The console command description.
     */
    protected $description = 'Deliver pending notifications that are ready based on delivery_time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $isDryRun = $this->option('dry-run');

        $query = UserNotification::whereNotNull('delivery_time')
            ->where('delivery_time', '<=', $now)
            ->where('is_read', false);

        $pendingNotifications = $query->get();

        if ($pendingNotifications->isEmpty()) {
            $this->info('No notifications ready for delivery at this time.');

            return Command::SUCCESS;
        }

        $this->info("Found {$pendingNotifications->count()} notifications ready for delivery.");

        if ($isDryRun) {
            $this->table(
                ['ID', 'Participant', 'Type', 'Delivery Time', 'Is Read'],
                $pendingNotifications->map(fn ($n) => [
                    $n->id,
                    $n->participant->name ?? 'Unknown',
                    $n->notification_type,
                    $n->delivery_time->format('Y-m-d H:i:s'),
                    $n->is_read ? 'Yes' : 'No',
                ])
            );
            $this->info('Dry run complete - no notifications were actually delivered.');

            return Command::SUCCESS;
        }

        $delivered = 0;
        $failed = 0;

        foreach ($pendingNotifications as $notification) {
            try {
                // Mark as read (delivered)
                $notification->update([
                    'is_read' => true,
                ]);

                $delivered++;
                $this->line("✓ Delivered notification {$notification->id} to {$notification->participant->name}");

                // Log successful delivery
                Log::info('Notification delivered', [
                    'notification_id' => $notification->id,
                    'participant_id' => $notification->participant_id,
                    'type' => $notification->notification_type,
                    'delivery_time' => $notification->delivery_time,
                    'delivered_at' => $now,
                ]);

            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to deliver notification {$notification->id}: {$e->getMessage()}");

                Log::error('Failed to deliver notification', [
                    'notification_id' => $notification->id,
                    'participant_id' => $notification->participant_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Delivery complete: {$delivered} delivered, {$failed} failed.");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
