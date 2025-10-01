<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParticipantCourseProgress>
 */
class ParticipantCourseProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'participant_id' => \App\Models\Participant::factory(),
            'course_batch_id' => \App\Models\CourseBatch::factory(),
            'enrollment_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'progress_percentage' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement(['enrolled', 'active', 'completed', 'dropped']),
            'total_tests' => $this->faker->numberBetween(5, 15),
            'tests_taken' => $this->faker->numberBetween(0, 10),
            'tests_passed' => $this->faker->numberBetween(0, 8),
            'total_exams' => $this->faker->numberBetween(2, 5),
            'exams_taken' => $this->faker->numberBetween(0, 3),
            'exams_needed' => $this->faker->numberBetween(1, 3),
        ];
    }
}
