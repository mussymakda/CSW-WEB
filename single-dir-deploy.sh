#!/bin/bash

# =============================================================================
# CSW Laravel App - Single Directory Deployment Script
# =============================================================================
# Run this script directly on your Linux server in public_html directory
# This version assumes all Laravel files are in public_html root (no subfolder)
# 
# Usage: bash single-dir-deploy.sh
# =============================================================================

set -e  # Exit on any error

# =============================================================================
# CONFIGURATION - UPDATE THESE VALUES
# =============================================================================

# Server Paths
PUBLIC_HTML_PATH="/home/$(whoami)/public_html"
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

# Check if we're in the right directory and have required files
check_environment() {
    log "Checking server environment..."
    
    # Check if we have PHP
    if ! command -v php >/dev/null 2>&1; then
        error "PHP is not installed or not in PATH"
    fi
    
    PHP_VERSION=$(php -v | head -n 1)
    info "PHP Version: $PHP_VERSION"
    
    # Check if we're in the right location
    if [[ ! -d "$PUBLIC_HTML_PATH" ]]; then
        error "public_html directory not found at $PUBLIC_HTML_PATH"
    fi
    
    # Check for Laravel files
    cd "$PUBLIC_HTML_PATH"
    
    if [[ ! -f "artisan" ]]; then
        error "Laravel artisan not found - make sure Laravel files are uploaded to public_html"
    fi
    
    if [[ ! -f "composer.json" ]]; then
        warn "composer.json not found - this may not be a Laravel project"
    fi
    
    if [[ ! -d "app" ]]; then
        error "Laravel app directory not found"
    fi
    
    log "Environment check completed ✓"
}

# Install/update composer dependencies if needed
install_dependencies() {
    log "Checking Laravel dependencies..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Check if vendor directory exists
    if [[ ! -d "vendor" ]]; then
        info "Vendor directory not found - attempting to install dependencies..."
        
        # Use composer or composer.phar
        if command -v composer >/dev/null 2>&1; then
            composer install --optimize-autoloader --no-dev --no-interaction
        elif [[ -f "composer.phar" ]]; then
            php composer.phar install --optimize-autoloader --no-dev --no-interaction
        else
            warn "Composer not found and no vendor/ directory - you may need to upload vendor/ manually"
            return 0
        fi
    else
        info "Dependencies already installed ✓"
    fi
    
    log "Dependencies ready ✓"
}

# Configure Laravel environment
configure_laravel() {
    log "Configuring Laravel environment..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Check if .env exists
    if [[ ! -f ".env" ]]; then
        if [[ -f ".env.production" ]]; then
            cp .env.production .env
            log "Copied .env.production to .env"
        else
            warn ".env file not found - you'll need to create one manually"
        fi
    fi
    
    # Generate app key if needed
    if [[ -f ".env" ]] && ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        log "Generated application key"
    fi
    
    log "Laravel environment configured ✓"
}

# Set proper file permissions
set_permissions() {
    log "Setting file permissions..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Set general permissions
    chmod -R 755 . 2>/dev/null || warn "Could not set 755 on public_html"
    chmod -R 775 storage/ 2>/dev/null || warn "Could not set 775 on storage"
    chmod -R 775 bootstrap/cache/ 2>/dev/null || warn "Could not set 775 on bootstrap/cache"
    
    # Try to set ownership (may fail on shared hosting)
    chown -R www-data:www-data storage/ bootstrap/cache/ 2>/dev/null || \
    chown -R apache:apache storage/ bootstrap/cache/ 2>/dev/null || \
    chown -R $(whoami):$(whoami) storage/ bootstrap/cache/ 2>/dev/null || \
    warn "Could not change ownership - this may be normal on shared hosting"
    
    log "File permissions set ✓"
}

