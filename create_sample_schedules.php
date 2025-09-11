<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

echo "🔧 Creating sample schedule data for AI notification testing...\n";

$participant = \App\Models\Participant::first();

if (!$participant) {
    echo "❌ No participants found. Please create participants first.\n";
    exit(1);
}

$today = strtolower(now()->format('l'));
echo "📅 Creating schedules for {$participant->name} on {$today}...\n";

// Clear existing schedules for today
\App\Models\DailySchedule::where('participant_id', $participant->id)
    ->where('day', $today)
    ->delete();

// Create realistic schedule for today
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
    \App\Models\DailySchedule::create([
        'participant_id' => $participant->id,
        'task' => $schedule['task'],
        'time' => $schedule['time'],
        'day' => $today,
        'category' => $schedule['category'],
        'location' => $schedule['location'],
        'priority' => $schedule['priority'],
        'is_completed' => false
    ]);
    
    echo "✅ Created: {$schedule['task']} at {$schedule['time']}\n";
}

echo "\n🎉 Sample schedules created for {$participant->name}!\n";
echo "💡 Now test with: php artisan notifications:generate-ai --participant={$participant->id} --type=schedule_optimization\n";
