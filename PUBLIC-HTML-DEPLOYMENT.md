# 🚀 Public HTML Deployment Guide for CSW Laravel App

## 📁 Directory Structure After Deployment

```
public_html/                          # Your hosting root directory
├── .htaccess                        # Root .htaccess (redirects to Laravel)
├── index.php                        # Laravel's public/index.php (modified)
├── favicon.ico                      # App favicon
├── robots.txt                       # SEO robots file
├── css/                            # Compiled CSS assets
├── js/                             # Compiled JavaScript assets
├── images/                         # Public images
├── storage/                        # Symlink to laravel/storage/app/public
│
└── laravel/                        # Laravel application (protected)
    ├── app/                        # Application code
    ├── bootstrap/                  # Laravel bootstrap
    ├── config/                     # Configuration files
    ├── database/                   # Migrations, seeds, factories
    ├── resources/                  # Views, assets sources
    ├── routes/                     # Route definitions
    ├── storage/                    # File storage, logs, cache
    ├── vendor/                     # Composer dependencies
    ├── .env                       # Environment configuration
    ├── artisan                    # Laravel CLI tool
    └── composer.json              # Dependencies
```

## � SSH Setup for Linux Server

### Setting up SSH Key Authentication:

1. **Generate SSH key (if you don't have one):**
   ```bash
   ssh-keygen -t rsa -b 4096 -C "your-email@example.com"
   ```

2. **Copy your public key to the server:**
   ```bash
   ssh-copy-id your-username@your-server.com
   ```

3. **Test SSH connection:**
   ```bash
   ssh your-username@your-server.com
   ```

## �📋 Deployment Steps

### 1. Prepare Your Files

Before deployment, ensure your `.env` file is configured for production:

```bash
# Check your .env file
cat .env

# Should have:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 2. Using the Linux Deployment Script

#### Automated Deployment (Recommended for Linux servers)

1. **Make the deployment script executable:**
   ```bash
   chmod +x deploy.sh
   ```

2. **Configure the deployment script:**
   ```bash
   # Edit deploy.sh and update these values:
   SERVER_HOST="your-server.com"
   SERVER_USER="your-username" 
   SERVER_PATH="/home/your-username/public_html"
   DOMAIN="your-domain.com"
   SSH_KEY_PATH="~/.ssh/id_rsa"  # Path to your SSH key
   ```

3. **Test deployment (dry run):**
   ```bash
   ./deploy.sh --dry-run
   ```

4. **Deploy to server:**
   ```bash
   ./deploy.sh
   ```

#### Prerequisites for Linux deployment:
- SSH access to your server
- rsync installed (usually pre-installed on Linux)
- SSH key authentication set up

#### Option B: Manual Deployment

If you prefer manual deployment or the script doesn't work for your hosting:

1. **Upload Laravel files to laravel/ subdirectory:**
   - Upload everything EXCEPT the `public/` folder to `public_html/laravel/`

2. **Upload public files to public_html root:**
   - Upload contents of `public/` folder to `public_html/` root

3. **Copy .htaccess files:**
   - Copy `.htaccess` (root) to `public_html/.htaccess`
   - The `public/.htaccess` should already be in place

4. **Modify index.php:**
   Update `public_html/index.php` to point to the Laravel directory:
   ```php
   require __DIR__.'/laravel/vendor/autoload.php';
   $app = require_once __DIR__.'/laravel/bootstrap/app.php';
   ```

### 3. Post-Deployment Configuration

#### Via SSH (Recommended for Linux servers):
```bash
ssh your-username@your-server.com
cd public_html/laravel

# Run Laravel commands
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Set proper Linux permissions
chmod -R 755 .
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chown -R www-data:www-data storage/ bootstrap/cache/ || chown -R apache:apache storage/ bootstrap/cache/

# Create storage symlink
ln -sfn ../laravel/storage/app/public ../storage
```

#### Via cPanel File Manager:
1. Navigate to `public_html/laravel/`
2. Set permissions:
   - `storage/` folder: 775
   - `bootstrap/cache/` folder: 775
3. Create storage symlink (if hosting supports it)

## 🔧 Important Files Explained

### Root .htaccess (`public_html/.htaccess`)
```apache
# Key features:
- Redirects HTTP to HTTPS
- Handles API routes properly
- Protects Laravel directory from direct access
- Caching for static assets
- Security headers
```

### Public .htaccess (`public_html/public/.htaccess`)
```apache
# Key features:
- CORS headers for API
- Bearer token handling
- Enhanced security
- Gzip compression
- Asset caching
```

### Modified index.php
The `index.php` file is modified to load Laravel from the `laravel/` subdirectory:
```php
// Original Laravel paths
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Modified for public_html structure
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';
```

## 📱 API Endpoints

After deployment, your API will be available at:

- **Authentication:** `https://your-domain.com/api/auth/login`
- **Mobile APIs:** `https://your-domain.com/api/mobile/*`
- **User Profile:** `https://your-domain.com/api/user/*`
- **Onboarding:** `https://your-domain.com/api/onboarding/*`

## 🔍 Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check `laravel/storage/logs/laravel.log`
   - Verify file permissions on storage and bootstrap/cache
   - Ensure .env file is properly configured

2. **API Routes Not Working**
   - Verify root .htaccess is in place
   - Check if mod_rewrite is enabled on server
   - Ensure Bearer token headers are being passed

3. **Assets Not Loading**
   - Run `php artisan storage:link` in laravel directory
   - Check if symbolic links are supported by hosting
   - Verify asset paths in .htaccess

4. **Database Connection Issues**
   - Update .env with correct database credentials
   - Run migrations: `php artisan migrate --force`
   - Check database user permissions

### Testing Your Linux Deployment:

```bash
# Test main site
curl -I https://your-domain.com

# Test API endpoint
curl -H "Accept: application/json" https://your-domain.com/api/user/profile

# Test with authentication
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/mobile/schedule

# Check server logs
ssh your-username@your-server.com "tail -f /home/your-username/public_html/laravel/storage/logs/laravel.log"
```

## 🛡️ Security Notes

- Laravel application files are protected in the `laravel/` subdirectory
- `.env` file is blocked by .htaccess rules
- Security headers are automatically added
- HTTPS redirection is enforced
- Sensitive files are denied access

## 📊 Performance Optimizations

- Gzip compression enabled
- Static asset caching (1 month for images/CSS/JS)
- Laravel configuration caching
- Route caching for faster response times
- View caching to reduce compilation overhead

Your Laravel application is now ready for production use on shared hosting! 🎉