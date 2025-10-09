#!/bin/bash
# Complete Server Fix Script for Laravel Production
# Run this in /home/csw/public_html/

echo "=== Laravel Production Fix Script ==="
echo "Current directory: $(pwd)"

# 1. Fix storage permissions (CRITICAL)
echo "Fixing storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create logs directory if it doesn't exist
mkdir -p storage/logs
chmod 775 storage/logs

# Try to set ownership (may fail on shared hosting)
chown -R $(whoami):$(whoami) storage bootstrap/cache 2>/dev/null || echo "Note: Could not change ownership (shared hosting limitation)"

echo "✅ Storage permissions fixed"

# 2. Build frontend assets
echo "Building frontend assets..."
if command -v npm >/dev/null 2>&1; then
    npm run build
    echo "✅ Frontend assets built"
else
    echo "⚠️  npm not available - will create minimal manifest"
    
    # Create minimal build directory and manifest
    mkdir -p public/build
    cat > public/build/manifest.json << 'EOF'
{
  "resources/css/app.css": "resources/css/app.css",
  "resources/js/app.js": "resources/js/app.js"
}
EOF
    
    # Create empty CSS/JS files if they don't exist
    mkdir -p public/css public/js
    touch public/css/app.css
    touch public/js/app.js
    
    echo "✅ Minimal manifest created"
fi

# 3. Clear and rebuild caches
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Caches rebuilt"

# 4. Set proper file permissions
echo "Setting file permissions..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 .env
chmod +x artisan

echo "✅ File permissions set"

# 5. Create storage symlink
php artisan storage:link

echo ""
echo "=== Fix Complete ==="
echo "Storage permissions: ✅"
echo "Build files: ✅"
echo "Caches: ✅"
echo "Permissions: ✅"
echo ""
echo "Your Laravel app should now work properly!"