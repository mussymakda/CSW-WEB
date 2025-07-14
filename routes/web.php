<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::get('/test-data', function () {
    $data = [
        'participants' => \App\Models\Participant::count(),
        'goals' => \App\Models\Goal::count(),
        'workout_subcategories' => \App\Models\WorkoutSubcategory::count(),
        'workout_videos' => \App\Models\WorkoutVideo::count(),
        'user_notifications' => \App\Models\UserNotification::count(),
        'users' => \App\Models\User::count(),
    ];
    
    $sampleGoal = \App\Models\Goal::with('workoutSubcategories')->first();
    $sampleSubcategory = \App\Models\WorkoutSubcategory::with('workoutVideos')->first();
    $sampleParticipant = \App\Models\Participant::with('userNotifications')->first();
    
    return response()->json([
        'counts' => $data,
        'sample_goal_subcategories' => $sampleGoal ? $sampleGoal->workoutSubcategories->count() : 0,
        'sample_subcategory_videos' => $sampleSubcategory ? $sampleSubcategory->workoutVideos->count() : 0,
        'sample_participant_notifications' => $sampleParticipant ? $sampleParticipant->userNotifications->count() : 0,
    ]);
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
