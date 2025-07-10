<?php

use App\Http\Controllers\Api\ParticipantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Participant API routes
Route::apiResource('participants', ParticipantController::class);

// Goals API endpoint (read-only for app users)
Route::get('goals', function () {
    return response()->json(\App\Models\Goal::all());
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
