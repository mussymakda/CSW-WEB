# 🏢 cPanel Deployment Guide - CSW Laravel App (UPDATED)

## 🎯 Quick Setup Overview
Your Laravel app is ready for cPanel deployment with this structure:
- **Laravel App**: `/home/csw/laravel/` (protected from web access)
- **Public Files**: `/home/csw/public_html/` (web accessible - fixes directory listing)
- **Database**: `csw_csw` (user: `csw_user`, password: `eBi;_*,LuBHE`)

## 🚨 FIXING DIRECTORY LISTING ISSUE
The directory listing you see happens because there's no `index.php` in `public_html`. Our deployment fixes this by:
1. Adding proper `index.php` file to `/home/csw/public_html/`
2. Adding `.htaccess` for Laravel routing
3. Structuring files properly for cPanel

## 📋 Database Configuration (Ready)
✅ **Database Name**: `csw_csw`  
✅ **Username**: `csw_user`   
✅ **Password**: `eBi;_*,LuBHE`  
✅ **Host**: `localhost`

## 🚀 Quick Deployment Steps

### Step 1: Prepare Files (Run This First)
```bash
# Copy environment file for cPanel
cp .env.cpanel .env

# Make deployment script executable  
chmod +x cpanel-deploy.sh

# Create deployment package
bash cpanel-deploy.sh
```

This creates:
- `cpanel_deploy/laravel/` - Complete Laravel app
- `cpanel_deploy/public_html/` - Public files with correct `index.php`
- `csw-laravel-cpanel.zip` - Upload package

### Step 2: Upload to cPanel Server

**Upload via cPanel File Manager:**
1. Login to cPanel at your hosting provider
2. Open **File Manager**
3. Navigate to `/home/csw/`
4. Upload `csw-laravel-cpanel.zip`
5. Extract ZIP file
6. Move extracted contents:
   - `cpanel_deploy/laravel/*` → `/home/csw/laravel/`
   - `cpanel_deploy/public_html/*` → `/home/csw/public_html/`

### Step 3: Set File Permissions
In cPanel File Manager:
- `/home/csw/laravel/storage/` → **755** (recursive)
- `/home/csw/laravel/bootstrap/cache/` → **755** (recursive)  
- `/home/csw/public_html/index.php` → **644**
- `/home/csw/public_html/.htaccess` → **644**

### Step 4: Setup Database (Already Configured)
Verify in cPanel **MySQL Databases**:
- Database `csw_csw` exists
- User `csw_user` has ALL PRIVILEGES on `csw_csw`

### Step 5: Run Laravel Setup
**If you have cPanel Terminal access:**
```bash
cd /home/csw/laravel
php artisan migrate --force
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan storage:link
```

**If no terminal access:**
- First API request will trigger automatic migration (if configured)
- Or contact hosting support to run these commands

## 📁 Final Directory Structure (After Deployment)

```
/home/csw/
├── public_html/              # WEB ACCESSIBLE (fixes directory listing)
│   ├── .htaccess            # ← Laravel routing & API handling
│   ├── index.php            # ← Laravel entry point (FIXES listing)
│   ├── css/                 # Compiled assets
│   ├── js/                  # Compiled assets  
│   ├── images/              # Public images
│   ├── storage/             # Symlink to laravel storage
│   └── favicon.ico          # Site favicon
│
└── laravel/                  # PROTECTED Laravel app
    ├── app/                 # Your application code
    ├── config/              # Laravel configuration
    ├── database/            # Migrations & seeders
    ├── routes/              # API routes (api.php)
    ├── storage/             # Logs, cache, uploads
    ├── .env                 # Production environment
    ├── artisan             # Laravel commands
    └── vendor/              # Dependencies
```

## 🔧 API Endpoints (After Deployment)

Your mobile app APIs will be available at:
```
Base URL: https://fitandfocusedacademics.com/api

Authentication:
POST /api/auth/login

Mobile APIs:  
GET /api/mobile/schedule
GET /api/mobile/progress-card
GET /api/mobile/sliders
GET /api/mobile/suggested-workouts
GET /api/mobile/notifications

User APIs:
GET /api/user/profile  
POST /api/user/profile
GET /api/user/account-setup-data
```

## 📱 Mobile App Configuration

Update your Flutter app:
```dart
// Replace localhost with your actual domain
final baseUrl = 'https://fitandfocusedacademics.com/api';
```

## ✅ Verification Steps

After deployment, test these:

1. **Main Site**: Visit `https://your-domain.com` 
   - Should show Laravel app (not directory listing)

2. **API Test**: 
   ```bash
   curl -H "Accept: application/json" https://your-domain.com/api/auth/login
   ```

3. **Database Test**: Login API should connect to database successfully

## 🔧 Troubleshooting

### Still seeing directory listing?
- Ensure `index.php` is in `/home/csw/public_html/`
- Check file permissions (644 for index.php)
- Clear browser cache

### 500 Internal Server Error?
- Check `/home/csw/laravel/storage/logs/laravel.log`
- Verify storage folder permissions (755)
- Ensure .env file has correct database credentials

### API not working?
- Verify `.htaccess` is in public_html
- Check mod_rewrite is enabled (contact hosting support)
- Test with curl commands above

### Database connection failed?
- Confirm database `csw_csw` exists in cPanel
- Verify user `csw_user` password: `eBi;_*,LuBHE`
- Check user has privileges on database

## 🛡️ Security Features

- ✅ Laravel app protected in `/laravel/` (not web accessible)
- ✅ `.env` file blocked by .htaccess  
- ✅ Security headers (XSS, CSRF protection)
- ✅ HTTPS enforcement
- ✅ API CORS properly configured

## 📊 Performance Features

- ✅ Gzip compression enabled
- ✅ Asset caching (CSS, JS, images)  
- ✅ Laravel config/route/view caching
- ✅ Optimized for shared hosting

Your CSW Laravel app is now production-ready on cPanel! 🎉

The directory listing issue is fixed with proper `index.php` and `.htaccess` files.