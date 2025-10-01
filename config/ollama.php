<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Ollama LLM integration used for
    | generating AI-powered notifications and content.
    |
    */

    'enabled' => true,

    'host' => env('OLLAMA_HOST', 'http://localhost:11434'),

    // Fixed model optimized for clean, direct responses
    'model' => 'llama3.2:3b',

    'timeout' => 60,

    'max_tokens' => 200,

    'temperature' => 0.8,

    /*
    |--------------------------------------------------------------------------
    | AI Notifications Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for AI-generated notifications functionality.
    |
    */

    'notifications' => [
        'enabled' => true, // Always enabled
        'interval' => env('AI_NOTIFICATION_INTERVAL', 'hourly'),
        'batch_size' => env('AI_NOTIFICATION_BATCH_SIZE', 10),
        'lookahead_hours' => env('AI_NOTIFICATION_LOOKAHEAD_HOURS', 24),

        // Dynamic AI generation - no templates, pure contextual intelligence
        'generation_style' => [
            'tone' => 'helpful friend who notices patterns',
            'approach' => 'practical insights, not motivational fluff',
            'length' => 'brief and punchy - like a quick helpful text',
            'avoid' => 'generic advice, obvious suggestions, cheerleader language, long explanations, thinking out loud, reasoning steps',
            'focus' => 'one specific actionable insight',
            'response_format' => 'direct notification text only - no thinking, reasoning, or explanation of process',
        ],

        // Context-rich prompts for dynamic generation
        'prompts' => [
            'schedule_optimization' => 'You\'re looking at {name}\'s schedule like a helpful friend who notices timing patterns. They have {tasks} coming up and their goal is {goal}. Spot something practical about timing, location, or flow that could genuinely save time or reduce stress. Be specific and actionable.',

            'overdue_reminder' => 'Be understanding - {name} has some tasks that didn\'t happen as planned: {overdue_tasks}. Don\'t be preachy. Just acknowledge the reality and suggest one practical way to adapt or reschedule. Consider their {goal} goal and current situation.',

            'smart_scheduling' => 'Help {name} think through their {upcoming_tasks} like you\'re planning together. Consider their {goal}, energy levels, and logistics in {location}. Share one insight that would actually make their day flow better.',

            'contextual_tip' => 'Based on {name}\'s patterns ({schedule_pattern}) and their goal of {goal}, you\'ve noticed something that could genuinely improve their routine. Focus on practical habits, time management, or small optimizations that fit their actual lifestyle.',

            'efficiency_suggestion' => '{name} has {task_count} things happening: {tasks}. Looking at this like someone who understands busy schedules, what timing, batching, or location strategy could make this smoother? Think practically about energy and logistics.',

            'motivational_boost' => 'Give {name} honest recognition for their {completion_rate}% completion rate. Their goal is {goal}. Be genuine about what they\'re doing well and mention one thing about their progress that\'s worth acknowledging. No fake cheerleading.',

            'preparation_reminder' => '{name} has {next_task} {time_until}. Think like someone who helps with planning - what might they actually need to remember, prepare, or consider? Be helpful about the practical details that matter.',

            'wellness_integration' => '{name} is managing {tasks} while working toward {goal}. Looking at their actual schedule, where could wellness naturally fit without adding stress? Suggest something realistic that works with their pace.',

            // Add variety with new notification types
            'productivity_insight' => 'Looking at how {name} structures their day, you notice a pattern that could work even better. They\'re working toward {goal} and tend to {schedule_pattern}. What\'s one adjustment that would genuinely improve their productivity?',

            'energy_management' => '{name} has been {recent_activity} lately. Considering their {goal} and today\'s schedule of {tasks}, what\'s one insight about managing energy that fits their actual routine?',

            'habit_suggestion' => 'Based on {name}\'s {schedule_pattern} and their {goal}, there\'s a small habit that could compound over time. What\'s something simple they could add or adjust that would actually stick?',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Generation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for various AI content generation features.
    |
    */

    'content' => [
        'max_length' => 280, // Maximum characters for notifications - increased for full messages
        'include_emoji' => true,
        'tone' => 'encouraging', // encouraging, professional, casual, motivational
    ],

];
