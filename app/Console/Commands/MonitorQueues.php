<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor 
                            {--refresh=5 : Refresh interval in seconds}
                            {--show-failed : Show failed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status and job processing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $refresh = (int) $this->option('refresh');
        $showFailed = $this->option('show-failed');

        $this->info('📊 Queue Monitor Started');
        $this->info('Press Ctrl+C to exit');
        $this->newLine();

        while (true) {
            $this->displayQueueStats();

            if ($showFailed) {
                $this->displayFailedJobs();
            }

            if ($refresh > 0) {
                sleep($refresh);
                $this->info("\n".str_repeat('=', 60));
                $this->info('Refreshed at: '.now()->format('Y-m-d H:i:s'));
                $this->info(str_repeat('=', 60));
            } else {
                break;
            }
        }

        return Command::SUCCESS;
    }

    private function displayQueueStats(): void
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $processingJobs = DB::table('jobs')->where('reserved_at', '!=', null)->count();

        $this->table([
            'Metric',
            'Count',
        ], [
            ['Pending Jobs', $pendingJobs],
            ['Processing Jobs', $processingJobs],
            ['Failed Jobs', $failedJobs],
        ]);

        if ($pendingJobs > 0) {
            $this->warn("⚠️  {$pendingJobs} jobs are pending. Consider running: php artisan queue:work");
        } else {
            $this->info('✅ No pending jobs');
        }
    }

    private function displayFailedJobs(): void
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get();

        if ($failedJobs->isNotEmpty()) {
            $this->newLine();
            $this->error('❌ Recent Failed Jobs:');

            $rows = $failedJobs->map(function ($job) {
                return [
                    $job->id,
                    $job->queue ?? 'default',
                    substr($job->payload, 0, 50).'...',
                    $job->failed_at,
                ];
            });

            $this->table([
                'ID',
                'Queue',
                'Payload',
                'Failed At',
            ], $rows);
        }
    }
}
