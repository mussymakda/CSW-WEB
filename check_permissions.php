<?php

function checkPermissions($path, $relativePath = '') {
    $webServerUser = posix_getpwuid(fileowner($path))['name'] ?? 'unknown';
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    
    echo "\n" . str_repeat('-', 80);
    echo "\nChecking: " . ($relativePath ?: '/');
    echo "\nAbsolute Path: $path";
    echo "\nPermissions: $perms";
    echo "\nOwner: $webServerUser";
    echo "\nWritable: " . (is_writable($path) ? 'Yes' : 'No');
    
    if (is_dir($path)) {
        try {
            $testFile = $path . '/.permissions_test_' . uniqid();
            $canWrite = @file_put_contents($testFile, 'test');
            if ($canWrite !== false) {
                unlink($testFile);
                echo "\nDirectory is writable (test file created successfully)";
            } else {
                echo "\nWARNING: Cannot write to directory!";
            }
        } catch (Exception $e) {
            echo "\nERROR testing write permissions: " . $e->getMessage();
        }
    }
}

// Critical paths to check
$pathsToCheck = [
    '.',
    'storage',
    'storage/app',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

echo "Laravel Permissions Diagnostic Tool\n";
echo "=================================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Web Server User: " . php_uname('n') . "\n";
echo "Current Working Directory: " . getcwd() . "\n";

foreach ($pathsToCheck as $path) {
    $fullPath = __DIR__ . '/' . $path;
    if (file_exists($fullPath)) {
        checkPermissions($fullPath, $path);
    } else {
        echo "\n\nWARNING: Path does not exist: $path";
    }
}

// Check .env file
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "\n\nChecking .env file:";
    checkPermissions($envPath, '.env');
}

echo "\n\nDone checking permissions.\n";