<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get authenticated user's profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $participant->id,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'name' => $participant->name,
                    'profile_picture' => $participant->profile_picture_url,
                    'date_of_birth' => $participant->date_of_birth ? $participant->date_of_birth->format('d/m/Y') : null,
                    'gender' => $participant->gender,
                    'height' => $participant->height_cm,
                    'weight' => $participant->weight_kg,
                    'fitness_level' => $participant->fitness_level,
                    'goals' => $participant->goals ? $participant->goals->map(function ($goal) {
                        return [
                            'id' => $goal->id,
                            'name' => $goal->name,
                            'description' => $goal->description
                        ];
                    }) : [],
                    'is_onboarding_completed' => (bool) $participant->onboarding_completed,
                    'created_at' => $participant->created_at?->toISOString(),
                    'updated_at' => $participant->updated_at?->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load profile information. Please try again later.',
                'error_code' => 'PROFILE_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * Update authenticated user's profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            // Validation rules
            $validator = Validator::make($request->all(), [
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('participants', 'email')->ignore($participant->id)
                ],
                'phone' => 'sometimes|string|max:20',
                'name' => 'sometimes|string|max:255',
                'date_of_birth' => 'sometimes|date_format:d/m/Y|before:today',
                'gender' => 'sometimes|in:male,female,other,prefer_not_to_say',
                'height' => 'sometimes|numeric|between:100,250',
                'weight' => 'sometimes|numeric|between:20,500',
                'fitness_level' => 'sometimes|in:beginner,intermediate,advanced',
                'goal_ids' => 'sometimes|array',
                'goal_ids.*' => 'exists:goals,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check your input and try again.',
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            
            // Convert date format if provided
            if (isset($validatedData['date_of_birth'])) {
                $validatedData['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['date_of_birth'])->format('Y-m-d');
            }
            
            // Handle height and weight mapping
            if (isset($validatedData['height'])) {
                $validatedData['height_cm'] = $validatedData['height'];
                unset($validatedData['height']);
            }
            
            if (isset($validatedData['weight'])) {
                $validatedData['weight_kg'] = $validatedData['weight'];
                unset($validatedData['weight']);
            }
            
            // Remove goal_ids from participant data
            $goalIds = $validatedData['goal_ids'] ?? null;
            unset($validatedData['goal_ids']);

            // Update participant
            $participant->update($validatedData);
            
            // Sync goals if provided
            if ($goalIds !== null) {
                $participant->goals()->sync($goalIds);
            }

            // Reload relationships
            $participant->refresh();
            $participant->load('goals');

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $participant->id,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'name' => $participant->name,
                    'profile_picture' => $participant->profile_picture_url,
                    'date_of_birth' => $participant->date_of_birth ? $participant->date_of_birth->format('d/m/Y') : null,
                    'gender' => $participant->gender,
                    'height' => $participant->height_cm,
                    'weight' => $participant->weight_kg,
                    'fitness_level' => $participant->fitness_level,
                    'goals' => $participant->goals ? $participant->goals->map(function ($goal) {
                        return [
                            'id' => $goal->id,
                            'name' => $goal->name,
                            'description' => $goal->description
                        ];
                    }) : [],
                    'is_onboarding_completed' => (bool) $participant->onboarding_completed,
                    'updated_at' => $participant->updated_at?->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update profile. Please try again later.',
                'error_code' => 'PROFILE_UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Upload/update profile picture
     */
    public function updateProfilePicture(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120' // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a valid image file (JPEG, JPG, PNG, or GIF) under 5MB.',
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old profile picture if exists
            if ($participant->profile_picture) {
                Storage::disk('public')->delete($participant->profile_picture);
            }

            // Store new profile picture
            $profilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            
            // Update participant
            $participant->update([
                'profile_picture' => $profilePicturePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'data' => [
                    'profile_picture' => $participant->profile_picture_url
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update profile picture. Please try again later.',
                'error_code' => 'PROFILE_PICTURE_UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            if ($participant->profile_picture) {
                Storage::disk('public')->delete($participant->profile_picture);
                $participant->update(['profile_picture' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully',
                'data' => [
                    'profile_picture' => null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to remove profile picture. Please try again later.',
                'error_code' => 'PROFILE_PICTURE_DELETE_ERROR'
            ], 500);
        }
    }

    /**
     * Get account setup data (goals, fitness levels, etc.)
     */
    public function getAccountSetupData(Request $request): JsonResponse
    {
        try {
            $goals = \App\Models\Goal::select('id', 'name')->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'goals' => $goals,
                    'fitness_levels' => [
                        ['id' => 'beginner', 'name' => 'Beginner', 'description' => 'Just getting started with fitness'],
                        ['id' => 'intermediate', 'name' => 'Intermediate', 'description' => 'Regular exercise routine'],
                        ['id' => 'advanced', 'name' => 'Advanced', 'description' => 'Experienced fitness enthusiast']
                    ],
                    'genders' => [
                        ['id' => 'male', 'name' => 'Male'],
                        ['id' => 'female', 'name' => 'Female'],
                        ['id' => 'other', 'name' => 'Other'],
                        ['id' => 'prefer_not_to_say', 'name' => 'Prefer not to say']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load account setup data. Please try again later.',
                'error_code' => 'SETUP_DATA_FETCH_ERROR'
            ], 500);
        }
    }
}
