#!/bin/bash

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Default web server user
WEB_USER="www-data"  # For Apache. Use 'nginx' for Nginx

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run this script as root or with sudo"
    exit 1
fi

# Detect web server user
if [ -f /etc/nginx/nginx.conf ]; then
    WEB_USER="nginx"
    echo "Detected Nginx: using $WEB_USER as web server user"
elif [ -f /etc/apache2/apache2.conf ]; then
    WEB_USER="www-data"
    echo "Detected Apache: using $WEB_USER as web server user"
else
    echo "Could not detect web server, using default: $WEB_USER"
fi

echo "Setting permissions for Laravel application..."

# Set base directory permissions
find "$SCRIPT_DIR" -type f -exec chmod 644 {} \;
find "$SCRIPT_DIR" -type d -exec chmod 755 {} \;

# Set storage and cache directory permissions
chmod -R 775 "$SCRIPT_DIR/storage"
chmod -R 775 "$SCRIPT_DIR/bootstrap/cache"

# Set ownership
chown -R $WEB_USER:$WEB_USER "$SCRIPT_DIR/storage"
chown -R $WEB_USER:$WEB_USER "$SCRIPT_DIR/bootstrap/cache"

# Protect .env file
if [ -f "$SCRIPT_DIR/.env" ]; then
    chmod 640 "$SCRIPT_DIR/.env"
    chown $WEB_USER:$WEB_USER "$SCRIPT_DIR/.env"
fi

echo "Permissions have been set!"

# Run the PHP permissions check script
if [ -f "$SCRIPT_DIR/check_permissions.php" ]; then
    echo "Running permissions check..."
    php "$SCRIPT_DIR/check_permissions.php"
else
    echo "Warning: check_permissions.php not found"
fi