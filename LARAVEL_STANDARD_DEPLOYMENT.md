# Laravel Standard Deployment Guide

## Proper Laravel Directory Structure Deployment

This guide follows Laravel best practices with the `public/` directory as the web root.

## Server Directory Structure (CORRECT WAY):

```
/home/csw/public_html/          # Upload your Laravel project here
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/                    # Must be writable
├── tests/
├── vendor/
├── .env                        # Production environment
├── .htaccess                   # Root redirect (use root.htaccess)
├── artisan
├── composer.json
├── composer.lock
└── public/                     # This should be your DOCUMENT ROOT
    ├── index.php              # Laravel entry point (already correct)
    ├── .htaccess              # Laravel routing (already exists)
    ├── css/
    ├── js/
    ├── build/
    └── storage -> ../storage/app/public
```

## Method 1: Configure Apache Document Root (RECOMMENDED)

### Option A: Via cPanel or Hosting Panel
1. **Change Document Root:**
   - Go to your hosting control panel
   - Find "Document Root" or "Public HTML" settings
   - Change document root from `/home/csw/public_html` to `/home/csw/public_html/public`
   - Save changes

### Option B: Via .htaccess Redirect (If you can't change document root)
1. **Use the root redirect .htaccess:**
   - Copy `root.htaccess` to `/home/csw/public_html/.htaccess`
   - This will redirect all requests to the `public/` directory

## Method 2: Complete Deployment Steps

### 1. Upload Files
Upload your ENTIRE Laravel project to `/home/csw/public_html/`:
- Keep the standard Laravel structure intact
- Don't move files around or flatten the structure

### 2. Set Up Root .htaccess (if document root can't be changed)
```bash
cd /home/csw/public_html
cp root.htaccess .htaccess
```

### 3. Set Permissions
```bash
cd /home/csw/public_html

# Set storage and cache permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set proper ownership (if possible)
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Environment Configuration
Create `.env` in `/home/csw/public_html/.env`:
```env
APP_NAME="CSW Web"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://fitandfocusedacademics.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Disable Ollama in production
OLLAMA_ENABLED=false
```

### 5. Run Laravel Setup Commands
```bash
cd /home/csw/public_html

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. Install Dependencies (if Composer is available)
```bash
cd /home/csw/public_html
composer install --optimize-autoloader --no-dev
```

## How It Works

1. **User visits:** `https://fitandfocusedacademics.com/`
2. **Apache serves:** `/home/csw/public_html/public/index.php` (via document root or .htaccess redirect)
3. **Laravel index.php loads:** Uses `../` paths to find framework files in parent directory
4. **Application runs:** Standard Laravel request lifecycle

## Security Features

- ✅ Framework files (app/, config/, etc.) are outside web root
- ✅ Environment files (.env) are protected
- ✅ Only public/ directory is web accessible
- ✅ Rate limiting and security headers active
- ✅ Proper file permissions set

## Troubleshooting

### If you see directory listing:
- Document root is pointing to wrong directory
- .htaccess redirect is not working
- Apache mod_rewrite is disabled

### If you get 500 errors:
- Check file permissions on storage/ and bootstrap/cache/
- Verify .env file exists and has correct database credentials
- Check Apache error logs for specific issues

### If Laravel doesn't load:
- Ensure public/index.php exists and is correct
- Verify vendor/autoload.php exists
- Check bootstrap/app.php exists

This is the PROPER Laravel way to deploy. Your application structure remains clean and secure.