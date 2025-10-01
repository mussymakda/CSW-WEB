#!/bin/bash

# =============================================================================
# CSW Laravel App - Server Terminal Deployment Script
# =============================================================================
# Run this script directly on your Linux server (no SSH required)
# This script assumes you've already uploaded your files to the server
# 
# Usage: bash server-deploy.sh
# =============================================================================

set -e  # Exit on any error

# =============================================================================
# CONFIGURATION - UPDATE THESE VALUES
# =============================================================================

# Server Paths (adjust for your server)
PUBLIC_HTML_PATH="/home/$(whoami)/public_html"
LARAVEL_PATH="$PUBLIC_HTML_PATH/laravel"
DOMAIN="fitandfocusedacademics.com/"

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

# Check if we're in the right directory and have required files
check_environment() {
    log "Checking server environment..."
    
    # Check if we have PHP
    if ! command -v php >/dev/null 2>&1; then
        error "PHP is not installed or not in PATH"
    fi
    
    PHP_VERSION=$(php -v | head -n 1)
    info "PHP Version: $PHP_VERSION"
    
    # Check if composer is available
    if ! command -v composer >/dev/null 2>&1; then
        warn "Composer not found globally - will try to use local composer.phar if available"
    fi
    
    # Check if we're in the right location
    if [[ ! -d "$PUBLIC_HTML_PATH" ]]; then
        error "public_html directory not found at $PUBLIC_HTML_PATH"
    fi
    
    log "Environment check completed ✓"
}

# Setup directory structure for Laravel in public_html
setup_directory_structure() {
    log "Setting up directory structure..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Create laravel directory if it doesn't exist
    mkdir -p laravel
    
    log "Directory structure ready ✓"
}

# Install/update composer dependencies
install_dependencies() {
    log "Installing Laravel dependencies..."
    
    cd "$LARAVEL_PATH"
    
    # Use composer or composer.phar
    if command -v composer >/dev/null 2>&1; then
        composer install --optimize-autoloader --no-dev --no-interaction
    elif [[ -f "composer.phar" ]]; then
        php composer.phar install --optimize-autoloader --no-dev --no-interaction
    else
        warn "Composer not found - skipping dependency installation"
        warn "You may need to upload vendor/ directory or install composer"
        return 0
    fi
    
    log "Dependencies installed ✓"
}

# Configure Laravel environment
configure_laravel() {
    log "Configuring Laravel environment..."
    
    cd "$LARAVEL_PATH"
    
    # Check if .env exists
    if [[ ! -f ".env" ]]; then
        if [[ -f ".env.production" ]]; then
            cp .env.production .env
            log "Copied .env.production to .env"
        else
            error ".env file not found and no .env.production template available"
        fi
    fi
    
    # Generate app key if needed
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        log "Generated application key"
    fi
    
    log "Laravel environment configured ✓"
}

# Update index.php to point to Laravel directory
fix_index_php() {
    log "Updating index.php for public_html structure..."
    
    cd "$PUBLIC_HTML_PATH"
    
    if [[ -f "index.php" ]]; then
        # Create backup
        cp index.php index.php.backup
        
        # Update paths to point to laravel directory
        sed -i 's|__DIR__\.'"'"'/\.\./vendor/autoload\.php|__DIR__."/laravel/vendor/autoload.php|g' index.php
        sed -i 's|__DIR__\.'"'"'/\.\./bootstrap/app\.php|__DIR__."/laravel/bootstrap/app.php|g' index.php
        
        log "Updated index.php paths ✓"
    else
        error "index.php not found in public_html"
    fi
}

# Set proper file permissions
set_permissions() {
    log "Setting file permissions..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Set general permissions
    chmod -R 755 laravel/ 2>/dev/null || warn "Could not set 755 on laravel directory"
    chmod -R 775 laravel/storage/ 2>/dev/null || warn "Could not set 775 on storage"
    chmod -R 775 laravel/bootstrap/cache/ 2>/dev/null || warn "Could not set 775 on bootstrap/cache"
    
    # Try to set ownership (may fail on shared hosting)
    chown -R www-data:www-data laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
    chown -R apache:apache laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
    chown -R $(whoami):$(whoami) laravel/storage/ laravel/bootstrap/cache/ 2>/dev/null || \
    warn "Could not change ownership - this may be normal on shared hosting"
    
    log "File permissions set ✓"
}

