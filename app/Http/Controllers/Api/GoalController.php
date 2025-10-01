<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    /**
     * Get all goals with their workout subcategories and videos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $goals = Goal::with([
                'workoutSubcategories' => function ($query) {
                    $query->with(['workoutVideos']);
                }
            ])->get();

            return response()->json([
                'success' => true,
                'message' => 'Goals retrieved successfully',
                'data' => $goals,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve goals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific goal with its related data
     */
    public function show(Request $request, int $goalId): JsonResponse
    {
        try {
            $goal = Goal::with([
                'workoutSubcategories' => function ($query) {
                    $query->with(['workoutVideos']);
                }
            ])->findOrFail($goalId);

            return response()->json([
                'success' => true,
                'message' => 'Goal retrieved successfully',
                'data' => $goal,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Goal not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}