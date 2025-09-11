<?php
/**
 * Ollama Model Management Script
 * This script helps manage Ollama models for the CSW application
 */

echo "🤖 Ollama Model Management for CSW\n";
echo "================================\n\n";

// Configuration
$ollamaHost = 'http://localhost:11434';
$preferredModels = [
    'llama3.2',
    'llama3.1', 
    'llama3',
    'phi3',
    'gemma2',
    'qwen2'
];

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return false;
    }
    
    return json_decode($response, true);
}

// Check Ollama connection
echo "📡 Checking Ollama connection...\n";
$tags = makeRequest("$ollamaHost/api/tags");

if (!$tags) {
    echo "❌ Cannot connect to Ollama at $ollamaHost\n";
    echo "Please ensure Ollama is running.\n";
    exit(1);
}

echo "✅ Connected to Ollama successfully!\n\n";

// List available models
echo "📋 Available models:\n";
if (empty($tags['models'])) {
    echo "❌ No models found.\n";
} else {
    foreach ($tags['models'] as $model) {
        echo "  ✅ {$model['name']}\n";
    }
}

echo "\n";

// Check if any preferred models are available
$availableModels = array_column($tags['models'], 'name');
$foundModel = null;

foreach ($preferredModels as $model) {
    if (in_array($model, $availableModels)) {
        $foundModel = $model;
        break;
    }
}

if ($foundModel) {
    echo "🎯 Found suitable model: $foundModel\n";
    echo "💡 Update your .env file:\n";
    echo "OLLAMA_MODEL=$foundModel\n\n";
} else {
    echo "⚠️  No preferred models found.\n";
    echo "💡 Consider pulling one of these models:\n";
    foreach ($preferredModels as $model) {
        echo "  - $model\n";
    }
    echo "\n";
    
    if (!empty($availableModels)) {
        echo "🔧 Or update your .env to use an available model:\n";
        foreach ($availableModels as $model) {
            echo "OLLAMA_MODEL=$model\n";
        }
        echo "\n";
    }
}

// Test generation with available model
if (!empty($availableModels)) {
    $testModel = $foundModel ?: $availableModels[0];
    echo "🧪 Testing text generation with model: $testModel\n";
    
    $testData = [
        'model' => $testModel,
        'prompt' => 'Generate a short motivational message for a fitness app user. Keep it under 50 words.',
        'stream' => false
    ];
    
    echo "⏳ Generating test text...\n";
    $result = makeRequest("$ollamaHost/api/generate", 'POST', $testData);
    
    if ($result && isset($result['response'])) {
        echo "✅ Test generation successful!\n";
        echo "📝 Generated text: " . trim($result['response']) . "\n\n";
        echo "🎉 Your AI notification system is ready to use!\n";
    } else {
        echo "❌ Test generation failed.\n";
        echo "🔍 Check if the model is fully loaded.\n";
    }
} else {
    echo "❌ No models available for testing.\n";
}

echo "\n📚 Quick commands:\n";
echo "  Test connection: php artisan notifications:generate-ai --test\n";
echo "  Generate notifications: php artisan notifications:generate-ai\n";
echo "  Admin panel: http://localhost:8000/admin (AI Configuration page)\n";
