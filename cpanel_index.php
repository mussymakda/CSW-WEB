<?php

/*
|--------------------------------------------------------------------------
| cPanel Index File for Laravel
|--------------------------------------------------------------------------
| This file serves as the entry point for Laravel applications hosted
| on cPanel shared hosting where the Laravel app is located outside
| the public_html directory for security.
|
| Laravel App Location: /home/csw/app/
| Public Directory: /home/csw/public_html/
|
*/

// Define the path to the Laravel application
define('LARAVEL_APP_PATH', '/home/csw/app');

// Check if Laravel application exists
if (!file_exists(LARAVEL_APP_PATH . '/bootstrap/app.php')) {
    die('Laravel application not found. Please ensure the app is uploaded to: ' . LARAVEL_APP_PATH);
}

// Register the auto loader
require_once LARAVEL_APP_PATH . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once LARAVEL_APP_PATH . '/bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);