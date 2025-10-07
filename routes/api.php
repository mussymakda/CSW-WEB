<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParticipantController;

use App\Services\AINotificationService;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Slider;

// Authentication routes (public)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Onboarding routes (public - for first-time login)
Route::post('/onboarding/send-otp', [App\Http\Controllers\Api\OnboardingController::class, 'sendOtp']);
Route::post('/onboarding/verify-otp', [App\Http\Controllers\Api\OnboardingController::class, 'verifyOtp']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    
    // Onboarding routes (authenticated)
    Route::prefix('onboarding')->group(function () {
        Route::get('/status', [App\Http\Controllers\Api\OnboardingController::class, 'getOnboardingStatus']);
        Route::post('/accept-terms', [App\Http\Controllers\Api\OnboardingController::class, 'acceptTerms']);
        Route::post('/update-profile', [App\Http\Controllers\Api\OnboardingController::class, 'updateProfile']);
        Route::post('/update-profile-picture', [App\Http\Controllers\Api\OnboardingController::class, 'updateProfilePicture']);
        Route::post('/select-goals', [App\Http\Controllers\Api\OnboardingController::class, 'selectGoals']);
        Route::post('/update-weight', [App\Http\Controllers\Api\OnboardingController::class, 'updateWeight']);
        Route::post('/update-height', [App\Http\Controllers\Api\OnboardingController::class, 'updateHeight']);
        Route::post('/complete', [App\Http\Controllers\Api\OnboardingController::class, 'completeOnboarding']);
    });
    
    // Goals API endpoints
    Route::get('/goals', [App\Http\Controllers\Api\GoalController::class, 'index']);
    Route::get('/goals/{goalId}', [App\Http\Controllers\Api\GoalController::class, 'show']);
    
    // User profile routes (for Flutter app)
    Route::prefix('user')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
        Route::post('/profile/picture', [App\Http\Controllers\Api\UserController::class, 'updateProfilePicture']);
        Route::delete('/profile/picture', [App\Http\Controllers\Api\UserController::class, 'deleteProfilePicture']);
        Route::get('/setup-data', [App\Http\Controllers\Api\UserController::class, 'getAccountSetupData']);
    });
});

// Admin API routes (for Filament panel)
Route::apiResource('participants', ParticipantController::class);

// Mobile app API routes (require authentication)
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/schedule', [App\Http\Controllers\Api\MobileController::class, 'getSchedule']);
    Route::get('/progress-card', [App\Http\Controllers\Api\MobileController::class, 'getProgressCard']);
    Route::get('/sliders', [App\Http\Controllers\Api\MobileController::class, 'getSliders']);
    Route::get('/suggested-workouts', [App\Http\Controllers\Api\MobileController::class, 'getSuggestedWorkouts']);
    Route::get('/guidance-tips', [App\Http\Controllers\Api\MobileController::class, 'getGuidanceTips']);
    Route::get('/workout-details/{subcategoryId}', [App\Http\Controllers\Api\MobileController::class, 'getWorkoutDetails']);
    Route::post('/log-video-view', [App\Http\Controllers\Api\MobileController::class, 'logVideoView']);
    Route::get('/workout-history', [App\Http\Controllers\Api\MobileController::class, 'getWorkoutHistory']);
    Route::get('/notifications', [App\Http\Controllers\Api\MobileController::class, 'getNotifications']);
    Route::post('/contact-us', [App\Http\Controllers\Api\MobileController::class, 'contactUs']);
});

// Mobile Sliders API endpoint
Route::get('sliders', function () {
    $sliders = Slider::active()
        ->current()
        ->ordered()
        ->select(['id', 'title', 'description', 'image_url', 'link_url', 'link_text'])
        ->get();
    
    return response()->json([
        'success' => true,
        'data' => $sliders,
        'count' => $sliders->count()
    ]);
});

// AI Notifications API
Route::prefix('ai')->group(function () {
    // Generate AI notification for specific participant
    Route::post('notifications/generate/{participant}', function (Request $request, $participantId, AINotificationService $aiService) {
        $participant = \App\Models\Participant::findOrFail($participantId);
        
        $type = $request->input('type', null);
        $extraVariables = $request->input('variables', []);
        
        if ($type) {
            $notification = $aiService->generateSpecificNotification($participant, $type, $extraVariables);
        } else {
            $notification = $aiService->generateParticipantNotification($participant);
        }
        
        if ($notification) {
            return response()->json([
                'success' => true,
                'notification' => $notification,
                'message' => 'AI notification generated successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate AI notification'
        ], 500);
    });
    
    // Check Ollama status
    Route::get('status', function (OllamaService $ollamaService) {
        return response()->json([
            'available' => $ollamaService->isAvailable(),
            'enabled' => config('ollama.enabled'),
            'host' => config('ollama.host'),
            'model' => config('ollama.model'),
        ]);
    });
    
    // Get AI notification statistics
    Route::get('stats', function (AINotificationService $aiService) {
        return response()->json($aiService->getStatistics());
    });
});

// Daily schedules for a specific participant
Route::get('participants/{participant}/schedules', function ($participantId) {
    $participant = \App\Models\Participant::findOrFail($participantId);
    return response()->json($participant->dailySchedules);
});

// Add daily schedule for a participant
Route::post('participants/{participant}/schedules', function (Request $request, $participantId) {
    $participant = \App\Models\Participant::findOrFail($participantId);
    
    $validated = $request->validate([
        'task' => 'required|string|max:255',
        'time' => 'required|date_format:H:i',
        'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
    ]);
    
    $schedule = $participant->dailySchedules()->create($validated);
    return response()->json($schedule, 201);
});
