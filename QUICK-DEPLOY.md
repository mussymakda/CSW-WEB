# Simple Upload and Run Commands

## 🚀 Quick Deployment Steps (No SSH Required)

### 1. Upload Files
Upload your project to server using FTP/cPanel File Manager:

**Upload Structure (All files in public_html root):**
```
public_html/
├── app/              ← Laravel application files
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env              ← Environment configuration
├── .htaccess         ← Laravel's public/.htaccess
├── artisan           ← Laravel CLI
├── composer.json
├── index.php         ← Laravel's public/index.php (no changes needed)
├── css/              ← Public assets
├── js/
└── images/
```

### 2. Run These Commands in Server Terminal

```bash
# Navigate to your directory
cd ~/public_html

# Set permissions
chmod -R 755 .
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Configure Laravel (if .env doesn't exist)
cp .env.production .env  # If needed
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Create storage symlink (for file uploads)
php artisan storage:link

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test Laravel installation
php artisan --version
```

### 3. Update Database in .env
```bash
cd ~/public_html
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

**Need help?** Check logs: `tail ~/public_html/storage/logs/laravel.log`