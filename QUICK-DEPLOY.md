# Simple Upload and Run Commands

## 🚀 Quick Deployment Steps (No SSH Required)

### 1. Upload Files
Upload your project to server using FTP/cPanel File Manager:

**Upload Structure:**
```
public_html/
├── laravel/          ← Upload entire Laravel project here
│   ├── app/
│   ├── config/
│   ├── .env
│   └── artisan
├── .htaccess         ← Root htaccess file
├── index.php         ← From public/ folder
├── css/
└── js/
```

### 2. Run These Commands in Server Terminal

```bash
# Navigate to your directory
cd ~/public_html

# Fix index.php paths
sed -i 's|__DIR__.'"'"'/../vendor/autoload.php|__DIR__."/laravel/vendor/autoload.php|g' index.php
sed -i 's|__DIR__.'"'"'/../bootstrap/app.php|__DIR__."/laravel/bootstrap/app.php|g' index.php

# Set permissions
chmod -R 755 laravel/
chmod -R 775 laravel/storage/
chmod -R 775 laravel/bootstrap/cache/

# Configure Laravel
cd laravel
cp .env.production .env  # If needed
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache

# Create storage symlink
cd ..
ln -sfn laravel/storage/app/public storage

# Test
php laravel/artisan --version
```

### 3. Update Database in .env
```bash
cd ~/public_html/laravel
nano .env

# Update:
DB_DATABASE=your_database
DB_USERNAME=your_username  
DB_PASSWORD=your_password
```

### 4. Done! 
Your site should work at: `https://your-domain.com`
Your API at: `https://your-domain.com/api`

---

**Need help?** Check logs: `tail ~/public_html/laravel/storage/logs/laravel.log`