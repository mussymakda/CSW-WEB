<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';

// Test Ollama Service
$ollama = $app->make(App\Services\OllamaService::class);

echo "=== Ollama Service Test ===" . PHP_EOL;
echo "Ollama Available: " . ($ollama->isAvailable() ? 'YES' : 'NO') . PHP_EOL;

if ($ollama->isAvailable()) {
    echo "Testing text generation..." . PHP_EOL;
    $response = $ollama->generateText("Hello! Please respond with just 'AI is working'");
    echo "Response: " . ($response ?? 'No response') . PHP_EOL;
} else {
    echo "Ollama service is not available or not enabled." . PHP_EOL;
}

echo "=== Test Complete ===" . PHP_EOL;