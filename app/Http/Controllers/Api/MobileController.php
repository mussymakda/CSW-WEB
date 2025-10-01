<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Models\GuidanceTip;
use App\Models\ParticipantCourseProgress;
use App\Models\Slider;
use App\Models\VideoView;
use App\Models\WorkoutSubcategory;
use App\Models\WorkoutVideo;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MobileController extends Controller
{
    /**
     * Get all participant schedules
     */
    public function getSchedule(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();

            $schedules = DailySchedule::where('participant_id', $participant->id)
                ->orderBy('day')
                ->orderBy('time')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'task' => $schedule->task,
                        'time' => $schedule->time?->format('H:i'),
                        'day' => $schedule->day,
                        'is_completed' => $schedule->is_completed,
                        'completed_at' => $schedule->completed_at?->format('Y-m-d H:i:s'),
                        'completion_notes' => $schedule->completion_notes,
                        'priority' => $schedule->priority,
                        'category' => $schedule->category,
                        'location' => $schedule->location,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'schedules' => $schedules
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load your schedule at the moment. Please try again later.',
                'error_code' => 'SCHEDULE_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * Get participant progress card data
     */
    public function getProgressCard(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            $progress = ParticipantCourseProgress::with('courseBatch.course')
                ->where('participant_id', $participant->id)
                ->whereNotNull('started_at')
                ->whereNull('completed_at')
                ->orderBy('enrollment_date', 'desc')
                ->first();

            if (!$progress) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'has_active_course' => false,
                        'message' => 'No active course found'
                    ]
                ]);
            }

            // Calculate expected completion date based on course batch end date
            $expectedCompletionDate = $progress->courseBatch->end_date ?? 
                                    Carbon::now()->addMonths(3)->format('Y-m-d');

            return response()->json([
                'success' => true,
                'data' => [
                    'has_active_course' => true,
                    'progress_percentage' => (float) $progress->progress_percentage,
                    'course_name' => $progress->courseBatch->course->name ?? 'Unknown Course',
                    'batch_name' => $progress->courseBatch->batch_name ?? 'N/A',
                    'expected_completion_date' => $expectedCompletionDate,
                    'enrollment_date' => $progress->enrollment_date?->format('Y-m-d'),
                    'status' => $progress->status,
                    'tests_progress' => [
                        'total' => $progress->total_tests ?? 0,
                        'taken' => $progress->tests_taken ?? 0,
                        'passed' => $progress->tests_passed ?? 0,
                    ],
                    'exams_progress' => [
                        'total' => $progress->total_exams ?? 0,
                        'taken' => $progress->exams_taken ?? 0,
                        'needed' => $progress->exams_needed ?? 0,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load your progress information. Please try again later.',
                'error_code' => 'PROGRESS_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * Get mobile slider data
     */
    public function getSliders(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();
            
            $sliders = Slider::where('is_active', true)
                ->where(function ($query) use ($today) {
                    $query->where('start_date', '<=', $today)
                          ->orWhereNull('start_date');
                })
                ->where(function ($query) use ($today) {
                    $query->where('end_date', '>=', $today)
                          ->orWhereNull('end_date');
                })
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($slider) {
                    return [
                        'id' => $slider->id,
                        'title' => $slider->title,
                        'description' => $slider->description,
                        'image_url' => $slider->image_url ? asset('storage/' . $slider->image_url) : null,
                        'link_url' => $slider->link_url,
                        'link_text' => $slider->link_text,
                        'start_date' => $slider->start_date?->format('Y-m-d'),
                        'end_date' => $slider->end_date?->format('Y-m-d'),
                        'sort_order' => $slider->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'sliders' => $sliders
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load promotional content. Please try again later.',
                'error_code' => 'SLIDERS_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * Get suggested workouts for the participant (top 5 workout categories)
     */
    public function getSuggestedWorkouts(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            // Get participant's selected goals to suggest relevant workouts
            $participantGoals = $participant->goals()->pluck('goals.id');
            
            $suggestedWorkouts = WorkoutSubcategory::with('goals')
                ->when($participantGoals->isNotEmpty(), function ($query) use ($participantGoals) {
                    // Prioritize workouts related to participant's goals
                    $query->whereHas('goals', function ($goalQuery) use ($participantGoals) {
                        $goalQuery->whereIn('goals.id', $participantGoals);
                    });
                })
                ->inRandomOrder() // Add variety to suggestions
                ->limit(5)
                ->get()
                ->map(function ($workout) {
                    return [
                        'id' => $workout->id,
                        'title' => $workout->title,
                        'info' => $workout->info,
                        'image_url' => $workout->image_url,
                        'related_goals' => $workout->goals->map(function ($goal) {
                            return [
                                'id' => $goal->id,
                                'name' => $goal->name,
                            ];
                        }),
                    ];
                });

            // If no goal-specific workouts found or we need more, get random popular ones
            if ($suggestedWorkouts->count() < 5) {
                $additionalWorkouts = WorkoutSubcategory::whereNotIn('id', $suggestedWorkouts->pluck('id'))
                    ->inRandomOrder()
                    ->limit(5 - $suggestedWorkouts->count())
                    ->get()
                    ->map(function ($workout) {
                        return [
                            'id' => $workout->id,
                            'title' => $workout->title,
                            'info' => $workout->info,
                            'image_url' => $workout->image_url,
                            'related_goals' => [],
                        ];
                    });
                
                $suggestedWorkouts = $suggestedWorkouts->merge($additionalWorkouts);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suggested_workouts' => $suggestedWorkouts->values(),
                    'total_count' => $suggestedWorkouts->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load workout suggestions. Please try again later.',
                'error_code' => 'SUGGESTED_WORKOUTS_ERROR'
            ], 500);
        }
    }

    /**
     * Get guidance tips for mobile app
     */
    public function getGuidanceTips(Request $request): JsonResponse
    {
        try {
            $guidanceTips = GuidanceTip::active()
                ->ordered()
                ->get()
                ->map(function ($tip) {
                    return [
                        'id' => $tip->id,
                        'name' => $tip->name,
                        'image_url' => $tip->image_url,
                        'link' => $tip->link,
                        'sort_order' => $tip->sort_order,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'guidance_tips' => $guidanceTips
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load guidance tips. Please try again later.',
                'error_code' => 'GUIDANCE_TIPS_ERROR'
            ], 500);
        }
    }

    /**
     * Get workout subcategory details with videos grouped by duration
     */
    public function getWorkoutDetails(Request $request, $subcategoryId): JsonResponse
    {
        try {
            $participant = $request->user();
            
            // Validate subcategory ID
            if (!is_numeric($subcategoryId) || $subcategoryId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a valid workout category.',
                    'error_code' => 'INVALID_WORKOUT_ID'
                ], 400);
            }
            
            $workoutSubcategory = WorkoutSubcategory::with(['workoutVideos' => function ($query) {
                $query->orderBy('duration_minutes');
            }])->find($subcategoryId);
            
            if (!$workoutSubcategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested workout category was not found.',
                    'error_code' => 'WORKOUT_NOT_FOUND'
                ], 404);
            }

            // Get viewed video IDs for this participant
            $viewedVideoIds = VideoView::where('participant_id', $participant->id)
                ->whereIn('workout_video_id', $workoutSubcategory->workoutVideos->pluck('id'))
                ->pluck('workout_video_id')
                ->toArray();

            // Group videos by duration
            $videosByDuration = [
                'all' => [],
                '5m' => [],
                '10m' => [],
                '15m' => [],
                '20m' => [],
                '30m' => [],
                '45m' => [],
                '50m' => []
            ];

            foreach ($workoutSubcategory->workoutVideos as $video) {
                $videoData = [
                    'id' => $video->id,
                    'title' => $video->title,
                    'duration_minutes' => $video->duration_minutes,
                    'duration_formatted' => $video->duration_formatted,
                    'video_url' => $video->video_url,
                    'image_url' => $video->image_url,
                    'is_viewed' => in_array($video->id, $viewedVideoIds),
                ];

                // Add to 'all' category
                $videosByDuration['all'][] = $videoData;

                // Add to specific duration category
                if ($video->duration_minutes <= 5) {
                    $videosByDuration['5m'][] = $videoData;
                } elseif ($video->duration_minutes <= 10) {
                    $videosByDuration['10m'][] = $videoData;
                } elseif ($video->duration_minutes <= 15) {
                    $videosByDuration['15m'][] = $videoData;
                } elseif ($video->duration_minutes <= 20) {
                    $videosByDuration['20m'][] = $videoData;
                } elseif ($video->duration_minutes <= 30) {
                    $videosByDuration['30m'][] = $videoData;
                } elseif ($video->duration_minutes <= 45) {
                    $videosByDuration['45m'][] = $videoData;
                } else {
                    $videosByDuration['50m'][] = $videoData;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'workout_subcategory' => [
                        'id' => $workoutSubcategory->id,
                        'title' => $workoutSubcategory->title,
                        'info' => $workoutSubcategory->info,
                        'image_url' => $workoutSubcategory->image_url,
                    ],
                    'videos_by_duration' => $videosByDuration,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load workout details. Please try again later.',
                'error_code' => 'WORKOUT_DETAILS_ERROR'
            ], 500);
        }
    }

    /**
     * Log video view by participant
     */
    public function logVideoView(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            $validator = Validator::make($request->all(), [
                'workout_video_id' => 'required|integer|exists:workout_videos,id',
                'duration_watched_seconds' => 'integer|min:0|max:7200',
            ], [
                'workout_video_id.required' => 'Video ID is required to track your progress.',
                'workout_video_id.integer' => 'Please provide a valid video ID.',
                'workout_video_id.exists' => 'The specified video was not found.',
                'duration_watched_seconds.integer' => 'Watch duration must be a number.',
                'duration_watched_seconds.min' => 'Watch duration cannot be negative.',
                'duration_watched_seconds.max' => 'Watch duration seems unusually long. Please try again.',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to track video progress.',
                    'error_code' => 'VIDEO_TRACKING_VALIDATION_ERROR',
                    'errors' => $validator->errors()
                ], 422);
            }

            $videoView = VideoView::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'workout_video_id' => $request->workout_video_id,
                    'viewed_at' => now()->format('Y-m-d'), // Daily tracking
                ],
                [
                    'duration_watched_seconds' => $request->duration_watched_seconds ?? 0,
                    'viewed_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Video view logged successfully',
                'data' => [
                    'video_view_id' => $videoView->id,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to track your video progress. This won\'t affect your workout experience.',
                'error_code' => 'VIDEO_TRACKING_ERROR'
            ], 500);
        }
    }

    /**
     * Get workout history for participant
     */
    public function getWorkoutHistory(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            $workoutHistory = VideoView::where('participant_id', $participant->id)
                ->with(['workoutVideo.workoutSubcategory'])
                ->orderBy('viewed_at', 'desc')
                ->get()
                ->groupBy(function ($view) {
                    return $view->viewed_at->format('Y-m-d');
                })
                ->map(function ($viewsForDay, $date) {
                    $subcategoriesForDay = $viewsForDay->groupBy('workoutVideo.workoutSubcategory.id')
                        ->map(function ($subcategoryViews) {
                            $subcategory = $subcategoryViews->first()->workoutVideo->workoutSubcategory;
                            return [
                                'subcategory' => [
                                    'id' => $subcategory->id,
                                    'title' => $subcategory->title,
                                    'info' => $subcategory->info,
                                    'image_url' => $subcategory->image_url,
                                ],
                                'videos_watched' => $subcategoryViews->map(function ($view) {
                                    return [
                                        'id' => $view->workoutVideo->id,
                                        'title' => $view->workoutVideo->title,
                                        'duration_minutes' => $view->workoutVideo->duration_minutes,
                                        'duration_formatted' => $view->workoutVideo->duration_formatted,
                                        'duration_watched_seconds' => $view->duration_watched_seconds,
                                        'viewed_at' => $view->viewed_at->format('H:i'),
                                    ];
                                })->values(),
                            ];
                        })->values();

                    return [
                        'date' => $date,
                        'workout_subcategories' => $subcategoriesForDay,
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'workout_history' => $workoutHistory
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load your workout history. Please try again later.',
                'error_code' => 'WORKOUT_HISTORY_ERROR'
            ], 500);
        }
    }

    /**
     * Get notifications for participant (current and past)
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $participant = $request->user();
            
            $notifications = UserNotification::where('user_id', $participant->id)
                ->where('scheduled_for', '<=', now())
                ->orderBy('scheduled_for', 'desc')
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'scheduled_for' => $notification->scheduled_for->format('Y-m-d H:i:s'),
                        'is_read' => $notification->is_read,
                        'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage(),
                'error_code' => 'NOTIFICATIONS_FETCH_ERROR'
            ], 500);
        }
    }

    /**
     * Send contact us email
     */
    public function contactUs(Request $request): JsonResponse
    {
        try {
            // Custom validation with user-friendly messages
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|min:3|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'description' => 'required|string|min:10|max:5000',
                'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,zip'
            ], [
                'title.required' => 'Please provide a subject for your message.',
                'title.min' => 'Subject must be at least 3 characters long.',
                'title.max' => 'Subject cannot exceed 255 characters.',
                'email.required' => 'Please provide your email address.',
                'email.email' => 'Please provide a valid email address.',
                'description.required' => 'Please provide a description of your inquiry.',
                'description.min' => 'Description must be at least 10 characters long.',
                'description.max' => 'Description cannot exceed 5000 characters.',
                'attachment.file' => 'The attachment must be a valid file.',
                'attachment.max' => 'Attachment size cannot exceed 10MB.',
                'attachment.mimes' => 'Attachment must be a PDF, Word document, image, text file, or ZIP archive.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check your input and try again.',
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()
                ], 422);
            }

            $participant = $request->user();
            $attachmentPath = null;
            $attachmentName = null;

            // Handle file attachment
            if ($request->hasFile('attachment')) {
                try {
                    $file = $request->file('attachment');
                    $attachmentName = $file->getClientOriginalName();
                    $sanitizedName = time() . '_' . preg_replace('/[^A-Za-z0-9\-_\.]/', '', $attachmentName);
                    $attachmentPath = $file->storeAs('contact-attachments', $sanitizedName, 'public');
                    
                    if (!$attachmentPath) {
                        throw new \Exception('Failed to save attachment');
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to process your attachment. Please try with a different file or contact us without an attachment.',
                        'error_code' => 'ATTACHMENT_UPLOAD_ERROR'
                    ], 422);
                }
            }

            // Prepare email data
            $contactData = [
                'participant_name' => $participant->name,
                'participant_email' => $request->email,
                'participant_id' => $participant->id,
                'title' => $request->title,
                'description' => $request->description,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'submitted_at' => now()->format('Y-m-d H:i:s'),
            ];

            // Get admin email from config
            $adminEmail = config('mail.admin_email', 'admin@example.com');
            
            try {
                // Send email to admin
                Mail::send('emails.contact-us', $contactData, function ($message) use ($contactData, $adminEmail, $attachmentPath) {
                    $message->to($adminEmail)
                            ->subject('Contact Us: ' . $contactData['title'])
                            ->replyTo($contactData['participant_email'], $contactData['participant_name']);
                    
                    // Attach file if exists
                    if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                        $message->attach(storage_path('app/public/' . $attachmentPath), [
                            'as' => $contactData['attachment_name']
                        ]);
                    }
                });

                // Send confirmation email to participant
                Mail::send('emails.contact-us-confirmation', $contactData, function ($message) use ($contactData) {
                    $message->to($contactData['participant_email'], $contactData['participant_name'])
                            ->subject('We received your message - ' . $contactData['title']);
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Your message has been sent successfully! We will get back to you within 24 hours.',
                    'data' => [
                        'submitted_at' => $contactData['submitted_at'],
                        'reference_id' => 'CSW-' . $participant->id . '-' . time()
                    ]
                ], 200);

            } catch (\Exception $e) {
                // Clean up uploaded file if email fails
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'We are currently experiencing technical difficulties. Please try again later or contact us directly.',
                    'error_code' => 'EMAIL_SEND_ERROR'
                ], 503);
            }

        } catch (\Exception $e) {
            // Clean up uploaded file if something goes wrong
            if (isset($attachmentPath) && $attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error_code' => 'CONTACT_US_ERROR'
            ], 500);
        }
    }
}
