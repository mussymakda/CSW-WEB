<?php

use App\Http\Controllers\Api\ParticipantController;
use App\Services\OllamaService;
use App\Services\AINotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Slider;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Participant API routes
Route::apiResource('participants', ParticipantController::class);

// Goals API endpoint (read-only for app users)
Route::get('goals', function () {
    return response()->json(\App\Models\Goal::all());
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
