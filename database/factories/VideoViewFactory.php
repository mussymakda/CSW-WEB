<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VideoView>
 */
class VideoViewFactory extends Factory
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
            'workout_video_id' => \App\Models\WorkoutVideo::factory(),
            'viewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'duration_watched_seconds' => $this->faker->numberBetween(30, 600),
        ];
    }
}
