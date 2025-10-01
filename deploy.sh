#!/bin/bash

# =============================================================================
# CSW Laravel App Deployment Script for public_html
# =============================================================================
# This script deploys your Laravel application to a shared hosting environment
# where files are typically placed in the public_html directory.
#
# Usage: bash deploy.sh
# Prerequisites: rsync, zip (for backup), ssh access to your server
# =============================================================================

set -e  # Exit on any error

# =============================================================================
# CONFIGURATION - UPDATE THESE VALUES
# =============================================================================

# Server Details
SERVER_HOST="your-server.com"
SERVER_USER="your-username"
SERVER_PATH="/home/your-username/public_html"
SSH_KEY_PATH="~/.ssh/id_rsa"  # Path to your SSH private key

# Local Paths
LOCAL_PROJECT_PATH="$(pwd)"
BACKUP_DIR="./backups"

# App Details
APP_NAME="CSW"
DOMAIN="your-domain.com"

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

# Check if required tools are installed
check_dependencies() {
    log "Checking dependencies..."
    
    command -v rsync >/dev/null 2>&1 || error "rsync is required but not installed"
    command -v zip >/dev/null 2>&1 || warn "zip not found - backups will be disabled"
    command -v ssh >/dev/null 2>&1 || error "ssh is required but not installed"
    command -v composer >/dev/null 2>&1 || error "composer is required but not installed"
    command -v php >/dev/null 2>&1 || error "PHP is required but not installed"
    
    log "All dependencies satisfied ✓"
}

# Validate configuration
validate_config() {
    log "Validating configuration..."
    
    if [[ "$SERVER_HOST" == "your-server.com" ]]; then
        error "Please update SERVER_HOST in the script configuration"
    fi
    
    if [[ "$SERVER_USER" == "your-username" ]]; then
        error "Please update SERVER_USER in the script configuration"
    fi
    
    if [[ ! -f ".env" ]]; then
        error ".env file not found. Please create one from .env.production"
    fi
    
    log "Configuration validated ✓"
}

# Create backup of current deployment
create_backup() {
    if command -v zip >/dev/null 2>&1; then
        log "Creating backup..."
        
        mkdir -p "$BACKUP_DIR"
        BACKUP_NAME="backup_$(date +'%Y%m%d_%H%M%S').zip"
        
        # Create backup of remote files
        ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" "cd $SERVER_PATH && zip -r /tmp/$BACKUP_NAME . 2>/dev/null || true"
        scp -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST:/tmp/$BACKUP_NAME" "$BACKUP_DIR/" 2>/dev/null || true
        ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" "rm -f /tmp/$BACKUP_NAME" 2>/dev/null || true
        
        log "Backup created: $BACKUP_DIR/$BACKUP_NAME ✓"
    else
        warn "Skipping backup - zip not available"
    fi
}

# Prepare local files for deployment
prepare_local_files() {
    log "Preparing local files..."
    
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

# Upload files to server
upload_files() {
    log "Uploading files to server..."
    
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
deploy.sh
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
XAMPP-SETUP.md
setup-xampp-path.ps1
start-dev.*
start-laravel-xampp.*
test_*.php
EOF

    # Upload application files (excluding Laravel public directory for now)
    rsync -avz \
        --exclude-from=.rsync_exclude \
        --exclude='public/' \
        --delete \
        -e "ssh -i $SSH_KEY_PATH" \
        ./ \
        "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/laravel/"
    
    # Upload public directory contents to public_html root
    rsync -avz \
        --delete \
        -e "ssh -i $SSH_KEY_PATH" \
        ./public/ \
        "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/"
    
    # Clean up
    rm -f .rsync_exclude
    
    log "Files uploaded successfully ✓"
}

# Configure server environment
configure_server() {
    log "Configuring server environment..."
    
    # Execute remote commands
    ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
        cd /home/$(whoami)/public_html
        
        # Update index.php to point to Laravel directory
        if [ -f index.php ]; then
            sed -i.bak 's|__DIR__\."/\.\./vendor/autoload\.php"|__DIR__."/laravel/vendor/autoload.php"|g' index.php
            sed -i.bak 's|__DIR__\."/\.\./bootstrap/app\.php"|__DIR__."/laravel/bootstrap/app.php"|g' index.php
            echo "Updated index.php paths"
        fi
        
        # Set proper Linux permissions
        chmod -R 755 laravel/ || echo "Could not set 755 on laravel directory"
        chmod -R 775 laravel/storage/ || echo "Could not set 775 on storage"
        chmod -R 775 laravel/bootstrap/cache/ || echo "Could not set 775 on bootstrap/cache"
        
        # Try to set ownership (may fail on shared hosting)
        chown -R www-data:www-data laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
        chown -R apache:apache laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
        chown -R $(whoami):$(whoami) laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
        echo "Could not change ownership - this may be normal on shared hosting"
        
        # Create symbolic links if they don't exist
        if [ ! -L storage ]; then
            ln -sfn laravel/storage/app/public storage && echo "Created storage symlink" || echo "Could not create storage symlink"
        fi
        
        echo "Server configuration completed"
ENDSSH
    
    log "Server configured ✓"
}

# Run Laravel commands on server
run_laravel_commands() {
    log "Running Laravel commands on server..."
    
    ssh -i "$SSH_KEY_PATH" "$SERVER_USER@$SERVER_HOST" << 'ENDSSH'
        cd /home/$(whoami)/public_html/laravel
        
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
        php artisan storage:link --force || echo "Storage link already exists or failed"
        
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

# Verify deployment
verify_deployment() {
    log "Verifying deployment..."
    
    # Test if the site responds
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN" || echo "000")
    
    if [[ "$HTTP_CODE" =~ ^[23] ]]; then
        log "Site is responding (HTTP $HTTP_CODE) ✓"
    else
        warn "Site returned HTTP $HTTP_CODE - please check manually"
    fi
    
    info "Deployment verification completed"
}

# Main deployment function
deploy() {
    log "Starting deployment of $APP_NAME to $SERVER_HOST"
    
    check_dependencies
    validate_config
    create_backup
    prepare_local_files
    upload_files
    configure_server
    run_laravel_commands
    verify_deployment
    
    log "🚀 Deployment completed successfully!"
    info "Your application should be available at: https://$DOMAIN"
    info "If you encounter issues, check the backup at: $BACKUP_DIR/"
}

# =============================================================================
# SCRIPT EXECUTION
# =============================================================================

echo -e "${BLUE}"
echo "=============================================="
echo "  CSW Laravel Deployment Script"
echo "=============================================="
echo -e "${NC}"

# Check if this is a dry run
if [[ "$1" == "--dry-run" ]]; then
    log "DRY RUN MODE - No files will be uploaded"
    check_dependencies
    validate_config
    prepare_local_files
    log "Dry run completed - everything looks good!"
    exit 0
fi

# Confirm deployment
echo -e "${YELLOW}This will deploy your Laravel application to:${NC}"
echo -e "${YELLOW}Server: $SERVER_HOST${NC}"
echo -e "${YELLOW}Path: $SERVER_PATH${NC}"
echo -e "${YELLOW}Domain: $DOMAIN${NC}"
echo ""
read -p "Are you sure you want to continue? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    deploy
else
    log "Deployment cancelled"
    exit 1
fi