# Server Deployment Without Composer

Since your server doesn't have Composer installed, you need to upload the complete Laravel project with all dependencies.

## Option 1: Upload Complete Project (Recommended)

### 1. Prepare Local Project for Upload
```bash
# On your local machine
cd C:\Users\musta\Documents\GitHub\CSW-WEB

# Make sure all dependencies are installed locally
composer install --optimize-autoloader --no-dev

# Create deployment package (exclude development files)
# Upload these directories to your server:
```

**Upload these folders/files to your server's `public_html/`:**
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `resources/`
- `routes/`
- `storage/`
- `vendor/` ← **This is crucial - contains all dependencies**
- `.env.example` (rename to `.env` on server)
- `artisan`
- `composer.json`
- `composer.lock`

**Also upload from your `public/` directory:**
- `index.php` (use the corrected server-index.php version)
- `.htaccess` (use the fixed version)
- `css/`
- `js/`
- Any other assets

### 2. Server Setup Commands
```bash
# On your server
cd /home/csw/public_html

# Copy environment file
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Generate application key
php artisan key:generate

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod 644 .env

# Run migrations
php artisan migrate

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link
```

## Option 2: Install Composer on Server (if you have shell access)

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Then run
composer install --optimize-autoloader --no-dev
```

## Environment Configuration (.env)
```bash
APP_NAME="CSW Fitness Academy"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fitandfocusedacademics.com

# Database (adjust for your server)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Or use SQLite for simplicity
# DB_CONNECTION=sqlite
# DB_DATABASE=/home/csw/public_html/database/database.sqlite

# Production settings
QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=file
LOG_CHANNEL=single

# Disable development features
OLLAMA_ENABLED=false
AI_NOTIFICATIONS_ENABLED=false
```

## Files to Upload Summary:
1. **All Laravel files** (with vendor/ folder)
2. **Fixed index.php** (server-index.php version)
3. **Fixed .htaccess** (simplified version)
4. **Environment file** (.env with production settings)

The key is uploading the complete `vendor/` directory with all PHP dependencies since the server can't run `composer install`.