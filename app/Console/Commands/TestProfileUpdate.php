<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\OnboardingController;
use Illuminate\Http\Request;
use App\Models\Participant;

class TestProfileUpdate extends Command
{
    protected $signature = 'test:profile-update';
    protected $description = 'Test profile update functionality';

    public function handle(): void
    {
        $this->info('Testing profile update functionality...');

        // Find the participant
        $participant = Participant::find(1);
        if (!$participant) {
            $this->error('Participant not found');
            return;
        }

        $this->info("Found participant: {$participant->name}");

        // Create mock request
        $request = new Request();
        $request->merge([
            'name' => 'Test User Updated',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'gender' => 'male',
            'dob' => '2007-10-04'
        ]);

        // Set the authenticated user
        $request->setUserResolver(function () use ($participant) {
            return $participant;
        });

        try {
            $controller = new OnboardingController();
            $response = $controller->updateProfile($request);
            
            $this->info("Response Status: " . $response->getStatusCode());
            $this->info("Response Data: " . json_encode($response->getData(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}