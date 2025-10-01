<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\DailySchedule;
use App\Models\Goal;
use App\Models\Participant;
use App\Models\ParticipantCourseProgress;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
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

        // Create goals, subcategories, videos, courses, and participants using factories
        $goals = Goal::factory()->count(5)->create();
        $subcategories = WorkoutSubcategory::factory()->count(8)->create();

        // Create some courses and batches
        $course = Course::create([
            'name' => 'Certified Personal Trainer Program',
            'description' => 'Comprehensive fitness certification program',
            'duration_weeks' => 24,
            'difficulty_level' => 'intermediate',
        ]);

        $batch = CourseBatch::create([
            'course_id' => $course->id,
            'batch_name' => 'CPT Batch 2025-1',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'max_participants' => 20,
            'status' => 'active',
        ]);

        // Attach subcategories to goals (many-to-many)
        $subcategories->each(function ($subcategory) use ($goals) {
            $subcategory->goals()->attach($goals->random(rand(1, 3))->pluck('id'));
        });

        // Create workout videos for each subcategory
        $subcategories->each(function ($subcategory) {
            WorkoutVideo::factory()->count(rand(3, 6))->create([
                'workout_subcategory_id' => $subcategory->id,
            ]);
        });

        // Create participants with random goals using factories
        Participant::factory()
            ->count(5)
            ->create()
            ->each(function ($participant) use ($goals, $batch) {
                $participant->update(['goal_id' => $goals->random()->id]);

                // Create some daily schedules for each participant
                DailySchedule::factory()->count(rand(10, 20))->create([
                    'participant_id' => $participant->id,
                ]);

                // Create some notifications for each participant
                UserNotification::factory()->count(rand(2, 5))->create([
                    'participant_id' => $participant->id,
                ]);

                // Create course progress for each participant
                ParticipantCourseProgress::create([
                    'participant_id' => $participant->id,
                    'course_batch_id' => $batch->id,
                    'enrollment_date' => now()->subMonths(rand(1, 6)),
                    'started_at' => now()->subMonths(rand(0, 5)),
                    'progress_percentage' => rand(10, 100),
                    'exams_taken' => rand(0, 8),
                    'total_exams' => 8,
                    'tests_taken' => rand(0, 15),
                    'tests_passed' => rand(0, 12),
                    'average_score' => rand(60, 95),
                    'status' => fake()->randomElement(['enrolled', 'active', 'completed']),
                ]);
            });
    }
}