# Run Laravel artisan commands
run_laravel_commands() {
    log "Running Laravel commands..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Clear caches first
    info "Clearing caches..."
    php artisan config:clear 2>/dev/null || warn "Config clear failed"
    php artisan route:clear 2>/dev/null || warn "Route clear failed"
    php artisan view:clear 2>/dev/null || warn "View clear failed"
    php artisan cache:clear 2>/dev/null || warn "Cache clear failed"
    
    # Run database migrations
    info "Running database migrations..."
    php artisan migrate --force --no-interaction 2>/dev/null || warn "Database migration failed - check your database configuration in .env"
    
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

# Verify deployment
verify_deployment() {
    log "Verifying deployment..."
    
    cd "$PUBLIC_HTML_PATH"
    
    # Check if key files exist
    local files_check=true
    
    [[ -f "index.php" ]] || { warn "index.php missing"; files_check=false; }
    [[ -f ".htaccess" ]] || { warn ".htaccess missing"; files_check=false; }
    [[ -f "artisan" ]] || { warn "artisan missing"; files_check=false; }
    [[ -d "storage" ]] || { warn "storage directory missing"; files_check=false; }
    [[ -d "app" ]] || { warn "app directory missing"; files_check=false; }
    
    if $files_check; then
        log "File structure verification passed ✓"
    else
        warn "Some files are missing - deployment may not work correctly"
    fi
    
    # Test Laravel installation
    if php artisan --version >/dev/null 2>&1; then
        LARAVEL_VERSION=$(php artisan --version)
        log "Laravel check passed: $LARAVEL_VERSION ✓"
    else
        warn "Laravel installation check failed"
    fi
    
    # Check storage permissions
    if [[ -w "storage" ]] && [[ -w "bootstrap/cache" ]]; then
        log "Storage permissions check passed ✓"
    else
        warn "Storage directories may not be writable"
    fi
}

# Display post-deployment information
show_completion_info() {
    log "🚀 Deployment completed!"
    
    echo ""
    info "=== Deployment Summary ==="
    info "Domain: https://$DOMAIN"
    info "Path: $PUBLIC_HTML_PATH"
    echo ""
    info "=== Next Steps ==="
    info "1. Update your .env file with correct database credentials"
    info "2. Test your website: https://$DOMAIN"
    info "3. Test API endpoints: https://$DOMAIN/api/auth/login"
    info "4. Update your mobile app base URL to: https://$DOMAIN/api"
    echo ""
    info "=== Useful Commands ==="
    info "Check logs: tail -f $PUBLIC_HTML_PATH/storage/logs/laravel.log"
    info "Clear cache: cd $PUBLIC_HTML_PATH && php artisan cache:clear"
    info "Run migrations: cd $PUBLIC_HTML_PATH && php artisan migrate"
    echo ""
}

# Main deployment function
deploy() {
    log "Starting CSW Laravel deployment (single directory mode)"
    
    check_environment
    install_dependencies
    configure_laravel
    set_permissions
    run_laravel_commands
    verify_deployment
    show_completion_info
}

# =============================================================================
# SCRIPT EXECUTION
# =============================================================================

echo -e "${BLUE}"
echo "=============================================="
echo "  CSW Laravel Single Directory Deployment"
echo "=============================================="
echo -e "${NC}"

# Check if this is a dry run
if [[ "$1" == "--dry-run" ]]; then
    log "DRY RUN MODE - Checking environment only"
    check_environment
    log "Dry run completed - ready for deployment!"
    exit 0
fi

# Show current configuration
echo -e "${YELLOW}Deployment Configuration:${NC}"
echo -e "${YELLOW}Public HTML Path: $PUBLIC_HTML_PATH${NC}"
echo -e "${YELLOW}Domain: $DOMAIN${NC}"
echo ""
echo -e "${YELLOW}This script assumes all Laravel files are in public_html root directory${NC}"
echo ""
read -p "Continue with deployment? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    deploy
else
    log "Deployment cancelled"
    exit 1
fi