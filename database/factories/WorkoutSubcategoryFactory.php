<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutSubcategory>
 */
class WorkoutSubcategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement([
                'Upper Body Strength',
                'Lower Body Power', 
                'Core Conditioning',
                'Cardio Blast',
                'Flexibility & Mobility',
                'HIIT Training',
                'Functional Fitness',
                'Endurance Building',
                'Weight Loss Focus',
                'Muscle Building'
            ]),
            'info' => $this->faker->paragraphs(2, true),
            'image' => 'https://picsum.photos/400/300?random=' . $this->faker->numberBetween(1, 1000),
        ];
    }
}
