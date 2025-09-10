<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // The root route redirects to /admin, which requires authentication
        // So we expect a redirect (302) not a 200 status
        $response->assertRedirect('/admin');
    }
}
