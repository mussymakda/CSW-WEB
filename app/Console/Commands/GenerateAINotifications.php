<?php

namespace App\Console\Commands;

use App\Services\AINotificationService;
use App\Services\OllamaService;
use Illuminate\Console\Command;

class GenerateAINotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:generate-ai {--test : Test mode - show what would be generated} {--participant= : Generate for specific participant ID} {--type= : Generate specific notification type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-powered notifications for participants using Ollama';

    protected AINotificationService $aiNotificationService;

    protected ?OllamaService $ollamaService;

    public function __construct(AINotificationService $aiNotificationService, ?OllamaService $ollamaService = null)
    {
        parent::__construct();
        $this->aiNotificationService = $aiNotificationService;
        $this->ollamaService = $ollamaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Starting AI Notification Generation');
        $this->line('=====================================');

        // Check Ollama availability
        if (! $this->ollamaService || ! $this->ollamaService->isAvailable()) {
            $this->error('❌ Ollama service is not available');
            $this->line('Please ensure Ollama is running and accessible at: '.config('ollama.host'));

            return 1;
        }

        $this->info('✅ Ollama service is available');

        // Test mode
        if ($this->option('test')) {
            return $this->runTest();
        }

        // Generate for specific participant
        if ($participantId = $this->option('participant')) {
            return $this->generateForParticipant($participantId);
        }

        // Generate notifications
        $this->info('🔄 Generating AI notifications...');

        $results = $this->aiNotificationService->generateNotifications();

        $this->displayResults($results);

        return 0;
    }

    protected function runTest(): int
    {
        $this->info('🧪 Running test mode...');

        // Test Ollama connection
        $testResults = $this->ollamaService ? $this->ollamaService->test() : [];

        $this->line('Connection Status:');
        $this->line('- Connection: '.($testResults['connection'] ? '✅ Connected' : '❌ Failed'));
        $this->line('- Model Available: '.($testResults['model_available'] ? '✅ Yes' : '❌ No'));
        $this->line('- Test Generation: '.($testResults['test_generation'] ? '✅ Success' : '❌ Failed'));

        if (! empty($testResults['models'])) {
            $this->line('- Available Models: '.implode(', ', $testResults['models']));
        }

        if ($testResults['error']) {
            $this->error('Error: '.$testResults['error']);
        }

        // Show statistics
        $stats = $this->aiNotificationService->getStatistics();
        $this->displayStatistics($stats);

        return 0;
    }

    protected function generateForParticipant(int $participantId): int
    {
        $participant = \App\Models\Participant::find($participantId);

        if (! $participant) {
            $this->error("❌ Participant with ID {$participantId} not found");

            return 1;
        }

        $this->info("🎯 Generating notification for: {$participant->name}");

        $type = $this->option('type');

        if ($type) {
            $notification = $this->aiNotificationService->generateSpecificNotification($participant, $type);
        } else {
            $notification = $this->aiNotificationService->generateParticipantNotification($participant);
        }

        if ($notification) {
            $this->info('✅ Notification generated successfully:');
            $this->line("Icon: {$notification->icon}");
            $this->line("Text: {$notification->notification_text}");
        } else {
            $this->error('❌ Failed to generate notification');

            return 1;
        }

        return 0;
    }

    protected function displayResults(array $results): void
    {
        $this->line('');
        $this->info('📊 Generation Results:');
        $this->line("✅ Generated: {$results['generated']} notifications");

        if (! empty($results['errors'])) {
            $this->line('❌ Errors: '.count($results['errors']));
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        // Show statistics
        $stats = $this->aiNotificationService->getStatistics();
        $this->displayStatistics($stats);
    }

    protected function displayStatistics(array $stats): void
    {
        $this->line('');
        $this->info('📈 Today\'s Statistics:');
        $this->line("- Total AI Notifications: {$stats['total_ai_notifications']}");
        $this->line("- Participants Notified: {$stats['participants_notified_today']}");

        if (! empty($stats['notification_types'])) {
            $this->line('- Notification Types:');
            foreach ($stats['notification_types'] as $icon => $count) {
                $this->line("  {$icon}: {$count}");
            }
        }

        $this->line('- Ollama Status: '.($stats['ollama_status']['available'] ? '✅ Available' : '❌ Unavailable'));
        $this->line('- AI Notifications: '.($stats['ollama_status']['enabled'] ? '✅ Enabled' : '❌ Disabled'));
    }
}
