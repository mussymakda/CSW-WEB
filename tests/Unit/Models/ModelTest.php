<?php

namespace Tests\Unit\Models;

use App\Models\Goal;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_required_attributes(): void
    {
        $user = new User;
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_user_password_is_hidden(): void
    {
        $user = new User;

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }

    public function test_user_casts_email_verified_at(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    public function test_participant_model_exists(): void
    {
        $this->assertTrue(class_exists(Participant::class));
    }

    public function test_participant_has_fillable_attributes(): void
    {
        $participant = new Participant;
        $fillable = $participant->getFillable();

        $this->assertIsArray($fillable);
        $this->assertNotEmpty($fillable);
    }

    public function test_goal_model_exists(): void
    {
        $this->assertTrue(class_exists(Goal::class));
    }

    public function test_goal_has_fillable_attributes(): void
    {
        $goal = new Goal;
        $fillable = $goal->getFillable();

        $this->assertIsArray($fillable);
        $this->assertNotEmpty($fillable);
    }
}
