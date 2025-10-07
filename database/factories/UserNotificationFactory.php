<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $icons = ['🏋️', '💪', '🎯', '⏰', '🔥', '💧', '🏃', '🧘', '📱', '⚡'];
        $notifications = [
            'Time for your morning workout!',
            'Don\'t forget to drink water',
            'Complete your daily cardio session',
            'Take a 5-minute break and stretch',
            'Log your workout progress',
            'Time for your protein shake',
            'Complete your strength training',
            'Record your daily weight',
            'Take progress photos',
            'Schedule your next workout session',
        ];

        return [
            'icon' => $this->faker->randomElement($icons),
            'notification_text' => $this->faker->randomElement($notifications),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
        ];
    }
}
