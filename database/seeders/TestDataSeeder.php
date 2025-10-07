<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\GuidanceTip;
use App\Models\Participant;
use App\Models\Slider;
use App\Models\User;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (if doesn't exist)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@fitandfocusedacademics.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Create test goals
        $goals = [
            ['name' => 'Weight Loss'],
            ['name' => 'Muscle Gain'],
            ['name' => 'Cardio Fitness'],
            ['name' => 'Flexibility'],
        ];

        foreach ($goals as $goal) {
            Goal::create($goal);
        }

        // Create test participants
        for ($i = 1; $i <= 10; $i++) {
            Participant::create([
                'name' => "Test Participant {$i}",
                'email' => "participant{$i}@example.com",
                'phone' => "+123456789{$i}",
                'weight_kg' => rand(50, 120),
                'height_cm' => rand(150, 200),
                'onboarding_completed' => $i <= 8, // 8 completed, 2 pending
                'terms_accepted' => true,
                'password' => Hash::make('password123'),
            ]);
        }

        // Create test sliders
        $sliders = [
            [
                'title' => 'Welcome to Fit Academy',
                'description' => 'Start your fitness journey today',
                'image_url' => 'https://via.placeholder.com/800x400/4F46E5/FFFFFF?text=Slider+1',
                'link_url' => '/workouts',
                'link_text' => 'Start Workout',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'sort_order' => 1,
            ],
            [
                'title' => 'Personalized Training',
                'description' => 'Get customized workout plans',
                'image_url' => 'https://via.placeholder.com/800x400/059669/FFFFFF?text=Slider+2',
                'link_url' => '/plans',
                'link_text' => 'View Plans',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'sort_order' => 2,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::create($slider);
        }

        // Create workout subcategories
        $subcategories = [
            ['title' => 'Upper Body', 'info' => 'Exercises for arms, chest, and back'],
            ['title' => 'Lower Body', 'info' => 'Exercises for legs and glutes'],
            ['title' => 'Core', 'info' => 'Abdominal and core strengthening'],
            ['title' => 'Cardio', 'info' => 'Cardiovascular exercises'],
        ];

        foreach ($subcategories as $subcategory) {
            $subcat = WorkoutSubcategory::create($subcategory);

            // Add some videos for each subcategory
            for ($i = 1; $i <= 3; $i++) {
                WorkoutVideo::create([
                    'workout_subcategory_id' => $subcat->id,
                    'title' => $subcategory['title']." Workout {$i}",
                    'video_url' => "https://example.com/video/{$subcat->id}/{$i}",
                    'duration_minutes' => rand(10, 60), // 10-60 minutes
                ]);
            }
        }

        // Create guidance tips
        $tips = [
            [
                'name' => 'Stay Hydrated',
                'link' => '/tips/hydration',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Warm Up Properly',
                'link' => '/tips/warmup',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Get Enough Sleep',
                'link' => '/tips/sleep',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($tips as $tip) {
            GuidanceTip::create($tip);
        }

        $this->command->info('Test data seeded successfully!');
        $this->command->info('Admin user: admin@fitandfocusedacademics.com / password123');
        $this->command->info('Created: 4 goals, 10 participants, 2 sliders, 4 workout categories, 12 videos, 3 tips');
    }
}
