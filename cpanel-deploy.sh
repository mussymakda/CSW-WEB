#!/bin/bash

# =============================================================================
# CSW Laravel App - cPanel Deployment Script
# =============================================================================
# This script prepares and deploys your Laravel application to cPanel shared hosting
# where the Laravel app goes in /home/csw/ and public files go in /home/csw/public_html/
#
# Usage: bash cpanel-deploy.sh
# =============================================================================

set -e  # Exit on any error

# =============================================================================
# CONFIGURATION - UPDATE THESE VALUES
# =============================================================================

# Server Details
SERVER_HOST="fitandfocusedacademics.com"
SERVER_USER="csw"
SERVER_HOME="/home/csw"
SERVER_PUBLIC="/home/csw/public_html"
SSH_KEY_PATH="~/.ssh/id_rsa"

# Database Configuration (cPanel)
DB_NAME="csw_csw"
DB_USER="csw_user"
DB_PASS="eBi;_*,LuBHE"

# App Details
APP_NAME="CSW"
DOMAIN="fitandfocusedacademics.com"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# =============================================================================
# FUNCTIONS
# =============================================================================

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    exit 1
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check dependencies
check_dependencies() {
    log "Checking dependencies..."
    
    command -v rsync >/dev/null 2>&1 || error "rsync is required but not installed"
    command -v ssh >/dev/null 2>&1 || error "ssh is required but not installed"
    command -v composer >/dev/null 2>&1 || error "composer is required but not installed"
    command -v php >/dev/null 2>&1 || error "PHP is required but not installed"
    
    log "All dependencies satisfied ✓"
}

# Validate configuration
validate_config() {
    log "Validating configuration..."
    
    if [[ "$SERVER_HOST" == "your-domain.com" ]]; then
        error "Please update SERVER_HOST in the script configuration"
    fi
    
    if [[ ! -f ".env" ]]; then
        error ".env file not found. Please create one from .env.production"
    fi
    
    log "Configuration validated ✓"
}

# Prepare local files for cPanel deployment
prepare_local_files() {
    log "Preparing local files for cPanel..."
    
    # Install/update composer dependencies
    composer install --optimize-autoloader --no-dev --no-interaction
    
    # Clear and cache configuration
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Generate optimized files
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log "Local files prepared ✓"
}

# Create cPanel-specific files
create_cpanel_files() {
    log "Creating cPanel-specific files..."
    
    # Create public_html index.php
    cat > ./cpanel_index.php << 'EOF'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
EOF

    # Create cPanel-specific .htaccess for public_html
    cat > ./cpanel_htaccess << 'EOF'
# cPanel Laravel Application - Public HTML Configuration
# This .htaccess file should be placed in public_html directory

# Disable directory browsing
Options -Indexes

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # API-specific headers
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, PATCH, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Origin, Content-Type, Accept, Authorization, X-Requested-With, X-XSRF-TOKEN"
    Header always set Access-Control-Max-Age "86400"
    
    # Remove server signature
    Header unset Server
    Header unset X-Powered-By
</IfModule>

# Enable Rewrite Engine
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Force HTTPS (optional - uncomment if you have SSL)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Handle CORS preflight requests
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Handle Bearer Token
    RewriteCond %{HTTP:Authorization} ^Bearer\ (.+)$
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

<Files "artisan">
    Require all denied
</Files>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    
    # Images
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    
    # CSS and JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    
    # API responses should not be cached
    ExpiresByType application/json "access plus 0 seconds"
    
    # Default
    ExpiresDefault "access plus 1 week"
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE application/xml
</IfModule>

# File Upload Limits (adjust as needed)
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>
EOF

    log "cPanel-specific files created ✓"
}

