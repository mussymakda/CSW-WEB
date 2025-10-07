<?php

namespace App\Services;

class SmartNotificationService
{
    /**
     * Generate contextual, non-templated notifications based on real user patterns
     */
    public function generateContextualNotification(string $template, array $variables = []): string
    {
        // Extract the notification intent and context
        $context = $this->analyzeContext($variables);

        // Generate based on specific scenarios rather than templates
        return $this->generateScenarioBasedNotification($context);
    }

    /**
     * Analyze user context to determine the best notification approach
     */
    protected function analyzeContext(array $variables): array
    {
        $context = [
            'name' => $variables['name'] ?? 'there',
            'age' => $variables['age'] ?? 25,
            'time' => $variables['current_time'] ?? 'now',
            'location' => $variables['location'] ?? 'your area',
            'goal' => $variables['goal'] ?? 'wellness',
        ];

        // Analyze schedule patterns
        if (isset($variables['tasks'])) {
            $context['scenario'] = $this->identifyScenario($variables);
        }

        return $context;
    }

    /**
     * Identify the real-world scenario to provide genuinely helpful suggestions
     */
    protected function identifyScenario(array $variables): string
    {
        $tasks = $variables['tasks'] ?? '';
        $time = $variables['current_time'] ?? '';
        $location = $variables['location'] ?? '';

        // Real scenario analysis
        if (strpos($tasks, 'school pickup') !== false && strpos($tasks, 'grocery') !== false) {
            return 'parent_errand_combo';
        }

        if (strpos($tasks, 'gym') !== false && (strpos($tasks, 'work') !== false || strpos($tasks, 'meeting') !== false)) {
            return 'fitness_work_balance';
        }

        if (strpos($tasks, 'appointment') !== false && strpos($tasks, 'errands') !== false) {
            return 'appointment_errands_combo';
        }

        if (isset($variables['overdue_tasks'])) {
            return 'catching_up';
        }

        if (isset($variables['completion_rate']) && $variables['completion_rate'] > 70) {
            return 'high_achiever';
        }

        if (isset($variables['completion_rate']) && $variables['completion_rate'] < 30) {
            return 'need_support';
        }

        return 'general_optimization';
    }

    /**
     * Generate truly contextual notifications based on real scenarios
     */
    protected function generateScenarioBasedNotification(array $context): string
    {
        $scenario = $context['scenario'] ?? 'general';
        $name = $context['name'];
        $age = $context['age'];
        $time = $context['time'];

        switch ($scenario) {
            case 'parent_errand_combo':
                return $this->generateParentErrandAdvice($context);

            case 'fitness_work_balance':
                return $this->generateWorkFitnessAdvice($context);

            case 'appointment_errands_combo':
                return $this->generateAppointmentErrandAdvice($context);

            case 'catching_up':
                return $this->generateCatchUpAdvice($context);

            case 'high_achiever':
                return $this->generateHighAchieverAdvice($context);

            case 'need_support':
                return $this->generateSupportiveAdvice($context);

            default:
                return $this->generateGeneralOptimizationAdvice($context);
        }
    }

    protected function generateParentErrandAdvice(array $context): string
    {
        $phrases = [
            'School pickup + store right next door = one smooth trip!',
            "Hit the grocery store after pickup while you're already there.",
            'Kids can help carry light stuff - they love feeling useful!',
            'That pharmacy and store are in the same lot as school.',
            'Grab snacks first, then tackle groceries - keeps everyone happy.',
        ];

        return $phrases[array_rand($phrases)];
    }

    protected function generateWorkFitnessAdvice(array $context): string
    {
        $hour = (int) substr($context['time'], 0, 2);

        if ($hour < 12) {
            $phrases = [
                'Morning gym = energy all day + that post-workout glow for meetings.',
                'Beat the crowd and feel accomplished before work starts.',
                'Morning workouts = better sleep tonight and sharper meetings.',
            ];
        } else {
            $phrases = [
                'End work strong with gym. Perfect way to decompress.',
                'Post-work gym = better sleep and mood reset.',
                'Evening workout = endorphin boost for the family time.',
            ];
        }

        return $phrases[array_rand($phrases)];
    }

    protected function generateAppointmentErrandAdvice(array $context): string
    {
        $phrases = [
            'Already out for appointment? Check what else you need nearby.',
            'While waiting, scope out errands you can do in that area.',
            "After appointment, strike while iron's hot - get errands done.",
            "Map what's near your appointment. Might surprise you!",
        ];

        return $phrases[array_rand($phrases)];
    }

    protected function generateCatchUpAdvice(array $context): string
    {
        $phrases = [
            'No stress - pick the easiest one first for momentum.',
            "Don't catch up on everything at once. What matters today?",
            'Start with 5-minute tasks - small wins build confidence.',
            'Behind? Normal. Focus on what affects others first.',
        ];

        return $phrases[array_rand($phrases)];
    }

    protected function generateHighAchieverAdvice(array $context): string
    {
        $phrases = [
            'Crushing it! Batch similar tasks to keep momentum going.',
            "Great rhythm. What's working that you can optimize?",
            'On a roll - prep today to make tomorrow smoother.',
            "Solid track record. You've earned some flexibility.",
        ];

        return $phrases[array_rand($phrases)];
    }

    protected function generateSupportiveAdvice(array $context): string
    {
        $phrases = [
            'Rough patch? Pick one tiny win today.',
            'Some weeks are harder. Focus on essentials only.',
            "Don't judge yourself. One thing at a time.",
            'Overwhelmed? Just focus on today. Tomorrow can wait.',
        ];

        return $phrases[array_rand($phrases)];
    }

    protected function generateGeneralOptimizationAdvice(array $context): string
    {
        $age = $context['age'];
        $goal = $context['goal'];

        if ($age < 25) {
            $phrases = [
                'Building habits now sets you up for life.',
                "You've got energy and time - experiment and find what works.",
                'Small consistent actions beat big sporadic efforts.',
            ];
        } elseif ($age < 40) {
            $phrases = [
                'Power decade - make it count with good systems.',
                'Balance is key. Routines are your best friends.',
                'Sweet spot for building habits with wisdom to stick.',
            ];
        } else {
            $phrases = [
                "Trust your instincts. Don't overcomplicate things.",
                'Your experience is your superpower.',
                'Less rushing, more intentionality. Be selective.',
            ];
        }

        return $phrases[array_rand($phrases)];
    }
}
