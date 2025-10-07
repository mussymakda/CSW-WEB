<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiBase = '/api';

    public function test_user_can_register_via_onboarding(): void
    {
        $response = $this->postJson($this->apiBase.'/onboarding/send-otp', [
            'phone_number' => '+1234567890',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_sliders_endpoint_returns_data(): void
    {
        $response = $this->getJson($this->apiBase.'/sliders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_goals_endpoint_returns_data(): void
    {
        $response = $this->getJson($this->apiBase.'/goals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_schedule_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/schedule');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_guidance_tips_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/guidance-tips');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_suggested_workouts_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/suggested-workouts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_notifications_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_progress_card_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/progress-card');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_mobile_workout_history_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/mobile/workout-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_contact_us_endpoint_accepts_messages(): void
    {
        $response = $this->postJson($this->apiBase.'/mobile/contact-us', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Test message',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_onboarding_status_endpoint(): void
    {
        $response = $this->getJson($this->apiBase.'/onboarding/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }
}
