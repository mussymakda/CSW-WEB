<?php
// Laravel Diagnostic Script
// Upload this as diagnose.php in your public_html directory

echo "<h1>Laravel Deployment Diagnostics</h1>";

// Test 1: PHP Basic Info
echo "<h2>1. PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test 2: File Structure
echo "<h2>2. File Structure Check</h2>";
$required_files = [
    'vendor/autoload.php',
    'bootstrap/app.php',
    'artisan',
    '.env',
    'app',
    'storage',
    'index.php'
];

foreach ($required_files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "$file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "<br>";
}

// Test 3: Permissions
echo "<h2>3. Directory Permissions</h2>";
$dirs_to_check = ['storage', 'bootstrap/cache'];
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? "✅ WRITABLE" : "❌ NOT WRITABLE";
        echo "$dir: $perms ($writable)<br>";
    } else {
        echo "$dir: ❌ MISSING<br>";
    }
}

// Test 4: Composer Autoloader
echo "<h2>4. Composer Autoloader</h2>";
try {
    require __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoloader loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 5: Laravel Bootstrap
echo "<h2>5. Laravel Bootstrap</h2>";
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel app bootstrapped successfully<br>";
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "<br>";
    echo "Error details: " . $e->getFile() . " on line " . $e->getLine() . "<br>";
    exit;
}

// Test 6: Environment File
echo "<h2>6. Environment Configuration</h2>";
if (file_exists('.env')) {
    echo "✅ .env file exists<br>";
    
    // Load environment manually to check for issues
    $env_content = file_get_contents('.env');
    $lines = explode("\n", $env_content);
    $env_vars = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
        }
    }
    
    echo "APP_KEY: " . (isset($env_vars['APP_KEY']) && !empty($env_vars['APP_KEY']) ? "✅ SET" : "❌ NOT SET") . "<br>";
    echo "DB_CONNECTION: " . (isset($env_vars['DB_CONNECTION']) ? $env_vars['DB_CONNECTION'] : "❌ NOT SET") . "<br>";
    echo "APP_ENV: " . (isset($env_vars['APP_ENV']) ? $env_vars['APP_ENV'] : "❌ NOT SET") . "<br>";
} else {
    echo "❌ .env file missing<br>";
}

// Test 7: Database Connection (if we got this far)
echo "<h2>7. Database Connection</h2>";
try {
    // Set up Laravel environment
    $app->make('Illuminate\Contracts\Http\Kernel');
    
    // Test database
    $pdo = new PDO(
        "mysql:host=" . ($env_vars['DB_HOST'] ?? 'localhost') . ";dbname=" . ($env_vars['DB_DATABASE'] ?? ''),
        $env_vars['DB_USERNAME'] ?? '',
        $env_vars['DB_PASSWORD'] ?? ''
    );
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 8: Laravel Request Handling
echo "<h2>8. Laravel Request Test</h2>";
try {
    $request = Illuminate\Http\Request::create('/', 'GET');
    echo "✅ Request object created<br>";
} catch (Exception $e) {
    echo "❌ Request handling failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Diagnosis Complete</h2>";
echo "<p>If all tests pass, the issue might be in your routes or controllers.</p>";
echo "<p>Delete this file after diagnosis: <code>rm diagnose.php</code></p>";
?>