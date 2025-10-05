#!/bin/bash

# EMERGENCY: Fix server security by properly structuring Laravel deployment
# This script will secure your Laravel application immediately

echo "🚨 EMERGENCY: Securing Laravel application..."

# 1. Create proper directory structure
mkdir -p /home/csw/laravel
mkdir -p /home/csw/public_html_backup

# 2. Backup current public_html
echo "Backing up current files..."
cp -r /home/csw/public_html/* /home/csw/public_html_backup/

# 3. Move Laravel app to secure location (outside web root)
echo "Moving Laravel application to secure location..."
mv /home/csw/public_html/* /home/csw/laravel/

# 4. Copy only Laravel's public folder contents to public_html
echo "Setting up public directory..."
cp -r /home/csw/laravel/public/* /home/csw/public_html/

# 5. Update index.php to point to correct Laravel location
cat > /home/csw/public_html/index.php << 'EOF'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/../laravel/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

# 6. Create secure .htaccess for public_html
cat > /home/csw/public_html/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Deny access to sensitive files
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
EOF

# 7. Set proper permissions
chown -R csw:csw /home/csw/laravel
chown -R www-data:www-data /home/csw/public_html
chmod -R 755 /home/csw/public_html
chmod -R 775 /home/csw/laravel/storage
chmod -R 775 /home/csw/laravel/bootstrap/cache

# 8. Create storage symlink
ln -sf /home/csw/laravel/storage/app/public /home/csw/public_html/storage

echo "✅ Security fix completed!"
echo "Laravel app moved to: /home/csw/laravel"
echo "Public files in: /home/csw/public_html"
echo "Backup created in: /home/csw/public_html_backup"