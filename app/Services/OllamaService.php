<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OllamaService
{
    protected string $host;
    protected string $model;
    protected int $timeout;
    protected int $maxTokens;
    protected float $temperature;
    protected bool $enabled;

    public function __construct()
    {
        $this->host = config('ollama.host');
        $this->model = config('ollama.model');
        $this->timeout = config('ollama.timeout');
        $this->maxTokens = config('ollama.max_tokens');
        $this->temperature = config('ollama.temperature');
        $this->enabled = config('ollama.enabled');
    }

    /**
     * Check if Ollama service is available
     */
    public function isAvailable(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout(5)->get($this->host . '/api/tags');
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('Ollama service not available: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate text using Ollama
     */
    public function generate(string $prompt, array $options = []): ?string
    {
        if (!$this->isAvailable()) {
            Log::warning('Ollama service not available for text generation');
            return null;
        }

        try {
            $payload = [
                'model' => $options['model'] ?? $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => $options['temperature'] ?? $this->temperature,
                    'num_predict' => $options['max_tokens'] ?? $this->maxTokens,
                ]
            ];

            $response = Http::timeout($this->timeout)
                ->post($this->host . '/api/generate', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['response'] ?? null;
            }

            Log::error('Ollama generation failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Ollama generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a notification message with dynamic context
     */
    public function generateNotification(string $notificationType, array $variables = []): ?string
    {
        $prompts = config('ollama.notifications.prompts', []);
        $prompt = $prompts[$notificationType] ?? null;
        
        if (!$prompt) {
            Log::warning("No prompt found for notification type: {$notificationType}");
            return null;
        }

        $contextualPrompt = $this->buildContextualPrompt($prompt, $variables);
        $options = [
            'temperature' => 0.9, // Higher creativity for unique responses
            'max_tokens' => config('ollama.content.max_length', 280), // Use updated max length
        ];

        $response = $this->generate($contextualPrompt, $options);

        if ($response) {
            return $this->cleanNotificationText($response);
        }

        // Fallback to smart contextual service instead of templates
        Log::info('Falling back to smart notification generation');
        $smartService = new \App\Services\SmartNotificationService();
        return $smartService->generateContextualNotification($notificationType, $variables);
    }

    /**
     * Build a rich, contextual prompt for unique generation
     */
    protected function buildContextualPrompt(string $basePrompt, array $variables): string
    {
        // Replace variables in the base prompt
        $prompt = $basePrompt;
        foreach ($variables as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }

        // Add generation guidelines
        $style = config('ollama.notifications.generation_style', []);

        $systemPrompt = "You are a perceptive personal assistant who notices useful patterns. ";
        $systemPrompt .= "Write like a " . ($style['tone'] ?? 'helpful friend') . ". ";
        $systemPrompt .= "Be " . ($style['approach'] ?? 'practical and specific') . ". ";
        $systemPrompt .= "Keep it " . ($style['length'] ?? 'concise but detailed') . ". ";

        if (isset($style['avoid'])) {
            $systemPrompt .= "Avoid: " . $style['avoid'] . ". ";
        }

        if (isset($style['focus'])) {
            $systemPrompt .= "Focus on: " . $style['focus'] . ". ";
        }

        $systemPrompt .= "Generate a unique, personalized notification that feels natural and helpful. Here's the context: " . $prompt;

        return $systemPrompt;
    }

    /**
     * Build prompt from template and variables (legacy support)
     */
    protected function buildPrompt(string $template, array $variables): string
    {
        $prompt = $template;
        
        foreach ($variables as $key => $value) {
            $prompt = str_replace('{' . $key . '}', $value, $prompt);
        }

        // Add system instructions
        $systemPrompt = "You are a helpful AI assistant for a wellness and fitness application. ";
        $systemPrompt .= "Generate a short, encouraging, and personalized notification message. ";
        $systemPrompt .= "Keep it under " . config('ollama.content.max_length', 280) . " characters. ";
        
        if (config('ollama.content.include_emoji', true)) {
            $systemPrompt .= "Include relevant emojis. ";
        }
        
        $systemPrompt .= "Tone should be " . config('ollama.content.tone', 'encouraging') . ". ";
        $systemPrompt .= "Here's the request: " . $prompt;

        return $systemPrompt;
    }

    /**
     * Clean and format notification text
     */
    protected function cleanNotificationText(string $text): string
    {
        // Remove common AI response prefixes
        $text = preg_replace('/^(Here\'s|Here is|I\'d suggest|I would suggest|Sure,?\s*)/i', '', $text);
        
        // Trim whitespace
        $text = trim($text);
        
        // Ensure it's not too long
        $maxLength = config('ollama.content.max_length', 280);
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return $text;
    }

    /**
     * Get available models from Ollama
     */
    public function getAvailableModels(): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        try {
            $response = Http::timeout(10)->get($this->host . '/api/tags');
            
            if ($response->successful()) {
                $data = $response->json();
                return collect($data['models'] ?? [])
                    ->pluck('name')
                    ->toArray();
            }
        } catch (Exception $e) {
            Log::error('Failed to get available models: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Test the connection and model
     */
    public function test(): array
    {
        $result = [
            'connection' => false,
            'model_available' => false,
            'test_generation' => false,
            'models' => [],
            'error' => null
        ];
        try {
            // Test connection
            $result['connection'] = $this->isAvailable();
            
            if (!$result['connection']) {
                $result['error'] = 'Cannot connect to Ollama server at ' . $this->host;
                return $result;
            }

            // Get available models
            $result['models'] = $this->getAvailableModels();
            $result['model_available'] = in_array($this->model, $result['models']);

            if (!$result['model_available']) {
                $result['error'] = "Model '{$this->model}' is not available. Available models: " . implode(', ', $result['models']);
                return $result;
            }

            // Test generation
            $testResponse = $this->generate('Say "Hello, CSW!" in a friendly way.');
            $result['test_generation'] = !empty($testResponse);
            
            if (!$result['test_generation']) {
                $result['error'] = 'Model is available but failed to generate text';
            }

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
