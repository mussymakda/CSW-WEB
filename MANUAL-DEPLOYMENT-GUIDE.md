# 📋 Manual Server Terminal Deployment Guide

## 🚀 Deploy Without SSH - Run Commands Directly on Server

This guide helps you deploy your CSW Laravel app by running commands directly on your Linux server terminal (no SSH automation needed).

### 📁 Step 1: Upload Files to Server

First, upload your project files to the server using your preferred method (FTP, cPanel File Manager, etc.):

#### Upload Structure:
```
/home/your-username/public_html/
├── Upload Laravel app files to: laravel/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/           # Include this if composer not available
│   ├── .env              # Copy from .env.production
│   ├── artisan
│   └── composer.json
│
├── Upload public files to root:
│   ├── .htaccess         # Root .htaccess file
│   ├── index.php         # From public/ directory
│   ├── favicon.ico
│   ├── css/
│   ├── js/
│   └── images/
```

### 📋 Step 2: Connect to Server Terminal

Access your server terminal using one of these methods:
- **SSH:** `ssh your-username@your-server.com`
- **cPanel Terminal** (if available)
- **Server provider's web terminal**
- **Direct server access**

### 🔧 Step 3: Run Deployment Script

```bash
# Navigate to your public_html directory
cd ~/public_html

# Make deployment script executable
chmod +x server-deploy.sh

# Test the deployment (dry run)
bash server-deploy.sh --dry-run

# Run actual deployment
bash server-deploy.sh
```

### 📝 Step 4: Manual Commands (Alternative)

If you prefer to run commands manually instead of using the script:

```bash
# 1. Navigate to public_html
cd ~/public_html

# 2. Update index.php paths
sed -i 's|__DIR__.'"'"'/../vendor/autoload.php|__DIR__."/laravel/vendor/autoload.php|g' index.php
sed -i 's|__DIR__.'"'"'/../bootstrap/app.php|__DIR__."/laravel/bootstrap/app.php|g' index.php

# 3. Install composer dependencies (if composer available)
cd laravel
composer install --optimize-autoloader --no-dev --no-interaction

# 4. Configure Laravel
cp .env.production .env  # If .env doesn't exist
php artisan key:generate --force

# 5. Set permissions
cd ..
chmod -R 755 laravel/
chmod -R 775 laravel/storage/
chmod -R 775 laravel/bootstrap/cache/

# 6. Run Laravel commands
cd laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 7. Database setup
php artisan migrate --force

# 8. Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 9. Create storage symlink
cd ..
ln -sfn laravel/storage/app/public storage
```

### 🗄️ Step 5: Database Configuration

Update your database settings:

```bash
cd ~/public_html/laravel
nano .env

# Update these values:
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Save and run migration
php artisan migrate --force
```

### ✅ Step 6: Verify Deployment

Test your deployment:

```bash
# Check Laravel version
cd ~/public_html/laravel
php artisan --version

# Test file structure
ls -la ~/public_html/
ls -la ~/public_html/laravel/

# Check permissions
ls -la ~/public_html/laravel/storage/
ls -la ~/public_html/laravel/bootstrap/cache/

# View Laravel logs
tail ~/public_html/laravel/storage/logs/laravel.log
```

### 🌐 Step 7: Test Your Website

```bash
# Test main site (if curl available)
curl -I https://your-domain.com

# Test API endpoint
curl -H "Accept: application/json" https://your-domain.com/api/auth/login
```

### 🔧 Common Issues & Solutions

#### Issue: "Composer not found"
```bash
# Option 1: Install composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install --optimize-autoloader --no-dev

# Option 2: Upload vendor/ directory from local machine
```

#### Issue: "Permission denied"
```bash
# Fix permissions
chmod -R 755 ~/public_html/laravel/
chmod -R 775 ~/public_html/laravel/storage/
chmod -R 775 ~/public_html/laravel/bootstrap/cache/
```

#### Issue: "Database connection failed"
```bash
# Check database credentials
cd ~/public_html/laravel
php artisan tinker
# Test: DB::connection()->getPdo();
```

#### Issue: "Storage symlink failed"
```bash
# Manual symlink creation
cd ~/public_html
rm -f storage  # Remove if exists
ln -sfn laravel/storage/app/public storage
```

### 📊 File Permissions Guide

```bash
# Recommended permissions:
chmod 755 ~/public_html/                     # Public directory
chmod 755 ~/public_html/laravel/             # Laravel app
chmod 775 ~/public_html/laravel/storage/     # Storage directory
chmod 775 ~/public_html/laravel/bootstrap/cache/  # Cache directory
chmod 644 ~/public_html/.htaccess            # Apache config
chmod 644 ~/public_html/index.php            # Entry point
```

### 🔍 Monitoring & Maintenance

```bash
# Check application logs
tail -f ~/public_html/laravel/storage/logs/laravel.log

# Clear cache when needed
cd ~/public_html/laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Update application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 📱 Update Mobile App

After successful deployment, update your mobile app base URL:

```dart
// Replace in your Flutter/mobile app
final baseUrl = 'https://your-domain.com/api';
```

Your CSW Laravel application is now deployed and ready for production use! 🎉