<?php

namespace Database\Factories;

use App\Models\DailySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyScheduleFactory extends Factory
{
    protected $model = DailySchedule::class;

    public function definition()
    {
        return [
            'participant_id' => null, // Will be set in seeder
            'task' => $this->faker->randomElement([
                'Morning Workout', 'Breakfast', 'Gym Session', 'Cardio Training',
                'Yoga Class', 'Lunch Break', 'Strength Training', 'Evening Walk',
                'Dinner', 'Meditation', 'Sleep Schedule', 'Protein Shake',
                'Stretching', 'Swimming', 'Cycling',
            ]),
            'time' => $this->faker->time('H:i'),
            'day' => $this->faker->randomElement([
                'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            ]),
        ];
    }
}
