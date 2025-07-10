<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Participant;
use App\Models\Goal;
use App\Models\DailySchedule;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user for Filament
        User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // Create 5 goals
        $goals = Goal::factory()->count(5)->create();

        // Create workout subcategories
        $subcategories = WorkoutSubcategory::factory()->count(8)->create([
            'title' => fn() => fake()->randomElement([
                'Upper Body Strength',
                'Lower Body Power',
                'Core Conditioning',
                'Cardio Blast',
                'Flexibility & Mobility',
                'HIIT Training',
                'Functional Fitness',
                'Endurance Building'
            ]),
        ]);

        // Attach subcategories to goals (many-to-many)
        $subcategories->each(function ($subcategory) use ($goals) {
            $subcategory->goals()->attach($goals->random(rand(1, 3))->pluck('id'));
        });

        // Create workout videos for each subcategory
        $subcategories->each(function ($subcategory) {
            WorkoutVideo::factory()->count(rand(3, 6))->create([
                'workout_subcategory_id' => $subcategory->id,
                'title' => fn() => fake()->randomElement([
                    'Beginner Push-ups',
                    'Advanced Squats',
                    'Core Blaster Routine',
                    '10-Minute HIIT',
                    'Flexibility Flow',
                    'Strength Builder',
                    'Cardio Kickstart',
                    'Power Training'
                ]) . ' - ' . fake()->numberBetween(1, 20),
            ]);
        });

        // Create participants with goals and schedules
        Participant::factory(10)->create()->each(function ($participant) use ($goals) {
            $participant->goal_id = $goals->random()->id;
            $participant->save();
            
            // Create daily schedules
            foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
                DailySchedule::factory()->create([
                    'participant_id' => $participant->id,
                    'day' => $day,
                ]);
            }

            // Create notifications
            UserNotification::factory()
                ->count(rand(3, 8))
                ->create(['participant_id' => $participant->id]);
        });
    }
}
