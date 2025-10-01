<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutVideo>
 */
class WorkoutVideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $videoTitles = [
            'Beginner Push-ups Tutorial',
            'Advanced Squats Technique',
            'Core Blaster 10-Min Routine',
            'HIIT Cardio Workout',
            'Full Body Stretch Session',
            'Strength Training Basics',
            'Fat Burning Cardio',
            'Power Training Circuit',
            'Flexibility Flow Yoga',
            'Muscle Building Routine',
        ];

        return [
            'title' => $this->faker->randomElement($videoTitles).' - Day '.$this->faker->numberBetween(1, 30),
            'image' => null,
            'duration_minutes' => $this->faker->numberBetween(5, 60),
            'video_url' => null,
        ];
    }
}
