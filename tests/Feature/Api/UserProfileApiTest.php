<?php

namespace Tests\Feature\Api;

use App\Models\Participant;
use App\Models\Goal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test goals
        Goal::create(['name' => 'Weight Loss', 'description' => 'Lose weight']);
        Goal::create(['name' => 'Build Muscle', 'description' => 'Gain muscle']);
    }

    public function test_get_user_profile(): void
    {
        $participant = Participant::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'height_cm' => 180,
            'weight_kg' => 75.5,
            'fitness_level' => 'intermediate'
        ]);

        Sanctum::actingAs($participant);

        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $participant->id,
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'date_of_birth' => '01/01/1990',
                        'gender' => 'male',
                        'height' => 180,
                        'weight' => 75.5,
                        'fitness_level' => 'intermediate'
                    ]
                ]);
    }

    public function test_update_user_profile(): void
    {
        $participant = Participant::factory()->create();
        $goal = Goal::first();

        Sanctum::actingAs($participant);

        $updateData = [
            'name' => 'Jane Smith',
            'phone' => '1234567890',
            'date_of_birth' => '15/05/1985',
            'gender' => 'female',
            'height' => 165,
            'weight' => 60.0,
            'fitness_level' => 'advanced',
            'goal_ids' => [$goal->id]
        ];

        $response = $this->putJson('/api/user/profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'name' => 'Jane Smith',
            'phone' => '1234567890',
            'gender' => 'female',
            'height_cm' => 165,
            'weight_kg' => 60.0,
            'fitness_level' => 'advanced'
        ]);
    }

    public function test_upload_profile_picture(): void
    {
        Storage::fake('public');
        
        $participant = Participant::factory()->create();
        Sanctum::actingAs($participant);

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->postJson('/api/user/profile/picture', [
            'profile_picture' => $file
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Profile picture updated successfully'
                ]);

        Storage::disk('public')->assertExists('profile-pictures/' . $file->hashName());
    }

    public function test_get_setup_data(): void
    {
        $participant = Participant::factory()->create();
        Sanctum::actingAs($participant);

        $response = $this->getJson('/api/user/setup-data');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'goals' => [
                            ['name' => 'Weight Loss'],
                            ['name' => 'Build Muscle']
                        ],
                        'fitness_levels' => [
                            ['id' => 'beginner'],
                            ['id' => 'intermediate'],
                            ['id' => 'advanced']
                        ],
                        'genders' => [
                            ['id' => 'male'],
                            ['id' => 'female'],
                            ['id' => 'other'],
                            ['id' => 'prefer_not_to_say']
                        ]
                    ]
                ]);
    }

    public function test_validation_errors(): void
    {
        $participant = Participant::factory()->create();
        Sanctum::actingAs($participant);

        $response = $this->putJson('/api/user/profile', [
            'email' => 'invalid-email',
            'height' => 1000, // Too high
            'weight' => 1000, // Too heavy
            'gender' => 'invalid',
            'fitness_level' => 'invalid'
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'error_code' => 'VALIDATION_ERROR'
                ]);
    }
}
