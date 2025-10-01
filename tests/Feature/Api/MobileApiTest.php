<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\DailySchedule;
use App\Models\Goal;
use App\Models\Participant;
use App\Models\ParticipantCourseProgress;
use App\Models\Slider;
use App\Models\WorkoutSubcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a participant for testing
        $this->participant = Participant::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);
    }

    public function test_get_schedule_returns_daily_schedule_for_date(): void
    {
        Sanctum::actingAs($this->participant);

        // Create test schedule
        DailySchedule::factory()->create([
            'participant_id' => $this->participant->id,
            'task' => 'Morning Workout',
            'time' => '06:30:00',
            'day' => 'wednesday',
            'priority' => 'high',
            'category' => 'fitness',
        ]);

        $response = $this->getJson('/api/mobile/schedule?date=2025-10-01'); // Wednesday

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => '2025-10-01',
                    'day' => 'wednesday',
                    'schedules' => [
                        [
                            'task' => 'Morning Workout',
                            'time' => '06:30',
                            'day' => 'wednesday',
                            'priority' => 'high',
                            'category' => 'fitness',
                        ]
                    ]
                ]
            ]);
    }

    public function test_get_schedule_requires_authentication(): void
    {
        $response = $this->getJson('/api/mobile/schedule?date=2025-10-01');
        
        $response->assertStatus(401);
    }

    public function test_get_schedule_validates_date_format(): void
    {
        Sanctum::actingAs($this->participant);

        $response = $this->getJson('/api/mobile/schedule?date=invalid-date');
        
        $response->assertStatus(422);
    }

    public function test_get_progress_card_returns_active_course_progress(): void
    {
        Sanctum::actingAs($this->participant);

        // Create test data
        $course = Course::factory()->create(['name' => 'Advanced Fitness Training']);
        $courseBatch = CourseBatch::factory()->create([
            'course_id' => $course->id,
            'batch_name' => 'AFT-2025-001',
            'end_date' => now()->addMonth(),
        ]);
        
        ParticipantCourseProgress::factory()->create([
            'participant_id' => $this->participant->id,
            'course_batch_id' => $courseBatch->id,
            'progress_percentage' => 65.50,
            'enrollment_date' => now()->subMonth(),
            'started_at' => now()->subMonth(),
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/mobile/progress-card');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'has_active_course' => true,
                    'progress_percentage' => 65.50,
                    'course_name' => 'Advanced Fitness Training',
                    'batch_name' => 'AFT-2025-001',
                    'status' => 'active',
                ]
            ]);
    }

    public function test_get_progress_card_handles_no_active_course(): void
    {
        Sanctum::actingAs($this->participant);

        $response = $this->getJson('/api/mobile/progress-card');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'has_active_course' => false,
                    'message' => 'No active course found'
                ]
            ]);
    }

    public function test_get_sliders_returns_active_sliders(): void
    {
        Sanctum::actingAs($this->participant);

        // Create active slider
        Slider::factory()->create([
            'title' => 'New Workout Program',
            'description' => 'Join our latest fitness challenge',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
            'sort_order' => 1,
        ]);

        // Create inactive slider (should not appear)
        Slider::factory()->create([
            'title' => 'Inactive Slider',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/mobile/sliders');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sliders' => [
                        [
                            'title' => 'New Workout Program',
                            'description' => 'Join our latest fitness challenge',
                            'sort_order' => 1,
                        ]
                    ]
                ]
            ])
            ->assertJsonMissing(['title' => 'Inactive Slider']);
    }

    public function test_get_suggested_workouts_returns_workout_categories(): void
    {
        Sanctum::actingAs($this->participant);

        // Create goal and relate to participant
        $goal = Goal::factory()->create(['name' => 'Weight Loss']);
        $this->participant->goals()->attach($goal);

        // Create workout subcategory related to goal
        $workout = WorkoutSubcategory::factory()->create([
            'title' => 'Cardio Training',
            'info' => 'High intensity cardio workouts',
        ]);
        $workout->goals()->attach($goal);

        $response = $this->getJson('/api/mobile/suggested-workouts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'suggested_workouts' => [
                        [
                            'title' => 'Cardio Training',
                            'info' => 'High intensity cardio workouts',
                            'related_goals' => [
                                [
                                    'name' => 'Weight Loss'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_get_suggested_workouts_returns_random_workouts_when_no_goals(): void
    {
        Sanctum::actingAs($this->participant);

        // Create workout subcategories without goals
        WorkoutSubcategory::factory()->count(3)->create();

        $response = $this->getJson('/api/mobile/suggested-workouts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'suggested_workouts' => [
                        '*' => [
                            'id',
                            'title',
                            'info',
                            'image_url',
                            'related_goals'
                        ]
                    ],
                    'total_count'
                ]
            ]);
    }

    public function test_mobile_apis_require_authentication(): void
    {
        $endpoints = [
            '/api/mobile/schedule?date=2025-10-01',
            '/api/mobile/progress-card',
            '/api/mobile/sliders',
            '/api/mobile/suggested-workouts',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }
}
