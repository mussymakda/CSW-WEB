<?php
// Simple diagnostic script to check server setup
echo "<h1>Server Diagnostic</h1>";

echo "<h2>Current Directory:</h2>";
echo "<p>" . __DIR__ . "</p>";

echo "<h2>Files in Current Directory:</h2>";
$files = scandir(__DIR__);
echo "<ul>";
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "<li>$file" . (is_dir($file) ? " (directory)" : " (file)") . "</li>";
    }
}
echo "</ul>";

echo "<h2>Check for Laravel Files:</h2>";
$laravelFiles = ['artisan', 'composer.json', 'bootstrap/app.php', 'vendor/autoload.php'];
foreach ($laravelFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<p>$file: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

echo "<h2>Current index.php Content:</h2>";
if (file_exists(__DIR__ . '/index.php')) {
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/index.php')) . "</pre>";
} else {
    echo "<p>❌ index.php does not exist</p>";
}

echo "<h2>PHP Info:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
?>