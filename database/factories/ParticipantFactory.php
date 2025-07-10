<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'dob' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'profile_picture' => 'profiles/' . $this->faker->randomElement([
                'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg'
            ]),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'weight' => $this->faker->randomFloat(1, 50, 120),
            'height' => $this->faker->randomFloat(2, 1.5, 2.1),
            'aceds_no' => 'ACEDS' . $this->faker->unique()->numberBetween(1000, 9999),
            'goal_id' => null, // Will be set in seeder
        ];
    }
}
