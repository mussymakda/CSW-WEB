# VPS Deployment Guide for CSW Laravel App

## 🚀 Production Deployment Checklist

### 1. Server Requirements
- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Nginx or Apache
- SSL Certificate (recommended)
- Node.js & NPM (for asset compilation)

### 2. Environment Configuration
1. Copy `.env.production` to `.env` on your VPS
2. Update the following variables for your server:
   ```bash
   APP_NAME="Your App Name"
   APP_URL=https://your-domain.com
   DB_DATABASE=your_production_database
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_secure_password
   SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com
   SESSION_DOMAIN=.your-domain.com
   ```

### 3. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE your_production_database;
CREATE USER 'your_db_username'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON your_production_database.* TO 'your_db_username'@'localhost';
FLUSH PRIVILEGES;
exit;
```

### 4. Laravel Setup Commands
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Generate application key (if needed)
php artisan key:generate

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symbolic link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Web Server Configuration

#### Nginx Configuration Example:
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/your-app/public;
    
    ssl_certificate /path/to/your/certificate.pem;
    ssl_certificate_key /path/to/your/private.key;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### 6. Security Considerations
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Configure proper file permissions
- [ ] Enable HTTPS with SSL certificate
- [ ] Configure firewall (UFW recommended)
- [ ] Regular backups of database and files
- [ ] Keep PHP and Laravel updated

### 7. Performance Optimizations
```bash
# Install OPcache for PHP
# Add to php.ini:
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000

# Queue worker (for background jobs)
php artisan queue:work --daemon
```

### 8. Monitoring & Logs
- Application logs: `storage/logs/`
- Web server logs: `/var/log/nginx/` or `/var/log/apache2/`
- Database logs: `/var/log/mysql/`

### 9. API Endpoints Ready for Production
All API endpoints are configured for production:
- Authentication: `POST /api/auth/login`
- Mobile APIs: `GET /api/mobile/*`
- User Profile: `GET /api/user/profile`
- Onboarding: `POST /api/onboarding/*`

### 10. Mobile App Configuration
Update your mobile app to use production URLs:
```dart
// Flutter/Dart example
final baseUrl = 'https://your-domain.com/api';
```

## 📱 API Documentation
- Main API docs: `API-DOCUMENTATION.md`
- Postman collection: `API-DOCUMENTATION-POSTMAN.md`

## 🔧 Troubleshooting
- Check Laravel logs in `storage/logs/laravel.log`
- Verify web server configuration
- Ensure database connection is working
- Check file permissions on storage directories
- Verify SSL certificate installation

---
**Note:** Replace `your-domain.com`, database credentials, and paths with your actual values.