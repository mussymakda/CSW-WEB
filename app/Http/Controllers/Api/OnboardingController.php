<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    /**
     * Send OTP to participant's email for first-time login verification
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:participants,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = Participant::where('email', $request->email)->first();

            // Generate and send OTP
            $otp = $participant->generateEmailOtp();

            // Send OTP via email (you can customize this with proper email template)
            Mail::raw(
                "Your verification code is: {$otp}\n\nThis code will expire in 10 minutes.",
                function ($message) use ($participant) {
                    $message->to($participant->email)
                        ->subject('Your Verification Code');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'data' => [
                    'email' => $participant->email,
                    'expires_in_minutes' => 10,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify OTP and allow password change
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:participants,email',
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = Participant::where('email', $request->email)->first();

            if (! $participant->verifyEmailOtp($request->otp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 401);
            }

            // Update password and mark as changed from default
            $participant->update([
                'password' => Hash::make($request->new_password),
                'password_changed_from_default' => true,
            ]);

            // Generate token for authenticated session
            $token = $participant->createToken('onboarding-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'OTP verified and password updated successfully',
                'data' => [
                    'participant' => $participant->load('goal', 'goals'),
                    'token' => $token,
                    'needs_onboarding' => $participant->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept terms and conditions
     */
    public function acceptTerms(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();

            $participant->update([
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions accepted successfully',
                'data' => [
                    'terms_accepted' => true,
                    'terms_accepted_at' => $participant->fresh()->terms_accepted_at,
                    'needs_onboarding' => $participant->fresh()->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept terms',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update participant profile information
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:participants,email,'.$request->user()->id,
            'phone' => 'nullable|string|max:255',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = $request->user();

            // Handle profile picture upload
            $updateData = [];

            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($participant->profile_picture && \Storage::disk('public')->exists($participant->profile_picture)) {
                    \Storage::disk('public')->delete($participant->profile_picture);
                }

                // Store new profile picture
                $profilePicturePath = $request->file('profile_picture')->store('profiles', 'public');
                $updateData['profile_picture'] = $profilePicturePath;
            }

            // Update other profile fields
            $fields = ['name', 'email', 'phone', 'dob', 'gender'];
            foreach ($fields as $field) {
                if ($request->filled($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            $participant->update($updateData);

            // Get fresh participant data with profile picture URL
            $freshParticipant = $participant->fresh();
            $responseData = $freshParticipant->toArray();

            // Add profile picture URL if exists
            if ($freshParticipant->profile_picture) {
                $responseData['profile_picture_url'] = asset('storage/'.$freshParticipant->profile_picture);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'participant' => $responseData,
                    'needs_onboarding' => $freshParticipant->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Select multiple goals for participant
     */
    public function selectGoals(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'goal_ids' => 'required|array|min:1',
            'goal_ids.*' => 'exists:goals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = $request->user();

            // Sync the selected goals (this will replace any existing goal selections)
            $participant->goals()->sync($request->goal_ids);

            // Also set the first goal as the primary goal for backward compatibility
            if (count($request->goal_ids) > 0) {
                $participant->update(['goal_id' => $request->goal_ids[0]]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Goals selected successfully',
                'data' => [
                    'selected_goals' => $participant->goals()->get(),
                    'primary_goal' => $participant->goal,
                    'needs_onboarding' => $participant->fresh()->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to select goals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update participant weight
     */
    public function updateWeight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'weight' => 'required|numeric|min:20|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = $request->user();

            $participant->update([
                'weight' => $request->weight,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Weight updated successfully',
                'data' => [
                    'weight' => $participant->weight,
                    'needs_onboarding' => $participant->fresh()->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update weight',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update participant height
     */
    public function updateHeight(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'height' => 'required|numeric|min:1.0|max:2.5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = $request->user();

            $participant->update([
                'height' => $request->height,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Height updated successfully',
                'data' => [
                    'height' => $participant->height,
                    'needs_onboarding' => $participant->fresh()->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update height',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete the onboarding process
     */
    public function completeOnboarding(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();

            // Check if all required onboarding steps are completed
            if ($participant->needsOnboarding()) {
                $missing = [];

                if (! $participant->email_verified_at) {
                    $missing[] = 'Email verification';
                }
                if (! $participant->password_changed_from_default) {
                    $missing[] = 'Password change';
                }
                if (! $participant->terms_accepted) {
                    $missing[] = 'Terms acceptance';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete onboarding. Missing steps: '.implode(', ', $missing),
                    'data' => [
                        'missing_steps' => $missing,
                    ],
                ], 400);
            }

            $participant->completeOnboarding();

            return response()->json([
                'success' => true,
                'message' => 'Onboarding completed successfully',
                'data' => [
                    'participant' => $participant->fresh()->load('goal', 'goals'),
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => $participant->fresh()->onboarding_completed_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete onboarding',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload or update profile picture
     */
    public function updateProfilePicture(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant = $request->user();

            // Delete old profile picture if exists
            if ($participant->profile_picture && \Storage::disk('public')->exists($participant->profile_picture)) {
                \Storage::disk('public')->delete($participant->profile_picture);
            }

            // Store new profile picture
            $profilePicturePath = $request->file('profile_picture')->store('profiles', 'public');

            $participant->update([
                'profile_picture' => $profilePicturePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'data' => [
                    'profile_picture' => $profilePicturePath,
                    'profile_picture_url' => asset('storage/'.$profilePicturePath),
                    'needs_onboarding' => $participant->fresh()->needsOnboarding(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get onboarding status
     */
    public function getOnboardingStatus(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();

            $status = [
                'email_verified' => ! is_null($participant->email_verified_at),
                'password_changed' => $participant->password_changed_from_default,
                'terms_accepted' => $participant->terms_accepted,
                'profile_completed' => ! is_null($participant->name),
                'goals_selected' => $participant->goals()->count() > 0,
                'weight_provided' => ! is_null($participant->weight),
                'height_provided' => ! is_null($participant->height),
                'onboarding_completed' => $participant->onboarding_completed,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'needs_onboarding' => $participant->needsOnboarding(),
                    'participant' => $participant->load('goal', 'goals'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get onboarding status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
