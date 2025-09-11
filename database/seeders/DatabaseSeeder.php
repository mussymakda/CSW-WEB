<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Participant;
use App\Models\Goal;
use App\Models\DailySchedule;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
use App\Models\UserNotification;
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

        // Create 5 goals
        $goals = Goal::factory()->count(5)->create();

        // Create workout subcategories
        $subcategories = WorkoutSubcategory::factory()->count(8)->create([
            'title' => fn() => fake()->randomElement([
                'Upper Body Strength',
                'Lower Body Power',
                'Core Conditioning',
                'Cardio Blast',
                'Flexibility & Mobility',
                'HIIT Training',
                'Functional Fitness',
                'Endurance Building'
            ]),
        ]);

        // Attach subcategories to goals (many-to-many)
        $subcategories->each(function ($subcategory) use ($goals) {
            $subcategory->goals()->attach($goals->random(rand(1, 3))->pluck('id'));
        });

        // Create workout videos for each subcategory
        $subcategories->each(function ($subcategory) {
            WorkoutVideo::factory()->count(rand(3, 6))->create([
                'workout_subcategory_id' => $subcategory->id,
                'title' => fn() => fake()->randomElement([
                    'Beginner Push-ups',
                    'Advanced Squats',
                    'Core Blaster Routine',
                    '10-Minute HIIT',
                    'Flexibility Flow',
                    'Strength Builder',
                    'Cardio Kickstart',
                    'Power Training'
                ]) . ' - ' . fake()->numberBetween(1, 20),
            ]);
        });

        // Create participants with goals and schedules
        $this->createRealisticParticipants($goals);
    }

    /**
     * Create realistic participants with full day schedules
     */
    private function createRealisticParticipants($goals)
    {
        // Clear existing data in correct order (respecting foreign keys)
        UserNotification::query()->delete();
        DailySchedule::query()->delete();
        Participant::query()->delete();

        $participants = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'dob' => '1995-03-15', // 30 years old
                'gender' => 'female',
                'weight' => 65.5,
                'height' => 1.68,
                'location' => 'Downtown Office District',
                'goal' => 'Weight Loss',
                'profile_type' => 'working_professional'
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@example.com', 
                'dob' => '1988-07-22', // 37 years old
                'gender' => 'male',
                'weight' => 82.3,
                'height' => 1.78,
                'location' => 'Suburban Area',
                'goal' => 'Muscle Gain',
                'profile_type' => 'family_man'
            ],
            [
                'name' => 'Emma Rodriguez',
                'email' => 'emma.rodriguez@example.com',
                'dob' => '2001-11-08', // 23 years old
                'gender' => 'female', 
                'weight' => 58.2,
                'height' => 1.65,
                'location' => 'University Campus',
                'goal' => 'General Fitness',
                'profile_type' => 'student'
            ],
            [
                'name' => 'Robert Williams',
                'email' => 'robert.williams@example.com',
                'dob' => '1978-04-12', // 47 years old
                'gender' => 'male',
                'weight' => 89.7,
                'height' => 1.83,
                'location' => 'Executive District',
                'goal' => 'Weight Loss',
                'profile_type' => 'executive'
            ],
            [
                'name' => 'Zoe Thompson',
                'email' => 'zoe.thompson@example.com',
                'dob' => '1962-09-30', // 62 years old
                'gender' => 'female',
                'weight' => 72.1,
                'height' => 1.62,
                'location' => 'Retirement Community',
                'goal' => 'Health Maintenance',
                'profile_type' => 'retiree'
            ]
        ];

        foreach ($participants as $participantData) {
            // Find the goal
            $goal = $goals->firstWhere('name', $participantData['goal']) ?? $goals->first();
            
            // Create participant
            $participant = Participant::create([
                'name' => $participantData['name'],
                'email' => $participantData['email'],
                'password' => bcrypt('password'),
                'phone' => fake()->phoneNumber(),
                'dob' => $participantData['dob'],
                'profile_picture' => 'profiles/avatar' . rand(1, 5) . '.jpg',
                'gender' => $participantData['gender'],
                'weight' => $participantData['weight'],
                'height' => $participantData['height'],
                'location' => $participantData['location'],
                'aceds_no' => 'ACEDS' . fake()->unique()->numberBetween(1000, 9999),
                'goal_id' => $goal->id,
            ]);

            // Create realistic daily schedules based on profile type
            $this->createScheduleForParticipant($participant, $participantData['profile_type']);

            // Create some initial notifications
            UserNotification::factory()
                ->count(rand(2, 5))
                ->create(['participant_id' => $participant->id]);
        }
    }

    /**
     * Create realistic daily schedules based on participant profile
     */
    private function createScheduleForParticipant($participant, $profileType)
    {
        $schedules = $this->getScheduleByProfile($profileType);
        
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $daySchedule = $schedules[$day] ?? $schedules['weekday'];
            
            foreach ($daySchedule as $task) {
                DailySchedule::create([
                    'participant_id' => $participant->id,
                    'task' => $task['task'],
                    'time' => $task['time'],
                    'day' => $day,
                    'priority' => $task['priority'] ?? 3,
                    'category' => $task['category'] ?? 'general',
                    'location' => $task['location'] ?? $participant->location,
                    'is_completed' => rand(0, 100) < 70, // 70% completion rate
                    'completed_at' => rand(0, 100) < 70 ? now()->subHours(rand(1, 48)) : null,
                ]);
            }
        }
    }

    /**
     * Get schedule templates by profile type
     */
    private function getScheduleByProfile($profileType)
    {
        switch ($profileType) {
            case 'working_professional':
                return [
                    'weekday' => [
                        ['task' => 'Morning Alarm', 'time' => '06:30', 'category' => 'routine', 'priority' => 1],
                        ['task' => 'Morning Workout', 'time' => '07:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Breakfast & Coffee', 'time' => '08:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Commute to Work', 'time' => '08:45', 'category' => 'travel', 'priority' => 1],
                        ['task' => 'Morning Meetings', 'time' => '09:30', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Mid-morning Snack', 'time' => '10:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Lunch Break', 'time' => '12:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Afternoon Work Block', 'time' => '14:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Protein Shake', 'time' => '15:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'End Work Day', 'time' => '17:30', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Commute Home', 'time' => '18:00', 'category' => 'travel', 'priority' => 1],
                        ['task' => 'Dinner Prep', 'time' => '19:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Evening Relaxation', 'time' => '20:30', 'category' => 'wellness', 'priority' => 3],
                        ['task' => 'Bedtime Routine', 'time' => '22:30', 'category' => 'routine', 'priority' => 2],
                    ],
                    'saturday' => [
                        ['task' => 'Sleep In', 'time' => '08:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Long Workout Session', 'time' => '09:30', 'category' => 'fitness', 'priority' => 1],
                        ['task' => 'Healthy Brunch', 'time' => '11:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Grocery Shopping', 'time' => '13:00', 'category' => 'errands', 'priority' => 2],
                        ['task' => 'Meal Prep Sunday', 'time' => '15:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Social Activity', 'time' => '17:00', 'category' => 'social', 'priority' => 3],
                        ['task' => 'Dinner Out', 'time' => '19:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Weekend Wind Down', 'time' => '21:00', 'category' => 'wellness', 'priority' => 3],
                    ],
                    'sunday' => [
                        ['task' => 'Leisurely Wake Up', 'time' => '08:30', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Yoga Session', 'time' => '09:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Weekend Breakfast', 'time' => '10:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Planning Next Week', 'time' => '12:00', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Light Lunch', 'time' => '13:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Outdoor Activity', 'time' => '15:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Prep for Monday', 'time' => '17:00', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Sunday Dinner', 'time' => '18:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Early Bedtime', 'time' => '22:00', 'category' => 'routine', 'priority' => 2],
                    ],
                ];

            case 'family_man':
                return [
                    'weekday' => [
                        ['task' => 'Early Morning Rise', 'time' => '06:00', 'category' => 'routine', 'priority' => 1],
                        ['task' => 'Quick Home Workout', 'time' => '06:15', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Family Breakfast', 'time' => '07:30', 'category' => 'nutrition', 'priority' => 1],
                        ['task' => 'Kids School Drop-off', 'time' => '08:15', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Work Start', 'time' => '09:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Coffee Break', 'time' => '10:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Lunch Meeting', 'time' => '12:00', 'category' => 'work', 'priority' => 2],
                        ['task' => 'Afternoon Focus Time', 'time' => '14:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'School Pickup', 'time' => '16:30', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Kids Activities', 'time' => '17:00', 'category' => 'family', 'priority' => 2],
                        ['task' => 'Family Dinner', 'time' => '18:30', 'category' => 'nutrition', 'priority' => 1],
                        ['task' => 'Help with Homework', 'time' => '19:30', 'category' => 'family', 'priority' => 2],
                        ['task' => 'Kids Bedtime', 'time' => '21:00', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Evening Stretching', 'time' => '21:30', 'category' => 'fitness', 'priority' => 3],
                        ['task' => 'Quality Time with Spouse', 'time' => '22:00', 'category' => 'family', 'priority' => 2],
                    ],
                    'saturday' => [
                        ['task' => 'Family Sleep In', 'time' => '07:30', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Family Breakfast', 'time' => '08:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Kids Sports/Activities', 'time' => '10:00', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Family Lunch Out', 'time' => '12:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Gym Session (while kids play)', 'time' => '14:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Family Time at Park', 'time' => '16:00', 'category' => 'family', 'priority' => 2],
                        ['task' => 'Saturday Night Dinner', 'time' => '18:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Family Movie Night', 'time' => '19:30', 'category' => 'family', 'priority' => 3],
                    ],
                    'sunday' => [
                        ['task' => 'Lazy Sunday Morning', 'time' => '08:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Family Brunch', 'time' => '09:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Outdoor Family Activity', 'time' => '11:00', 'category' => 'family', 'priority' => 2],
                        ['task' => 'Sunday Meal Prep', 'time' => '14:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Personal Workout', 'time' => '15:30', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Family Dinner Prep', 'time' => '17:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Week Planning', 'time' => '20:00', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Early Family Bedtime', 'time' => '21:30', 'category' => 'routine', 'priority' => 2],
                    ],
                ];

            case 'student':
                return [
                    'weekday' => [
                        ['task' => 'Wake Up for Class', 'time' => '07:30', 'category' => 'routine', 'priority' => 2],
                        ['task' => 'Quick Breakfast', 'time' => '08:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Morning Lectures', 'time' => '09:00', 'category' => 'education', 'priority' => 1],
                        ['task' => 'Campus Coffee Break', 'time' => '11:00', 'category' => 'social', 'priority' => 3],
                        ['task' => 'Lunch with Friends', 'time' => '12:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Afternoon Classes', 'time' => '14:00', 'category' => 'education', 'priority' => 1],
                        ['task' => 'Campus Gym Session', 'time' => '16:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Study Time', 'time' => '18:00', 'category' => 'education', 'priority' => 1],
                        ['task' => 'Dinner at Cafeteria', 'time' => '19:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Social Time/Club Activities', 'time' => '20:00', 'category' => 'social', 'priority' => 2],
                        ['task' => 'Late Night Study', 'time' => '22:00', 'category' => 'education', 'priority' => 2],
                        ['task' => 'Wind Down', 'time' => '23:30', 'category' => 'routine', 'priority' => 3],
                    ],
                    'saturday' => [
                        ['task' => 'Sleep In', 'time' => '10:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Brunch', 'time' => '11:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Weekend Workout', 'time' => '13:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Study Group', 'time' => '15:00', 'category' => 'education', 'priority' => 2],
                        ['task' => 'Social Activities', 'time' => '17:00', 'category' => 'social', 'priority' => 1],
                        ['task' => 'Dinner Out', 'time' => '19:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Late Night Social', 'time' => '22:00', 'category' => 'social', 'priority' => 3],
                    ],
                    'sunday' => [
                        ['task' => 'Recovery Sleep', 'time' => '11:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Light Brunch', 'time' => '12:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Assignment Work', 'time' => '14:00', 'category' => 'education', 'priority' => 1],
                        ['task' => 'Campus Walk/Light Exercise', 'time' => '16:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Meal Prep for Week', 'time' => '17:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Video Call Home', 'time' => '19:00', 'category' => 'family', 'priority' => 2],
                        ['task' => 'Prep for Monday', 'time' => '20:30', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Early Bedtime', 'time' => '23:00', 'category' => 'routine', 'priority' => 2],
                    ],
                ];

            case 'executive':
                return [
                    'weekday' => [
                        ['task' => 'Executive Morning Routine', 'time' => '05:30', 'category' => 'routine', 'priority' => 1],
                        ['task' => 'Personal Training Session', 'time' => '06:00', 'category' => 'fitness', 'priority' => 1],
                        ['task' => 'Executive Breakfast', 'time' => '07:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Morning Email Review', 'time' => '08:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Strategic Meetings', 'time' => '09:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Board Room Presentation', 'time' => '11:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Business Lunch', 'time' => '12:30', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Afternoon Strategy Session', 'time' => '14:30', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Executive Decision Time', 'time' => '16:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Client Calls', 'time' => '17:30', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Executive Dinner', 'time' => '19:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Evening Review', 'time' => '21:00', 'category' => 'work', 'priority' => 2],
                        ['task' => 'Wind Down', 'time' => '22:30', 'category' => 'wellness', 'priority' => 2],
                    ],
                    'saturday' => [
                        ['task' => 'Executive Sleep Recovery', 'time' => '07:00', 'category' => 'routine', 'priority' => 2],
                        ['task' => 'Premium Gym Session', 'time' => '08:30', 'category' => 'fitness', 'priority' => 1],
                        ['task' => 'Business Brunch', 'time' => '10:30', 'category' => 'work', 'priority' => 2],
                        ['task' => 'Golf Meeting', 'time' => '13:00', 'category' => 'work', 'priority' => 2],
                        ['task' => 'Executive Massage', 'time' => '16:00', 'category' => 'wellness', 'priority' => 3],
                        ['task' => 'Fine Dining', 'time' => '19:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Executive Social Event', 'time' => '21:00', 'category' => 'social', 'priority' => 2],
                    ],
                    'sunday' => [
                        ['task' => 'Strategic Planning', 'time' => '08:00', 'category' => 'work', 'priority' => 1],
                        ['task' => 'Executive Brunch', 'time' => '10:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Personal Investment Review', 'time' => '12:00', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Exclusive Workout', 'time' => '14:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Week Ahead Planning', 'time' => '16:00', 'category' => 'planning', 'priority' => 1],
                        ['task' => 'Executive Dinner', 'time' => '18:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Strategic Reading', 'time' => '20:00', 'category' => 'development', 'priority' => 2],
                        ['task' => 'Executive Rest', 'time' => '22:00', 'category' => 'routine', 'priority' => 2],
                    ],
                ];

            case 'retiree':
                return [
                    'weekday' => [
                        ['task' => 'Peaceful Wake Up', 'time' => '07:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Morning Stretching', 'time' => '07:30', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Healthy Breakfast', 'time' => '08:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Morning Walk', 'time' => '09:30', 'category' => 'fitness', 'priority' => 1],
                        ['task' => 'Garden Time', 'time' => '10:30', 'category' => 'hobby', 'priority' => 3],
                        ['task' => 'Light Lunch', 'time' => '12:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Afternoon Reading', 'time' => '13:30', 'category' => 'leisure', 'priority' => 3],
                        ['task' => 'Senior Fitness Class', 'time' => '15:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Social Hour with Friends', 'time' => '16:30', 'category' => 'social', 'priority' => 2],
                        ['task' => 'Early Dinner', 'time' => '17:30', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Evening TV/Entertainment', 'time' => '19:00', 'category' => 'leisure', 'priority' => 3],
                        ['task' => 'Bedtime Routine', 'time' => '21:00', 'category' => 'routine', 'priority' => 2],
                    ],
                    'saturday' => [
                        ['task' => 'Leisurely Morning', 'time' => '08:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Weekend Breakfast', 'time' => '09:00', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Community Activities', 'time' => '10:30', 'category' => 'social', 'priority' => 2],
                        ['task' => 'Lunch with Family', 'time' => '12:30', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Afternoon Hobby Time', 'time' => '14:30', 'category' => 'hobby', 'priority' => 3],
                        ['task' => 'Light Exercise', 'time' => '16:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Weekend Dinner', 'time' => '17:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Evening Entertainment', 'time' => '18:30', 'category' => 'leisure', 'priority' => 3],
                    ],
                    'sunday' => [
                        ['task' => 'Sunday Morning Relaxation', 'time' => '08:30', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Light Yoga', 'time' => '09:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Sunday Brunch', 'time' => '10:30', 'category' => 'nutrition', 'priority' => 3],
                        ['task' => 'Family Visit/Call', 'time' => '12:00', 'category' => 'family', 'priority' => 1],
                        ['task' => 'Afternoon Rest', 'time' => '14:00', 'category' => 'routine', 'priority' => 3],
                        ['task' => 'Gentle Walk', 'time' => '15:30', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Sunday Dinner', 'time' => '17:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Week Planning', 'time' => '18:30', 'category' => 'planning', 'priority' => 2],
                        ['task' => 'Early Rest', 'time' => '20:30', 'category' => 'routine', 'priority' => 2],
                    ],
                ];

            default:
                return [
                    'weekday' => [
                        ['task' => 'Morning Routine', 'time' => '07:00', 'category' => 'routine', 'priority' => 2],
                        ['task' => 'Breakfast', 'time' => '08:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Workout', 'time' => '09:00', 'category' => 'fitness', 'priority' => 2],
                        ['task' => 'Lunch', 'time' => '12:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Afternoon Activity', 'time' => '15:00', 'category' => 'general', 'priority' => 3],
                        ['task' => 'Dinner', 'time' => '18:00', 'category' => 'nutrition', 'priority' => 2],
                        ['task' => 'Evening Wind Down', 'time' => '21:00', 'category' => 'routine', 'priority' => 3],
                    ],
                ];
        }
    }
}