# Run Laravel artisan commands
run_laravel_commands() {
    log "Running Laravel commands..."
    
    cd "$LARAVEL_PATH"
    
    # Check if artisan exists
    if [[ ! -f "artisan" ]]; then
        error "Laravel artisan not found - make sure Laravel files are uploaded correctly"
    fi
    
    # Clear caches first
    info "Clearing caches..."
    php artisan config:clear 2>/dev/null || warn "Config clear failed"
    php artisan route:clear 2>/dev/null || warn "Route clear failed"
    php artisan view:clear 2>/dev/null || warn "View clear failed"
    php artisan cache:clear 2>/dev/null || warn "Cache clear failed"
    
    # Run database migrations
    info "Running database migrations..."
    php artisan migrate --force --no-interaction 2>/dev/null || warn "Database migration failed - check your database configuration"
    
    # Create storage symlink
    info "Creating storage symlink..."
    php artisan storage:link --force 2>/dev/null || warn "Storage link creation failed"
    
    # Cache for production
    info "Caching for production..."
    php artisan config:cache && log "Config cached ✓" || warn "Config cache failed"
    php artisan route:cache && log "Routes cached ✓" || warn "Route cache failed"
    php artisan view:cache && log "Views cached ✓" || warn "View cache failed"
    
    log "Laravel commands completed ✓"
}

# Create storage symlink in public_html root
create_storage_symlink() {
    log "Creating storage symlink in public_html..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Remove existing symlink if it exists
    [[ -L storage ]] && rm storage
    
    # Create new symlink
    ln -sfn laravel/storage/app/public storage && log "Storage symlink created ✓" || warn "Could not create storage symlink"
}

# Verify deployment
verify_deployment() {
    log "Verifying deployment..."
    
    # Check if key files exist
    local files_check=true
    
    [[ -f "$PUBLIC_HTML_PATH/index.php" ]] || { warn "index.php missing in public_html"; files_check=false; }
    [[ -f "$PUBLIC_HTML_PATH/.htaccess" ]] || { warn ".htaccess missing in public_html"; files_check=false; }
    [[ -f "$LARAVEL_PATH/artisan" ]] || { warn "artisan missing in laravel directory"; files_check=false; }
    [[ -f "$LARAVEL_PATH/.env" ]] || { warn ".env missing in laravel directory"; files_check=false; }
    
    if $files_check; then
        log "File structure verification passed ✓"
    else
        warn "Some files are missing - deployment may not work correctly"
    fi
    
    # Test Laravel installation
    cd "$LARAVEL_PATH"
    if php artisan --version >/dev/null 2>&1; then
        LARAVEL_VERSION=$(php artisan --version)
        log "Laravel check passed: $LARAVEL_VERSION ✓"
    else
        warn "Laravel installation check failed"
    fi
}

# Display post-deployment information
show_completion_info() {
    log "🚀 Deployment completed!"
    
    echo ""
    info "=== Deployment Summary ==="
    info "Domain: https://$DOMAIN"
    info "Laravel Path: $LARAVEL_PATH"
    info "Public Path: $PUBLIC_HTML_PATH"
    echo ""
    info "=== Next Steps ==="
    info "1. Update your .env file with correct database credentials"
    info "2. Test your website: https://$DOMAIN"
    info "3. Test API endpoints: https://$DOMAIN/api/auth/login"
    info "4. Update your mobile app base URL to: https://$DOMAIN/api"
    echo ""
    info "=== Useful Commands ==="
    info "Check logs: tail -f $LARAVEL_PATH/storage/logs/laravel.log"
    info "Clear cache: cd $LARAVEL_PATH && php artisan cache:clear"
    info "Run migrations: cd $LARAVEL_PATH && php artisan migrate"
    echo ""
}

# Main deployment function
deploy() {
    log "Starting CSW Laravel deployment on server terminal"
    
    check_environment
    setup_directory_structure
    install_dependencies
    configure_laravel
    fix_index_php
    set_permissions
    run_laravel_commands
    create_storage_symlink
    verify_deployment
    show_completion_info
}

# =============================================================================
# SCRIPT EXECUTION
# =============================================================================

echo -e "${BLUE}"
echo "=============================================="
echo "  CSW Laravel Server Terminal Deployment"
echo "=============================================="
echo -e "${NC}"

# Check if this is a dry run
if [[ "$1" == "--dry-run" ]]; then
    log "DRY RUN MODE - Checking environment only"
    check_environment
    setup_directory_structure
    log "Dry run completed - ready for deployment!"
    exit 0
fi

# Show current configuration
echo -e "${YELLOW}Deployment Configuration:${NC}"
echo -e "${YELLOW}Public HTML Path: $PUBLIC_HTML_PATH${NC}"
echo -e "${YELLOW}Laravel Path: $LARAVEL_PATH${NC}"
echo -e "${YELLOW}Domain: $DOMAIN${NC}"
echo ""
read -p "Continue with deployment? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    deploy
else
    log "Deployment cancelled"
    exit 1
fi