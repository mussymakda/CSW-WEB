# 🐧 Linux Server Deployment Instructions for CSW Laravel App

## 📋 Quick Start for Linux Server

### Prerequisites
- Linux server with SSH access
- PHP 8.2+ installed
- Composer installed on local machine
- MySQL/MariaDB database
- Apache or Nginx web server
- SSH key authentication set up

### 1. Configure SSH Access

```bash
# Generate SSH key (if needed)
ssh-keygen -t rsa -b 4096 -C "your-email@example.com"

# Copy public key to server
ssh-copy-id your-username@your-server.com

# Test connection
ssh your-username@your-server.com
```

### 2. Configure Deployment Script

Edit `deploy.sh` with your server details:

```bash
nano deploy.sh

# Update these variables:
SERVER_HOST="your-server.com"
SERVER_USER="your-username"
SERVER_PATH="/home/your-username/public_html"
SSH_KEY_PATH="~/.ssh/id_rsa"
DOMAIN="your-domain.com"
```

### 3. Deploy to Linux Server

```bash
# Make script executable
chmod +x deploy.sh

# Test deployment (dry run)
./deploy.sh --dry-run

# Deploy to production
./deploy.sh
```

### 4. Post-Deployment Server Configuration

The script will automatically handle most configuration, but verify these settings:

#### Database Configuration:
```bash
# Connect to your server
ssh your-username@your-server.com

# Create database (if not exists)
mysql -u root -p
CREATE DATABASE csw_production;
CREATE USER 'csw_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON csw_production.* TO 'csw_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Update .env file
cd /home/your-username/public_html/laravel
nano .env

# Update database credentials:
DB_DATABASE=csw_production
DB_USERNAME=csw_user
DB_PASSWORD=secure_password
```

#### Web Server Configuration:

**For Apache (with .htaccess support):**
```bash
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**For Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /home/your-username/public_html;
    index index.php;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /home/your-username/public_html;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/certificate.pem;
    ssl_certificate_key /path/to/private.key;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to Laravel app directory
    location ~ ^/laravel/(app|bootstrap|config|database|resources|routes|storage|tests|vendor)/ {
        deny all;
        return 403;
    }
}
```

### 5. Verify Deployment

```bash
# Test the deployment
curl -I https://your-domain.com

# Test API endpoints
curl -H "Accept: application/json" https://your-domain.com/api/auth/login

# Check logs
ssh your-username@your-server.com "tail -f /home/your-username/public_html/laravel/storage/logs/laravel.log"
```

### 6. Linux-Specific File Permissions

```bash
# Connect to server
ssh your-username@your-server.com
cd /home/your-username/public_html

# Set proper permissions
chmod -R 755 .
chmod -R 775 laravel/storage/
chmod -R 775 laravel/bootstrap/cache/

# Set ownership (adjust based on your web server)
# For Apache:
sudo chown -R www-data:www-data laravel/storage/ laravel/bootstrap/cache/
# For Nginx:
sudo chown -R nginx:nginx laravel/storage/ laravel/bootstrap/cache/
# For shared hosting (if you have sudo access):
chown -R your-username:your-username laravel/storage/ laravel/bootstrap/cache/
```

### 7. Monitoring and Maintenance

```bash
# Check Laravel logs
tail -f /home/your-username/public_html/laravel/storage/logs/laravel.log

# Check web server logs
# Apache:
sudo tail -f /var/log/apache2/access.log
sudo tail -f /var/log/apache2/error.log

# Nginx:
sudo tail -f /var/log/nginx/access.log  
sudo tail -f /var/log/nginx/error.log

# Monitor system resources
htop
df -h
free -m
```

### 8. Backup Strategy for Linux

```bash
# Create backup script
nano /home/your-username/backup.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/your-username/backups"
mkdir -p $BACKUP_DIR

# Backup files
tar -czf $BACKUP_DIR/csw_files_$DATE.tar.gz -C /home/your-username/public_html .

# Backup database
mysqldump -u csw_user -p csw_production > $BACKUP_DIR/csw_db_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "csw_*" -mtime +7 -delete

# Make executable and add to crontab
chmod +x /home/your-username/backup.sh

# Add to crontab for daily backups at 2 AM
crontab -e
# Add: 0 2 * * * /home/your-username/backup.sh
```

### 9. Performance Optimization for Linux

```bash
# Install and configure OPcache
sudo apt update
sudo apt install php8.2-opcache

# Edit PHP configuration
sudo nano /etc/php/8.2/apache2/php.ini
# or for CLI:
sudo nano /etc/php/8.2/cli/php.ini

# Add/update OPcache settings:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0

# Restart web server
sudo systemctl restart apache2
# or for nginx:
sudo systemctl restart nginx php8.2-fpm
```

### 10. Security Hardening

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Configure firewall
sudo ufw enable
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS

# Disable unused services
sudo systemctl disable --now exim4    # if not using mail
sudo systemctl disable --now bluetooth

# Secure SSH
sudo nano /etc/ssh/sshd_config
# Set: PasswordAuthentication no
# Set: PermitRootLogin no
sudo systemctl restart ssh
```

Your CSW Laravel application is now properly deployed on your Linux server! 🚀

## 📱 Update Mobile App

After successful deployment, update your mobile app configuration:

```dart
// Replace in your Flutter app
final baseUrl = 'https://your-domain.com/api';
```

All API endpoints are now available at `https://your-domain.com/api/`