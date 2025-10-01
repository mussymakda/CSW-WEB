<?php

namespace Tests\Feature\Api;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactUsApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a participant for testing
        $this->participant = Participant::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test Participant',
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);
    }

    public function test_contact_us_sends_email_successfully(): void
    {
        Sanctum::actingAs($this->participant);
        Mail::fake();
        
        $contactData = [
            'title' => 'App Issue - Video playback problem',
            'email' => 'test@example.com',
            'description' => 'I am having trouble playing workout videos. They keep buffering and stopping midway through the workout.',
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your message has been sent successfully! We will get back to you within 24 hours.'
            ])
            ->assertJsonStructure([
                'data' => [
                    'submitted_at',
                    'reference_id'
                ]
            ]);

        // Verify emails were sent
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 2); // Admin + confirmation emails
    }

    public function test_contact_us_with_file_attachment(): void
    {
        Sanctum::actingAs($this->participant);
        Mail::fake();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('screenshot.jpg', 100, 100)->size(500);
        
        $contactData = [
            'title' => 'Bug Report with Screenshot',
            'email' => 'test@example.com',
            'description' => 'Here is a screenshot of the error I am experiencing in the app.',
            'attachment' => $file,
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify file was stored
        Storage::disk('public')->assertExists('contact-attachments/' . $file->hashName());
    }

    public function test_contact_us_validates_required_fields(): void
    {
        Sanctum::actingAs($this->participant);
        
        $response = $this->postJson('/api/mobile/contact-us', [
            'title' => '',
            'email' => 'invalid-email',
            'description' => 'Short',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Please check your input and try again.',
                'error_code' => 'VALIDATION_ERROR'
            ])
            ->assertJsonStructure(['errors']);
    }

    public function test_contact_us_validates_file_size(): void
    {
        Sanctum::actingAs($this->participant);
        Storage::fake('public');

        $largeFile = UploadedFile::fake()->create('large-file.pdf', 15000); // 15MB - exceeds 10MB limit
        
        $contactData = [
            'title' => 'Test with large file',
            'email' => 'test@example.com',
            'description' => 'This message has a file that is too large.',
            'attachment' => $largeFile,
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'VALIDATION_ERROR'
            ]);
    }

    public function test_contact_us_validates_file_type(): void
    {
        Sanctum::actingAs($this->participant);
        Storage::fake('public');

        $invalidFile = UploadedFile::fake()->create('malware.exe', 100);
        
        $contactData = [
            'title' => 'Test with invalid file type',
            'email' => 'test@example.com',
            'description' => 'This message has an invalid file type.',
            'attachment' => $invalidFile,
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'VALIDATION_ERROR'
            ]);
    }

    public function test_contact_us_requires_authentication(): void
    {
        $contactData = [
            'title' => 'Test message',
            'email' => 'test@example.com',
            'description' => 'This should fail without authentication.',
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);
        
        $response->assertStatus(401);
    }

    public function test_contact_us_handles_email_failure_gracefully(): void
    {
        Sanctum::actingAs($this->participant);
        
        // Force mail to fail by using invalid configuration
        config(['mail.default' => 'invalid_mailer']);
        
        $contactData = [
            'title' => 'Test email failure',
            'email' => 'test@example.com',
            'description' => 'This should handle email sending failure gracefully.',
        ];

        $response = $this->postJson('/api/mobile/contact-us', $contactData);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'We are currently experiencing technical difficulties. Please try again later or contact us directly.',
                'error_code' => 'EMAIL_SEND_ERROR'
            ]);
    }
}
