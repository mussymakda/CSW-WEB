<?php

namespace App\Console\Commands;

use App\Models\DailySchedule;
use App\Models\Participant;
use Illuminate\Console\Command;

class CreateSampleSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:create-sample {participant? : Participant ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample schedules for testing AI notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $participantId = $this->argument('participant');

        if ($participantId) {
            $participant = Participant::find($participantId);
            if (! $participant) {
                $this->error("Participant with ID {$participantId} not found.");

                return 1;
            }
        } else {
            $participant = Participant::first();
            if (! $participant) {
                $this->error('No participants found. Please create participants first.');

                return 1;
            }
        }

        $today = strtolower(now()->format('l'));
        $this->info("Creating sample schedules for {$participant->name} on {$today}...");

        // Clear existing schedules for today
        DailySchedule::where('participant_id', $participant->id)
            ->where('day', $today)
            ->delete();

        // Create realistic schedule scenarios
        $schedules = [
            [
                'task' => 'School pickup',
                'time' => '15:30',
                'category' => 'family',
                'location' => 'Elementary School',
                'priority' => 1,
            ],
            [
                'task' => 'Grocery shopping',
                'time' => '16:00',
                'category' => 'errands',
                'location' => 'Supermarket near school',
                'priority' => 2,
            ],
            [
                'task' => 'Pharmacy pickup',
                'time' => '16:15',
                'category' => 'errands',
                'location' => 'Pharmacy in same plaza',
                'priority' => 2,
            ],
            [
                'task' => 'Gym workout',
                'time' => '18:00',
                'category' => 'health',
                'location' => 'Local Gym',
                'priority' => 2,
            ],
            [
                'task' => 'Prepare dinner',
                'time' => '19:30',
                'category' => 'family',
                'location' => 'Home',
                'priority' => 1,
            ],
        ];

        foreach ($schedules as $schedule) {
            DailySchedule::create([
                'participant_id' => $participant->id,
                'task' => $schedule['task'],
                'time' => $schedule['time'],
                'day' => $today,
                'category' => $schedule['category'],
                'location' => $schedule['location'],
                'priority' => $schedule['priority'],
                'is_completed' => false,
            ]);

            $this->line("✅ Created: {$schedule['task']} at {$schedule['time']}");
        }

        $this->info("\n🎉 Sample schedules created for {$participant->name}!");
        $this->line('💡 Test AI notifications:');
        $this->line("  php artisan notifications:generate-ai --participant={$participant->id} --type=schedule_optimization");
        $this->line("  php artisan notifications:generate-ai --participant={$participant->id} --type=efficiency_suggestion");

        // Mark one task as overdue for testing
        $overdueTask = DailySchedule::where('participant_id', $participant->id)
            ->where('day', $today)
            ->where('time', '<', now()->format('H:i'))
            ->first();

        if ($overdueTask) {
            $this->line("  php artisan notifications:generate-ai --participant={$participant->id} --type=overdue_reminder");
        }

        return 0;
    }
}