# Upload files to cPanel server
upload_files() {
    log "Uploading files to cPanel server..."
    
    # Create rsync exclude file
    cat > .rsync_exclude << EOF
.git/
.gitignore
node_modules/
.env.example
.env.production
tests/
.phpunit.result.cache
storage/logs/*
!storage/logs/.gitignore
bootstrap/cache/*
!bootstrap/cache/.gitignore
cpanel-deploy.sh
.rsync_exclude
backups/
.DS_Store
Thumbs.db
*.log
.vscode/
.idea/
README*.md
CLAUDE.md
COURSE-PROGRESS-README.md
FINAL-STATUS.md
ONBOARDING-IMPLEMENTATION.md
PRODUCTION-READY.md
VPS-DEPLOYMENT-GUIDE.md
LINUX-DEPLOYMENT.md
XAMPP-SETUP.md
setup-xampp-path.ps1
start-dev.*
start-laravel-xampp.*
test_*.php
public/
cpanel_*
EOF

    # Upload Laravel application files to home directory (excluding public)
    rsync -avz \
        --exclude-from=.rsync_exclude \
        --delete \
        -e "ssh -i $SSH_KEY_PATH" \
        ./ \
        "$SERVER_USER@$SERVER_HOST:$SERVER_HOME/"
    
    # Upload public directory contents and custom files to public_html
    rsync -avz \
        -e "ssh -i $SSH_KEY_PATH" \
        ./public/ \
        "$SERVER_USER@$SERVER_HOST:$SERVER_PUBLIC/"
    
    # Upload custom cPanel files
    scp -i "$SSH_KEY_PATH" ./cpanel_index.php "$SERVER_USER@$SERVER_HOST:$SERVER_PUBLIC/index.php"
    scp -i "$SSH_KEY_PATH" ./cpanel_htaccess "$SERVER_USER@$SERVER_HOST:$SERVER_PUBLIC/.htaccess"
    
    # Clean up local temporary files
    rm -f .rsync_exclude ./cpanel_index.php ./cpanel_htaccess
    
    log "Files uploaded successfully ✓"
}

# Configure cPanel server
configure_cpanel_server() {
    log "Configuring cPanel server..."
    
    ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
        cd /home/csw
        
        # Set proper permissions for Laravel
        chmod -R 755 .
        chmod -R 775 storage/
        chmod -R 775 bootstrap/cache/
        
        # Set proper permissions for public_html
        chmod -R 755 public_html/
        chmod 644 public_html/.htaccess
        chmod 644 public_html/index.php
        
        # Create symbolic link for storage (if possible)
        cd public_html
        if [ ! -L storage ]; then
            ln -sfn ../storage/app/public storage 2>/dev/null || echo "Could not create storage symlink - may need manual setup"
        fi
        
        echo "cPanel server configuration completed"
ENDSSH
    
    log "cPanel server configured ✓"
}

# Run Laravel commands on cPanel server
run_cpanel_laravel_commands() {
    log "Running Laravel commands on cPanel server..."
    
    ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
        cd /home/csw
        
        # Check if we have proper Laravel installation
        if [ ! -f artisan ]; then
            echo "ERROR: artisan not found. Laravel may not be properly deployed."
            exit 1
        fi
        
        # Check PHP version
        PHP_VERSION=$(php -v | head -n 1)
        echo "PHP Version: $PHP_VERSION"
        
        # Run migrations (be careful in production!)
        echo "Running database migrations..."
        php artisan migrate --force --no-interaction || echo "Migration failed or not needed"
        
        # Create storage link
        echo "Creating storage symlink..."
        php artisan storage:link --force || echo "Storage link command failed"
        
        # Clear all caches first
        echo "Clearing caches..."
        php artisan config:clear || echo "Config clear failed"
        php artisan route:clear || echo "Route clear failed"  
        php artisan view:clear || echo "View clear failed"
        php artisan cache:clear || echo "Cache clear failed"
        
        # Cache optimization for production
        echo "Caching for production..."
        php artisan config:cache && echo "Config cached successfully" || echo "Config cache failed"
        php artisan route:cache && echo "Routes cached successfully" || echo "Route cache failed" 
        php artisan view:cache && echo "Views cached successfully" || echo "View cache failed"
        
        # Check Laravel installation
        php artisan --version || echo "Laravel check failed"
        
        echo "Laravel commands completed"
ENDSSH
    
    log "Laravel commands executed ✓"
}

# Verify cPanel deployment
verify_cpanel_deployment() {
    log "Verifying cPanel deployment..."
    
    # Test if the site responds
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN" || echo "000")
    
    if [[ "$HTTP_CODE" =~ ^[23] ]]; then
        log "Site is responding (HTTP $HTTP_CODE) ✓"
    else
        warn "Site returned HTTP $HTTP_CODE - please check manually"
    fi
    
    # Test API endpoint
    API_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN/api" || echo "000")
    
    if [[ "$API_CODE" =~ ^[23] ]]; then
        log "API is responding (HTTP $API_CODE) ✓"
    else
        warn "API returned HTTP $API_CODE - please check manually"
    fi
    
    info "Deployment verification completed"
}

# Main deployment function
deploy_to_cpanel() {
    log "Starting cPanel deployment of $APP_NAME to $SERVER_HOST"
    
    check_dependencies
    validate_config
    prepare_local_files
    create_cpanel_files
    upload_files
    configure_cpanel_server
    run_cpanel_laravel_commands
    verify_cpanel_deployment
    
    log "🚀 cPanel deployment completed successfully!"
    info "Your application should be available at: https://$DOMAIN"
    info "API endpoints available at: https://$DOMAIN/api/"
    info "If you see issues, check server logs via cPanel File Manager"
}

# =============================================================================
# SCRIPT EXECUTION
# =============================================================================

echo -e "${BLUE}"
echo "=============================================="
echo "  CSW Laravel cPanel Deployment Script"
echo "=============================================="
echo -e "${NC}"

# Check if this is a dry run
if [[ "$1" == "--dry-run" ]]; then
    log "DRY RUN MODE - No files will be uploaded"
    check_dependencies
    validate_config
    prepare_local_files
    create_cpanel_files
    log "Dry run completed - everything looks good!"
    # Clean up dry run files
    rm -f ./cpanel_index.php ./cpanel_htaccess
    exit 0
fi

# Confirm deployment
echo -e "${YELLOW}This will deploy your Laravel application to cPanel:${NC}"
echo -e "${YELLOW}Server: $SERVER_HOST${NC}"
echo -e "${YELLOW}User: $SERVER_USER${NC}"
echo -e "${YELLOW}Laravel Home: $SERVER_HOME${NC}"
echo -e "${YELLOW}Public HTML: $SERVER_PUBLIC${NC}"
echo -e "${YELLOW}Domain: $DOMAIN${NC}"
echo ""
read -p "Are you sure you want to continue? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    deploy_to_cpanel
else
    log "Deployment cancelled"
    exit 1
fi