#!/bin/bash

echo "🔧 Laravel Deployment Troubleshooting Script"
echo "============================================="

# Step 1: Check current directory structure
echo "1. Checking directory structure..."
echo "Contents of /home/csw/public_html:"
ls -la /home/csw/public_html/

# Step 2: Test with minimal .htaccess
echo ""
echo "2. Creating minimal .htaccess..."
cat > /home/csw/public_html/.htaccess << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
EOF

echo "Minimal .htaccess created."

# Step 3: Check if index.php exists and its content
echo ""
echo "3. Checking index.php..."
if [ -f "/home/csw/public_html/index.php" ]; then
    echo "index.php exists. First few lines:"
    head -10 /home/csw/public_html/index.php
else
    echo "❌ index.php missing! Creating basic Laravel index.php..."
    
    # Check if Laravel is in app directory
    if [ -d "/home/csw/app" ]; then
        echo "Found Laravel in /home/csw/app - creating index.php"
        cat > /home/csw/public_html/index.php << 'EOF'
<?php
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture())->send();
$kernel->terminate($request, $response);
EOF
    else
        echo "Laravel not found in /home/csw/app - need to run deployment script first"
        exit 1
    fi
fi

# Step 4: Check file permissions
echo ""
echo "4. Checking file permissions..."
echo "public_html permissions:"
ls -ld /home/csw/public_html/
echo "index.php permissions:"
ls -l /home/csw/public_html/index.php 2>/dev/null || echo "index.php not found"

# Step 5: Test PHP syntax
echo ""
echo "5. Testing PHP syntax..."
if [ -f "/home/csw/public_html/index.php" ]; then
    php -l /home/csw/public_html/index.php
else
    echo "No index.php to test"
fi

# Step 6: Check Laravel structure (if moved to /home/csw/app)
echo ""
echo "6. Checking Laravel application..."
if [ -d "/home/csw/app" ]; then
    echo "Laravel app directory exists at /home/csw/app"
    echo "Contents:"
    ls -la /home/csw/app/ | head -10
    
    if [ -f "/home/csw/app/.env" ]; then
        echo "✅ .env file found"
    else
        echo "❌ .env file missing in /home/csw/app/"
    fi
    
    if [ -f "/home/csw/app/vendor/autoload.php" ]; then
        echo "✅ Composer autoloader found"
    else
        echo "❌ Composer autoloader missing - run 'composer install' in /home/csw/app"
    fi
else
    echo "Laravel app not found at /home/csw/app"
    echo "Current public_html structure suggests Laravel is still here"
    echo "Need to run secure deployment script"
fi

# Step 7: Set basic permissions
echo ""
echo "7. Setting basic permissions..."
chown -R www-data:www-data /home/csw/public_html/
chmod 755 /home/csw/public_html/
if [ -f "/home/csw/public_html/index.php" ]; then
    chmod 644 /home/csw/public_html/index.php
fi

echo ""
echo "✅ Basic troubleshooting completed!"
echo ""
echo "Next steps:"
echo "1. Test your site: https://fitandfocusedacademics.com"
echo "2. If still getting errors, check Apache error logs"
echo "3. If files are still visible, run the secure deployment script"