<?php

namespace App\Services;

use App\Models\DailySchedule;
use App\Models\Participant;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AINotificationService
{
    protected OllamaService $ollama;

    protected array $prompts;

    protected bool $enabled;

    public function __construct(OllamaService $ollama)
    {
        $this->ollama = $ollama;
        $this->prompts = config('ollama.notifications.prompts', []);
        $this->enabled = config('ollama.notifications.enabled', false);
    }

    /**
     * Generate scheduled notifications based on participant schedules and timing
     */
    public function generateScheduledNotifications(): array
    {
        $results = ['generated' => 0, 'errors' => []];

        try {
            // Get participants with upcoming tasks
            $participants = $this->getParticipantsWithUpcomingTasks();

            // Process in batches
            $batchSize = config('ollama.notifications.batch_size', 10);
            $batches = $participants->chunk($batchSize);

            foreach ($batches as $batch) {
                $batchResults = $this->processBatch($batch);
                $results['generated'] += $batchResults['generated'];
                $results['errors'] = array_merge($results['errors'], $batchResults['errors']);

                // Brief pause between batches to prevent overwhelming Ollama
                if ($batches->count() > 1) {
                    sleep(1);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error generating scheduled notifications: '.$e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function generateNotifications(): array
    {
        return $this->generateScheduledNotifications();
    }

    /**
     * Get participants with upcoming tasks that need notifications
     */
    protected function getParticipantsWithUpcomingTasks(): Collection
    {
        $now = Carbon::now();
        $currentDay = strtolower($now->format('l'));
        $tomorrow = strtolower($now->addDay()->format('l'));

        // Get participants with schedules for today or tomorrow
        return Participant::with(['goal', 'dailySchedules' => function ($query) use ($currentDay, $tomorrow) {
            $query->whereIn('day', [$currentDay, $tomorrow])
                ->where('is_completed', false)
                ->orderBy('time');
        }])
            ->whereHas('dailySchedules', function ($query) use ($currentDay, $tomorrow) {
                $query->where('is_completed', false)
                    ->whereIn('day', [$currentDay, $tomorrow]);
            })
            ->get();
    }

    /**
     * Process a batch of participants for notification generation
     */
    protected function processBatch(Collection $participants): array
    {
        $results = ['generated' => 0, 'errors' => []];

        foreach ($participants as $participant) {
            try {
                // Check if participant already has recent notifications to avoid spam
                if ($this->hasRecentNotification($participant)) {
                    continue;
                }

                $notification = $this->generateTimeBasedNotification($participant);

                if ($notification) {
                    $results['generated']++;
                    Log::info("Generated time-based notification for participant: {$participant->name}");
                } else {
                    Log::debug("No notification needed for participant: {$participant->name}");
                }
            } catch (\Exception $e) {
                $error = "Failed to generate notification for {$participant->name}: ".$e->getMessage();
                $results['errors'][] = $error;
                Log::error($error);
            }
        }

        return $results;
    }

    /**
     * Generate time-based notification for participant with upcoming tasks
     */
    protected function generateTimeBasedNotification(Participant $participant): ?UserNotification
    {
        $upcomingTasks = $participant->dailySchedules
            ->where('is_completed', false)
            ->sortBy('time')
            ->take(3); // Focus on next 3 tasks

        if ($upcomingTasks->isEmpty()) {
            return null;
        }

        // Determine notification type based on task timing
        $notificationType = $this->determineTimeBasedNotificationType($upcomingTasks);

        // Calculate delivery time (when notification should be shown locally)
        $deliveryTime = $this->calculateDeliveryTime($upcomingTasks->first());

        // Generate the notification with timing context
        $variables = $this->prepareTimeBasedVariables($participant, $upcomingTasks, $notificationType);
        $notificationText = $this->ollama->generateNotification($notificationType, $variables);

        if (! $notificationText) {
            return null;
        }

        return UserNotification::create([
            'icon' => $this->getNotificationIcon($notificationType),
            'notification_text' => $notificationText,
            'participant_id' => $participant->id,
            'is_read' => false,
            'notification_type' => $notificationType,
            'delivery_time' => $deliveryTime,
            'created_at' => now(),
        ]);
    }

    /**
     * Check if participant has received a notification recently
     */
    protected function hasRecentNotification(Participant $participant): bool
    {
        $recentThreshold = Carbon::now()->subMinutes(30); // Avoid notifications within 30 minutes

        return UserNotification::where('participant_id', $participant->id)
            ->where('created_at', '>=', $recentThreshold)
            ->exists();
    }

    /**
     * Determine notification type based on upcoming task timing
     */
    protected function determineTimeBasedNotificationType(Collection $upcomingTasks): string
    {
        $nextTask = $upcomingTasks->first();
        $now = Carbon::now();

        // Handle time - it's already a Carbon instance due to model casting
        $taskTime = $nextTask->time instanceof Carbon ? $nextTask->time : Carbon::parse($nextTask->time);

        // If we're looking at tomorrow's tasks, adjust the date
        if ($taskTime->format('H:i') < $now->format('H:i')) {
            $taskTime->addDay();
        }

        $hoursUntil = $now->diffInHours($taskTime);

        if ($hoursUntil <= 1) {
            return 'preparation_reminder';
        } elseif ($hoursUntil <= 4) {
            return 'smart_scheduling';
        } elseif ($upcomingTasks->count() >= 3) {
            return 'efficiency_suggestion';
        } else {
            return 'contextual_tip';
        }
    }

    /**
     * Calculate when notification should be delivered locally
     */
    protected function calculateDeliveryTime(DailySchedule $task): Carbon
    {
        // Handle time - it's already a Carbon instance due to model casting
        $taskTime = $task->time instanceof Carbon ? $task->time : Carbon::parse($task->time);
        $now = Carbon::now();

        // If task time has passed today, it's for tomorrow
        if ($taskTime->format('H:i') < $now->format('H:i')) {
            $taskTime->addDay();
        }

        // Deliver notification 30-60 minutes before task time
        $deliveryTime = $taskTime->copy()->subMinutes(rand(30, 60));

        // Don't deliver in the past
        if ($deliveryTime->lt($now)) {
            $deliveryTime = $now->copy()->addMinutes(5);
        }

        return $deliveryTime;
    }

    /**
     * Prepare variables for time-based notification templates
     */
    protected function prepareTimeBasedVariables(Participant $participant, Collection $upcomingTasks, string $notificationType): array
    {
        $nextTask = $upcomingTasks->first();

        // Handle time - it's already a Carbon instance due to model casting
        $taskTime = $nextTask->time instanceof Carbon ? $nextTask->time : Carbon::parse($nextTask->time);
        $now = Carbon::now();

        // If task time is in the past, assume it's for tomorrow
        if ($taskTime->format('H:i') < $now->format('H:i')) {
            $taskTime->addDay();
        }

        // Prepare comprehensive variables for all prompt types
        $taskList = $upcomingTasks->pluck('task')->join(', ');

        return [
            // Basic participant info
            'name' => $participant->name,
            'goal' => $participant->goal->name ?? 'general fitness',

            // Task information (multiple formats for different prompts)
            'next_task' => $nextTask->task,
            'tasks' => $taskList, // Used by efficiency_suggestion
            'upcoming_tasks' => $taskList, // Used by smart_scheduling
            'task_count' => $upcomingTasks->count(),

            // Timing information
            'time_until' => $taskTime->diffForHumans($now),
            'current_time' => $now->format('g:i A'),

            // Location and context
            'location' => $nextTask->location ?? $participant->location ?? 'your area',
            'category' => $nextTask->category ?? 'general',
            'priority' => $nextTask->priority ?? 3,

            // Additional context variables that prompts might use
            'schedule_pattern' => $this->analyzeSchedulePattern($participant),
            'completion_rate' => $this->calculateCompletionRate($participant),
            'recent_activity' => $this->getRecentActivitySummary($participant),
        ];
    }

    /**
     * Generate a notification for a specific participant
     */
    public function generateParticipantNotification(Participant $participant): ?UserNotification
    {
        // Determine notification type based on participant data
        $notificationType = $this->determineNotificationType($participant);
        $template = $this->prompts[$notificationType] ?? null;

        if (! $template) {
            Log::warning("No prompt found for notification type: {$notificationType}");

            return null;
        }

        // Prepare variables for the template
        $variables = $this->prepareTemplateVariables($participant, $notificationType);

        // Generate notification text using AI
        $notificationText = $this->ollama->generateNotification($notificationType, $variables);

        if (! $notificationText) {
            Log::warning("Failed to generate notification text for participant: {$participant->name}");

            return null;
        }

        // Create the notification
        return UserNotification::create([
            'icon' => $this->getNotificationIcon($notificationType),
            'notification_text' => $notificationText,
            'participant_id' => $participant->id,
            'is_read' => false,
            'notification_type' => $notificationType,
        ]);
    }

    /**
     * Generate specific type of notification
     */
    public function generateSpecificNotification(Participant $participant, string $type, array $extraVariables = []): ?UserNotification
    {
        $template = $this->prompts[$type] ?? null;

        if (! $template) {
            Log::warning("No prompt found for notification type: {$type}");

            return null;
        }

        $variables = array_merge(
            $this->prepareTemplateVariables($participant, $type),
            $extraVariables
        );

        $notificationText = $this->ollama->generateNotification($type, $variables);

        if (! $notificationText) {
            return null;
        }

        return UserNotification::create([
            'icon' => $this->getNotificationIcon($type),
            'notification_text' => $notificationText,
            'participant_id' => $participant->id,
            'is_read' => false,
            'notification_type' => $type,
        ]);
    }

    /**
     * Get participants who need notifications
     */
    protected function getParticipantsForNotifications(): Collection
    {
        $batchSize = config('ollama.notifications.batch_size', 10);

        return Participant::with(['goal', 'dailySchedules'])
            ->whereDoesntHave('notifications', function ($query) {
                // Anti-spam: No notifications in last 3 hours
                $query->where('created_at', '>=', Carbon::now()->subHours(3));
            })
            ->limit($batchSize)
            ->get();
    }

    /**
     * Determine what type of notification to send based on context
     */
    protected function determineNotificationType(Participant $participant): string
    {
        $now = Carbon::now();
        $hour = $now->hour;
        $dayOfWeek = strtolower($now->format('l'));

        // Get today's schedule
        $todaySchedule = $participant->dailySchedules()
            ->where('day', $dayOfWeek)
            ->orderBy('time')
            ->get();

        // Check recent notifications to avoid repetition
        $recentNotifications = $participant->notifications()
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->pluck('notification_type')
            ->toArray();

        // Diversify notifications - avoid sending same type repeatedly
        $availableTypes = [
            'schedule_optimization',
            'efficiency_suggestion',
            'wellness_integration',
            'contextual_tip',
            'motivational_boost',
            'preparation_reminder',
            'productivity_insight',
            'energy_management',
            'habit_suggestion',
        ];

        // Check for overdue tasks (but not always prioritize them)
        $overdueTasks = $todaySchedule->filter(function ($schedule) {
            $taskTime = Carbon::parse($schedule->time);

            return $taskTime->isPast() && ! $schedule->is_completed;
        });

        // Only send overdue reminders if:
        // 1. There are tasks that are truly overdue (not just delayed) AND
        // 2. We haven't sent an overdue reminder recently AND
        // 3. The task is actually significantly overdue (4+ hours for true skipping)
        $actuallySkippedTasks = $todaySchedule->filter(function ($schedule) use ($now, $todaySchedule) {
            if ($schedule->is_completed) {
                return false;
            }

            $taskTime = Carbon::parse($schedule->time);
            // Only consider "skipped" if 4+ hours past and no similar task completed
            if ($taskTime->diffInHours($now) < 4) {
                return false;
            }

            // Check if similar category task was done today
            $similarCompleted = $todaySchedule->where('category', $schedule->category)
                ->where('is_completed', true)
                ->where('id', '!=', $schedule->id)
                ->count() > 0;

            return ! $similarCompleted;
        });

        if ($actuallySkippedTasks->count() > 0 && ! in_array('overdue_reminder', $recentNotifications)) {
            return 'overdue_reminder';
        }

        // Check for optimization opportunities (multiple tasks close in time/location)
        $upcomingTasks = $todaySchedule->filter(function ($schedule) use ($now) {
            $taskTime = Carbon::parse($schedule->time);

            return $taskTime->isFuture() && $taskTime->diffInHours($now) <= 3;
        });

        if ($upcomingTasks->count() >= 2 && ! in_array('schedule_optimization', $recentNotifications)) {
            return 'schedule_optimization';
        }

        // Check for preparation needs (important task coming up)
        $nextImportantTask = $todaySchedule->filter(function ($schedule) use ($now) {
            $taskTime = Carbon::parse($schedule->time);

            return $taskTime->isFuture() &&
                   $taskTime->diffInMinutes($now) <= 60 &&
                   $schedule->priority <= 2; // High priority
        })->first();

        if ($nextImportantTask && ! in_array('preparation_reminder', $recentNotifications)) {
            return 'preparation_reminder';
        }

        // Time-based suggestions with variety
        if ($hour >= 6 && $hour <= 10 && ! in_array('efficiency_suggestion', $recentNotifications)) {
            return 'efficiency_suggestion';
        }

        if ($hour >= 12 && $hour <= 16 && ! in_array('wellness_integration', $recentNotifications)) {
            return 'wellness_integration';
        }

        if ($hour >= 17 && $hour <= 20 && ! in_array('motivational_boost', $recentNotifications)) {
            return 'motivational_boost';
        }

        // Default to contextual tip if we haven't sent one recently
        // Or rotate through available types to ensure variety
        $unusedTypes = array_diff($availableTypes, $recentNotifications);

        if (! empty($unusedTypes)) {
            return $unusedTypes[array_rand($unusedTypes)];
        }

        // If all types have been used recently, default to contextual_tip
        return 'contextual_tip';
    }

    /**
     * Prepare comprehensive variables for template substitution
     */
    protected function prepareTemplateVariables(Participant $participant, string $notificationType): array
    {
        $now = Carbon::now();
        $dayOfWeek = strtolower($now->format('l'));

        // Base participant information
        $variables = [
            'name' => $participant->name,
            'goal' => $participant->goal?->name ?? 'general wellness',
            'age' => $participant->dob ? $participant->dob->age : 'unknown',
            'location' => $participant->location ?? 'your area',
            'current_time' => $now->format('g:i A'),
            'education' => $this->inferEducationLevel($participant),
        ];

        // Get today's schedule with completion status
        $todaySchedule = $participant->dailySchedules()
            ->where('day', $dayOfWeek)
            ->orderBy('time')
            ->get();

        // Add type-specific variables
        switch ($notificationType) {
            case 'schedule_optimization':
                $upcomingTasks = $todaySchedule->filter(function ($schedule) use ($now) {
                    $taskTime = Carbon::parse($schedule->time);

                    return $taskTime->isFuture() && $taskTime->diffInHours($now) <= 4;
                });

                $variables['tasks'] = $upcomingTasks->pluck('task')->join(', ');
                $variables['task_locations'] = $upcomingTasks->pluck('location')->filter()->unique()->join(', ');
                break;

            case 'overdue_reminder':
                $overdueTasks = $todaySchedule->filter(function ($schedule) {
                    $taskTime = Carbon::parse($schedule->time);

                    return $taskTime->isPast() && ! $schedule->is_completed;
                });

                $variables['overdue_tasks'] = $overdueTasks->pluck('task')->join(', ');
                $variables['overdue_count'] = $overdueTasks->count();
                break;

            case 'smart_scheduling':
                $pendingTasks = $todaySchedule->where('is_completed', false);
                $variables['upcoming_tasks'] = $pendingTasks->pluck('task')->join(', ');
                $variables['task_count'] = $pendingTasks->count();
                break;

            case 'efficiency_suggestion':
                $allTasks = $todaySchedule->where('is_completed', false);
                $variables['tasks'] = $allTasks->pluck('task')->take(3)->join(', ');
                $variables['task_count'] = $allTasks->count();
                break;

            case 'motivational_boost':
                $completedToday = $todaySchedule->where('is_completed', true)->count();
                $totalToday = $todaySchedule->count();
                $variables['completion_rate'] = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;

                // Weekly completion rate
                $weeklyCompleted = $participant->dailySchedules()
                    ->where('created_at', '>=', $now->startOfWeek())
                    ->where('is_completed', true)
                    ->count();

                $variables['weekly_progress'] = $weeklyCompleted;
                break;

            case 'preparation_reminder':
                $nextTask = $todaySchedule->filter(function ($schedule) {
                    $taskTime = Carbon::parse($schedule->time);

                    return $taskTime->isFuture() && $schedule->priority <= 2;
                })->first();

                if ($nextTask) {
                    $variables['next_task'] = $nextTask->task;
                    $variables['time_until'] = Carbon::parse($nextTask->time)->diffForHumans();
                    $variables['task_location'] = $nextTask->location ?? 'the scheduled location';
                }
                break;

            case 'wellness_integration':
                $busyPeriods = $this->identifyBusyPeriods($todaySchedule);
                $variables['tasks'] = $todaySchedule->where('is_completed', false)->pluck('task')->take(3)->join(', ');
                $variables['busy_periods'] = $busyPeriods;
                break;

            case 'contextual_tip':
                $variables['schedule_pattern'] = $this->analyzeSchedulePattern($participant);
                $variables['recent_activity'] = $this->getRecentActivitySummary($participant);
                break;

            case 'productivity_insight':
                $variables['schedule_pattern'] = $this->analyzeSchedulePattern($participant);
                $variables['task_categories'] = $todaySchedule->pluck('category')->unique()->join(', ');
                break;

            case 'energy_management':
                $variables['recent_activity'] = $this->getRecentActivitySummary($participant);
                $variables['tasks'] = $todaySchedule->where('is_completed', false)->pluck('task')->take(3)->join(', ');
                break;

            case 'habit_suggestion':
                $variables['schedule_pattern'] = $this->analyzeSchedulePattern($participant);
                $variables['consistency_score'] = $this->calculateConsistencyScore($participant);
                break;
        }

        return $variables;
    }

    /**
     * Infer education level from participant data
     */
    protected function inferEducationLevel(Participant $participant): string
    {
        // This could be enhanced with actual education field
        $age = $participant->dob ? $participant->dob->age : 25;

        if ($age < 18) {
            return 'student';
        }
        if ($age >= 18 && $age <= 22) {
            return 'college-level';
        }
        if ($age >= 23 && $age <= 30) {
            return 'early career';
        }
        if ($age >= 31 && $age <= 45) {
            return 'professional';
        }

        return 'experienced professional';
    }

    /**
     * Analyze schedule patterns for personalized insights
     */
    protected function analyzeSchedulePattern(Participant $participant): string
    {
        $schedules = $participant->dailySchedules()->take(20)->get();

        if ($schedules->isEmpty()) {
            return 'getting started with scheduling';
        }

        $categories = $schedules->pluck('category')->filter()->countBy();
        $topCategory = $categories->keys()->first() ?? 'mixed activities';

        $avgTasksPerDay = $schedules->groupBy('day')->avg(function ($daySchedules) {
            return $daySchedules->count();
        });

        if ($avgTasksPerDay > 5) {
            return "busy schedule focused on {$topCategory}";
        } elseif ($avgTasksPerDay > 2) {
            return "balanced routine with emphasis on {$topCategory}";
        } else {
            return "light schedule with {$topCategory} activities";
        }
    }

    /**
     * Get recent activity summary
     */
    protected function getRecentActivitySummary(Participant $participant): string
    {
        $recentCompleted = $participant->dailySchedules()
            ->where('is_completed', true)
            ->where('completed_at', '>=', Carbon::now()->subDays(7))
            ->count();

        if ($recentCompleted > 10) {
            return 'highly active';
        }
        if ($recentCompleted > 5) {
            return 'consistently active';
        }
        if ($recentCompleted > 2) {
            return 'moderately active';
        }

        return 'getting started';
    }

    /**
     * Calculate consistency score for habit formation
     */
    protected function calculateConsistencyScore(Participant $participant): int
    {
        $lastWeek = $participant->dailySchedules()
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->get();

        if ($lastWeek->isEmpty()) {
            return 0;
        }

        $completionRate = $lastWeek->where('is_completed', true)->count() / $lastWeek->count();

        return round($completionRate * 100);
    }

    /**
     * Identify busy periods in the schedule
     */
    protected function identifyBusyPeriods($todaySchedule): string
    {
        $busyHours = [];

        foreach ($todaySchedule as $schedule) {
            $hour = Carbon::parse($schedule->time)->hour;
            $busyHours[] = $hour;
        }

        $busyHours = array_count_values($busyHours);
        $peakHour = array_keys($busyHours, max($busyHours))[0] ?? 12;

        if ($peakHour < 12) {
            return 'busy morning';
        }
        if ($peakHour < 17) {
            return 'busy afternoon';
        }

        return 'busy evening';
    }

    /**
     * Get icon for notification type
     */
    protected function getNotificationIcon(string $type): string
    {
        $icons = [
            'schedule_optimization' => '🧠',
            'overdue_reminder' => '⏰',
            'smart_scheduling' => '📋',
            'contextual_tip' => '💡',
            'efficiency_suggestion' => '⚡',
            'motivational_boost' => '🚀',
            'preparation_reminder' => '🎯',
            'wellness_integration' => '🧘',
            'productivity_insight' => '🔍',
            'energy_management' => '⚖️',
            'habit_suggestion' => '🌱',
            // Legacy icons
            'workout_reminder' => '🏋️',
            'progress_update' => '📈',
            'goal_achievement' => '🎯',
            'wellness_tip' => '💡',
            'schedule_reminder' => '⏰',
        ];

        return $icons[$type] ?? '🔔';
    }

    /**
     * Get statistics about AI notifications
     */
    public function getStatistics(): array
    {
        $today = Carbon::today();

        return [
            'total_ai_notifications' => UserNotification::whereDate('created_at', $today)->count(),
            'participants_notified_today' => UserNotification::whereDate('created_at', $today)
                ->distinct('participant_id')
                ->count(),
            'notification_types' => UserNotification::whereDate('created_at', $today)
                ->select('icon')
                ->groupBy('icon')
                ->selectRaw('icon, count(*) as count')
                ->pluck('count', 'icon')
                ->toArray(),
            'ollama_status' => [
                'available' => $this->ollama->isAvailable(),
                'enabled' => $this->enabled,
            ],
        ];
    }

    /**
     * Calculate participant's completion rate for motivational context
     */
    protected function calculateCompletionRate(Participant $participant): int
    {
        $totalTasks = $participant->dailySchedules()->count();
        $completedTasks = $participant->dailySchedules()->where('is_completed', true)->count();

        if ($totalTasks === 0) {
            return 0;
        }

        return (int) round(($completedTasks / $totalTasks) * 100);
    }
}
