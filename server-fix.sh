#!/bin/bash
# Server Fix Script for Laravel Standard Deployment
# Run this script in your /home/csw/public_html directory

echo "=== Laravel Standard Deployment Fix ==="
echo "Current directory: $(pwd)"

# Check if we're in the right directory
if [ ! -f "artisan" ] || [ ! -d "public" ]; then
    echo "ERROR: This doesn't look like the Laravel root directory!"
    echo "Please run this script from /home/csw/public_html/"
    exit 1
fi

echo "✅ Laravel project detected"

# 1. Set up root .htaccess for document root redirect
echo "Setting up root .htaccess redirect..."
cat > .htaccess << 'EOF'
# Laravel Standard Structure .htaccess - Root Directory
# This file redirects all requests to the public/ directory

<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect everything to public directory unless already there
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteCond %{REQUEST_URI} !^/favicon\.ico$
    RewriteCond %{REQUEST_URI} !^/robots\.txt$
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Security: Block access to sensitive directories
<Files "composer.json">
    Require all denied
</Files>

<Files "composer.lock">
    Require all denied
</Files>

<Files ".env*">
    Require all denied
</Files>

<Files "artisan">
    Require all denied
</Files>

# Block directory browsing for sensitive folders
Options -Indexes
EOF

echo "✅ Root .htaccess created"

# 2. Set proper permissions
echo "Setting file permissions..."
chmod 644 .htaccess
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If vendor doesn't exist, show warning
if [ ! -d "vendor" ]; then
    echo "⚠️  WARNING: vendor/ directory not found!"
    echo "   You need to upload the vendor/ directory or run 'composer install'"
fi

# 3. Check if .env exists
if [ ! -f ".env" ]; then
    echo "⚠️  WARNING: .env file not found!"
    echo "   Create .env file with your production configuration"
else
    echo "✅ .env file found"
fi

# 4. Check if public/index.php exists and is correct
if [ -f "public/index.php" ]; then
    echo "✅ public/index.php exists"
    
    # Check if it has the correct path structure
    if grep -q "\.\./vendor/autoload\.php" public/index.php; then
        echo "✅ public/index.php has correct paths"
    else
        echo "⚠️  public/index.php may have incorrect paths"
    fi
else
    echo "❌ public/index.php is missing!"
    exit 1
fi

echo ""
echo "=== Deployment Status ==="
echo "✅ Root .htaccess redirect configured"
echo "✅ File permissions set"
echo "✅ Security restrictions in place"
echo ""
echo "Next steps:"
echo "1. Ensure .env file is configured for production"
echo "2. Run: php artisan key:generate (if APP_KEY is missing)"
echo "3. Run: php artisan migrate --force"
echo "4. Run: php artisan storage:link"
echo "5. Run: php artisan optimize"
echo ""
echo "Your Laravel app should now work at:"
echo "- https://fitandfocusedacademics.com/ (redirects to public/)"
echo "- https://fitandfocusedacademics.com/public/ (direct access)"
echo ""
echo "If you still have issues, check Apache error logs or contact hosting support."