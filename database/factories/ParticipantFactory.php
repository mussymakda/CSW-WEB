<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition()
    {
        $programs = [
            'Certified Personal Trainer (CPT)',
            'Group Fitness Instructor Certification',
            'Nutrition Coach Certification',
            'Youth Exercise Specialist',
            'Senior Fitness Specialist',
            'Corrective Exercise Specialist',
        ];

        return [
            'student_number' => 'STU'.$this->faker->unique()->numberBetween(10000, 99999),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'location' => $this->faker->city(),
            'client_name' => $this->faker->company(),
            'dob' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'profile_picture' => 'profiles/'.$this->faker->randomElement([
                'avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'avatar4.jpg', 'avatar5.jpg',
            ]),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'weight' => $this->faker->randomFloat(1, 50, 120),
            'height' => $this->faker->randomFloat(2, 1.5, 2.1),
            'aceds_no' => 'ACEDS'.$this->faker->unique()->numberBetween(1000, 9999),
            'program_description' => $this->faker->randomElement($programs),
            'status' => $this->faker->randomElement(['active', 'enrolled', 'completed', 'paused']),
            'graduation_date' => $this->faker->optional(0.3)->dateTimeBetween('-2 years', '+6 months'),
            'goal_id' => null, // Will be set in seeder
        ];
    }
}
