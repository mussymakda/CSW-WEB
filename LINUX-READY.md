# 🐧 Linux Server Deployment - Quick Reference

## Ready for Linux Server Deployment ✅

Your CSW Laravel app is now configured for Linux server deployment with:

### 📁 Deployment Files Created:
- **`deploy.sh`** - Enhanced Linux deployment script with SSH automation
- **`LINUX-DEPLOYMENT.md`** - Comprehensive Linux-specific deployment guide  
- **`.htaccess`** - Root public_html configuration
- **`public/.htaccess`** - Enhanced Laravel public directory configuration

### 🚀 Quick Deploy Commands:

```bash
# 1. Make script executable
chmod +x deploy.sh

# 2. Configure your server details in deploy.sh:
# SERVER_HOST="your-server.com"
# SERVER_USER="your-username" 
# SERVER_PATH="/home/your-username/public_html"
# DOMAIN="your-domain.com"

# 3. Test deployment
./deploy.sh --dry-run

# 4. Deploy to production
./deploy.sh
```

### 🔧 Linux Server Features:
- **SSH Key Authentication** - Secure automated deployment
- **Rsync File Transfer** - Efficient file synchronization
- **Automatic Permissions** - Proper Linux file permissions (755/775)
- **Laravel Optimization** - Config, route, and view caching
- **Symlink Creation** - Storage directory linking
- **Database Migration** - Automated schema updates
- **Error Handling** - Graceful failure recovery

### 📋 Directory Structure on Linux Server:
```
/home/your-username/public_html/
├── .htaccess              # Root redirects & security
├── index.php              # Modified Laravel entry point
├── css/, js/, images/     # Public assets
├── storage/               # Symlink to laravel/storage/app/public
└── laravel/               # Protected Laravel application
    ├── app/, config/      # Application code
    ├── storage/           # Logs, cache, uploads
    ├── .env              # Environment config
    └── artisan           # Laravel CLI
```

### 🛡️ Security Features:
- **HTTPS Redirection** - Automatic SSL enforcement
- **Directory Protection** - Laravel app files protected from direct access
- **Security Headers** - X-Frame-Options, XSS Protection, etc.
- **File Access Control** - .env and sensitive files blocked
- **CORS Configuration** - Proper API access headers

### 🔗 After Deployment:
1. **Test your APIs:** `https://your-domain.com/api/auth/login`
2. **Update mobile app:** Change base URL to `https://your-domain.com/api`
3. **Monitor logs:** `tail -f ~/public_html/laravel/storage/logs/laravel.log`

Your Laravel application is production-ready for Linux server deployment! 🎉