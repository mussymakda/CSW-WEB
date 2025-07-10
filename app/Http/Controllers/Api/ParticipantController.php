<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $participants = Participant::with(['goal', 'dailySchedules'])->get();
        return response()->json($participants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:participants',
            'phone' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'profile_picture' => 'nullable|image|max:2048',
            'gender' => 'nullable|in:male,female,other',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'aceds_no' => 'nullable|string|max:255',
            'goal_id' => 'nullable|exists:goals,id',
        ]);

        $participant = Participant::create($validated);
        return response()->json($participant->load(['goal', 'dailySchedules']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $participant = Participant::with(['goal', 'dailySchedules'])->findOrFail($id);
        return response()->json($participant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $participant = Participant::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:participants,email,' . $id,
            'phone' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'profile_picture' => 'nullable|image|max:2048',
            'gender' => 'nullable|in:male,female,other',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'aceds_no' => 'nullable|string|max:255',
            'goal_id' => 'nullable|exists:goals,id',
        ]);

        $participant->update($validated);
        return response()->json($participant->load(['goal', 'dailySchedules']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $participant = Participant::findOrFail($id);
        $participant->delete();
        return response()->json(['message' => 'Participant deleted successfully']);
    }
}
