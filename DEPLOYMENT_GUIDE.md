# Deployment Instructions for Single public_html Directory

## Overview
This configuration puts the entire Laravel application in the public_html directory, which is suitable for shared hosting environments.

## Directory Structure After Deployment:
```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/           # Must be writable (755 or 775)
├── tests/
├── vendor/
├── .env              # Production environment
├── .htaccess         # Already exists in public/
├── artisan
├── composer.json
├── index.php         # Use the provided public_html_index.php
├── css/              # From public directory
├── js/               # From public directory
└── build/            # From public directory
```

## Deployment Steps:

### 1. Upload Files to Server
Upload ALL project files to your public_html directory:
- All Laravel application files (app/, config/, database/, etc.)
- All public assets (css/, js/, images/, etc.)

### 2. Replace index.php
Copy the contents from `public_html_index.php` to your `public_html/index.php`:

### 3. Set Directory Permissions
```bash
chmod -R 755 public_html/storage
chmod -R 755 public_html/bootstrap/cache
```

If you have shell access:
```bash
cd public_html
chmod -R 755 storage bootstrap/cache
# If www-data user exists (Apache/Nginx)
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Environment Configuration
Create/edit `.env` in public_html:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:your_generated_key_here

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

### 5. Run Artisan Commands
```bash
cd public_html

# Generate app key if needed
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create storage link
php artisan storage:link
```

### 6. Install Dependencies (if needed)
```bash
composer install --optimize-autoloader --no-dev
```

### 7. Security Considerations
- Ensure sensitive files are not accessible:
  - .env file should not be web-accessible (use .htaccess rules)
  - vendor/, app/, config/ directories should be protected

### 8. Create .htaccess Protection (Optional)
Create additional .htaccess files in sensitive directories:

`public_html/.htaccess` (already exists from Laravel public directory)
`public_html/app/.htaccess`:
```apache
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Deny from all
</IfModule>
```

Copy this .htaccess to: config/, database/, storage/, vendor/, bootstrap/

## Notes:
- This setup exposes more of the Laravel structure than recommended for security
- Consider moving sensitive directories outside public_html if possible
- The rate limiting and security headers middleware will still function correctly
- Queue processing will work with the database driver as configured