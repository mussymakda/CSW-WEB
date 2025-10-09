# Server Index.php Fix

The issue is that your current `index.php` is looking for Laravel files outside the web directory with `../` paths, but everything is actually in the same `public_html` directory.

## Current Problem:
Your `index.php` has:
```php
require __DIR__.'/../vendor/autoload.php';     // Looking OUTSIDE public_html
require_once __DIR__.'/../bootstrap/app.php';  // Looking OUTSIDE public_html
```

But your files are actually IN `public_html`:
```
public_html/
├── vendor/     ← HERE, not outside
├── bootstrap/  ← HERE, not outside
└── storage/    ← HERE, not outside
```

## Fix on Your Server:

### 1. Replace index.php
Replace your current `index.php` with this corrected version:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

### 2. Server Commands:
```bash
# On your server
cd /home/csw/public_html

# Backup current index.php
cp index.php index.php.backup

# Create the corrected index.php (copy content above)
# Then set permissions
chmod 644 index.php

# Test Laravel
php artisan --version

# Test the site
curl -I https://fitandfocusedacademics.com
```

### Key Changes:
- Changed `__DIR__.'/../vendor/autoload.php'` → `__DIR__.'/vendor/autoload.php'`
- Changed `__DIR__.'/../bootstrap/app.php'` → `__DIR__.'/bootstrap/app.php'`  
- Changed `__DIR__.'/../storage/framework/'` → `__DIR__.'/storage/framework/'`

This tells Laravel to look for files in the SAME directory instead of going up one level.

## Directory Structure Should Be:
```
/public_html/
├── index.php          ← Fixed paths (no ../)
├── .htaccess          ← Fixed (no DirectoryMatch)  
├── vendor/            ← Laravel dependencies
├── bootstrap/         ← Laravel bootstrap
├── storage/           ← Laravel storage
├── app/              ← Laravel app code
├── config/           ← Laravel config
└── .env              ← Environment config
```

After this fix, your Laravel application should load properly instead of showing the directory listing!