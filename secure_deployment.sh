#!/bin/bash

# Secure Laravel Deployment Script
# This will properly structure your Laravel application for production

echo "🔒 Securing Laravel Application..."

# Step 1: Create secure directory structure
echo "Creating secure directory structure..."
mkdir -p /home/csw/app
mkdir -p /home/csw/backup

# Step 2: Backup current setup
echo "Backing up current setup..."
cp -r /home/csw/public_html /home/csw/backup/public_html_$(date +%Y%m%d_%H%M%S)

# Step 3: Move Laravel application to secure location
echo "Moving Laravel application to secure location..."
cd /home/csw/public_html

# Move Laravel core files (everything except public folder contents)
mv app bootstrap config database resources routes storage tests vendor artisan composer.* package.* .env* *.md /home/csw/app/ 2>/dev/null

# Step 4: Clear public_html and copy only public folder contents
echo "Setting up public directory..."
# Keep only what should be in public
find /home/csw/public_html -mindepth 1 -maxdepth 1 ! -name 'public' -exec rm -rf {} + 2>/dev/null

# If public folder exists, move its contents up
if [ -d "/home/csw/public_html/public" ]; then
    mv /home/csw/public_html/public/* /home/csw/public_html/ 2>/dev/null
    rmdir /home/csw/public_html/public 2>/dev/null
fi

# If no index.php, copy from app/public
if [ ! -f "/home/csw/public_html/index.php" ] && [ -d "/home/csw/app/public" ]; then
    cp -r /home/csw/app/public/* /home/csw/public_html/
fi

# Step 5: Update index.php to point to secure Laravel location
echo "Updating index.php..."
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

if (file_exists($maintenance = __DIR__.'/../app/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/../app/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.'/../app/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

# Step 6: Create secure .htaccess
echo "Creating secure .htaccess..."
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

<Files "composer.json">
    Require all denied
</Files>

<Files "composer.lock">
    Require all denied
</Files>

<Files ".env">
    Require all denied
</Files>
EOF

# Step 7: Set proper permissions
echo "Setting permissions..."
chown -R csw:csw /home/csw/app
chown -R www-data:www-data /home/csw/public_html
chmod -R 755 /home/csw/public_html
chmod -R 775 /home/csw/app/storage /home/csw/app/bootstrap/cache

# Step 8: Create storage symlink
echo "Creating storage symlink..."
rm -f /home/csw/public_html/storage
ln -sf /home/csw/app/storage/app/public /home/csw/public_html/storage

# Step 9: Update .env file location if needed
if [ -f "/home/csw/app/.env" ]; then
    echo "Found .env file in secure location"
else
    echo "Warning: No .env file found. You may need to create one in /home/csw/app/"
fi

echo "✅ Deployment completed!"
echo ""
echo "📁 Directory structure:"
echo "   /home/csw/app/           - Laravel application (secure)"
echo "   /home/csw/public_html/   - Public files only (web accessible)"
echo "   /home/csw/backup/        - Backup of original files"
echo ""
echo "🔗 Test your site now: https://fitandfocusedacademics.com"
echo ""
echo "If you see errors, check:"
echo "1. Database configuration in /home/csw/app/.env"
echo "2. File permissions"
echo "3. Laravel logs in /home/csw/app/storage/logs/"