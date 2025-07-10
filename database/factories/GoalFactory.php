<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    protected $model = Goal::class;    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Weight Loss', 
                'Muscle Gain', 
                'Endurance Training', 
                'Flexibility & Mobility', 
                'General Fitness',
                'Strength Building',
                'Cardio Improvement',
                'Body Toning'
            ]),
            'display_image' => 'goals/' . $this->faker->randomElement([
                'weight-loss.jpg',
                'muscle-gain.jpg', 
                'endurance.jpg',
                'flexibility.jpg',
                'fitness.jpg'
            ]),
        ];
    }
}
