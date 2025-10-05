# 🏗️ cPanel Deployment Guide for CSW Laravel App

## 📋 Overview
This guide shows you how to deploy your Laravel CSW app on cPanel shared hosting where:
- **Laravel app files** go in `/home/csw/` (your home directory)
- **Public files** go in `/home/csw/public_html/` (web-accessible directory)
- **Domain** points to `/home/csw/public_html/`

## 🚨 Fixing Directory Listing Issue
The directory listing you're seeing happens because there's no `index.php` file in `public_html`. Our deployment will fix this.

## 🗂️ Final Directory Structure
```
/home/csw/                           # Laravel application (not web accessible)
├── app/                            # Laravel app code
├── bootstrap/                      # Laravel bootstrap
├── config/                         # Configuration files
├── database/                       # Migrations, seeders
├── resources/                      # Views, assets
├── routes/                         # Route definitions
├── storage/                        # File storage, logs, cache
├── vendor/                         # Composer dependencies
├── .env                           # Environment configuration
├── artisan                        # Laravel CLI
└── composer.json                  # Dependencies

/home/csw/public_html/              # Web-accessible directory
├── .htaccess                      # Apache configuration (FIXES directory listing)
├── index.php                      # Laravel entry point (FIXES directory listing)
├── favicon.ico                    # Site favicon
├── robots.txt                     # SEO robots file
├── css/                          # Compiled CSS assets
├── js/                           # Compiled JavaScript assets
├── images/                       # Public images
└── storage/                      # Symlink to ../storage/app/public
```

## 🚀 Deployment Options

### Option 1: Automated Deployment (Recommended)

#### Prerequisites:
- SSH access to your cPanel server
- SSH key authentication set up
- Local development environment with PHP, Composer

#### Steps:

1. **Configure SSH access:**
   ```bash
   # Generate SSH key if you don't have one
   ssh-keygen -t rsa -b 4096 -C "your-email@example.com"
   
   # Copy to your server (replace with your actual server)
   ssh-copy-id csw@your-domain.com
   
   # Test connection
   ssh csw@your-domain.com
   ```

2. **Configure deployment script:**
   ```bash
   # Edit cpanel-deploy.sh
   nano cpanel-deploy.sh
   
   # Update these values:
   SERVER_HOST="your-domain.com"
   SERVER_USER="csw"
   DOMAIN="your-domain.com"
   ```

3. **Deploy:**
   ```bash
   # Make script executable
   chmod +x cpanel-deploy.sh
   
   # Test deployment (dry run)
   ./cpanel-deploy.sh --dry-run
   
   # Deploy to production
   ./cpanel-deploy.sh
   ```

### Option 2: Manual Deployment (If SSH not available)

#### Via cPanel File Manager:

1. **Upload Laravel files to home directory:**
   - Zip your entire Laravel project (excluding `public/` folder)
   - In cPanel File Manager, navigate to `/home/csw/`
   - Upload and extract the zip file
   - Delete the zip file after extraction

2. **Upload public files to public_html:**
   - Zip the contents of your `public/` folder
   - In cPanel File Manager, navigate to `/home/csw/public_html/`
   - Upload and extract the zip file
   - Delete the zip file after extraction

3. **Replace index.php:**
   - Delete the extracted `index.php` from `public_html`
   - Upload the `cpanel-index.php` file and rename it to `index.php`

4. **Replace .htaccess:**
   - Delete any existing `.htaccess` in `public_html`
   - Upload the `cpanel-public-htaccess` file and rename it to `.htaccess`

5. **Set permissions:**
   - Select `storage/` folder and set permissions to `775`
   - Select `bootstrap/cache/` folder and set permissions to `775`

## ⚙️ Configuration Steps

### 1. Database Setup (via cPanel)

1. **Create database:**
   - Go to cPanel → MySQL Databases
   - Create database: `csw_csw_production`
   - Create user: `csw_dbuser`
   - Set password: `your_secure_password`
   - Add user to database with ALL PRIVILEGES

2. **Update .env file:**
   ```bash
   # Via SSH or cPanel File Manager, edit /home/csw/.env
   
   APP_NAME="CSW App"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=csw_csw_production
   DB_USERNAME=csw_dbuser
   DB_PASSWORD=your_secure_password
   
   # Add your actual domain for Sanctum
   SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com
   SESSION_DOMAIN=.your-domain.com
   ```

### 2. Run Laravel Commands

#### Via SSH (if available):
```bash
ssh csw@your-domain.com
cd /home/csw

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### Via cPanel Terminal (if available):
```bash
cd /home/csw
php artisan migrate --force
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan storage:link
```

## 🔧 Troubleshooting

### Issue: Directory Listing Still Showing
**Solution:**
- Ensure `index.php` exists in `/home/csw/public_html/`
- Ensure `.htaccess` contains `Options -Indexes`
- Check file permissions: `index.php` should be 644

### Issue: 500 Internal Server Error
**Solutions:**
1. Check Laravel logs: `/home/csw/storage/logs/laravel.log`
2. Check cPanel Error Logs
3. Verify file permissions:
   - `storage/` folders: 775
   - `bootstrap/cache/` folders: 775
4. Ensure `.env` file has correct database credentials

### Issue: API Not Working
**Solutions:**
1. Verify `.htaccess` is properly uploaded to `public_html`
2. Check if mod_rewrite is enabled (contact hosting provider)
3. Test API endpoint: `https://your-domain.com/api/auth/login`

### Issue: Assets Not Loading
**Solutions:**
1. Run `php artisan storage:link` in `/home/csw/`
2. Check if symlinks are supported by hosting
3. Manually copy files from `storage/app/public/` to `public_html/storage/`

## 📱 Update Mobile App Configuration

After successful deployment, update your mobile app:

```dart
// Replace in your Flutter/mobile app
final baseUrl = 'https://your-domain.com/api';
```

## 🧪 Testing Your Deployment

```bash
# Test main site (should show Laravel app, not directory listing)
curl -I https://your-domain.com

# Test API login endpoint
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test API with authentication
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/mobile/schedule
```

## 🔒 Security Notes

- Laravel application files are protected (not web-accessible)
- `.env` file is protected by `.htaccess`
- Security headers automatically added
- Directory browsing is disabled
- Sensitive files are blocked from direct access

## 📊 Performance Features

- Gzip compression enabled
- Static asset caching (images, CSS, JS)
- Laravel route/config/view caching
- OPcache recommended (contact hosting provider)

## 🏗️ Files Created for cPanel Deployment

1. **`cpanel-deploy.sh`** - Automated deployment script
2. **`cpanel-index.php`** - Laravel entry point for public_html
3. **`cpanel-public-htaccess`** - Apache configuration for public_html

Your CSW Laravel application will be properly deployed on cPanel and accessible without directory listing! 🎉

## 📞 Support

If you encounter issues:
1. Check cPanel Error Logs
2. Check Laravel logs: `/home/csw/storage/logs/laravel.log`
3. Contact your hosting provider for mod_rewrite/PHP configuration
4. Verify file permissions are correct