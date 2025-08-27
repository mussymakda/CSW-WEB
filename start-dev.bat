@echo off
REM CSW Web Application - Development Setup (Windows)

echo 🚀 Starting CSW Web Application Development Server
echo ==================================================

REM Check if .env exists
if not exist .env (
    echo ❌ .env file not found. Please copy .env.example to .env and configure it.
    pause
    exit /b 1
)

REM Install dependencies if needed
if not exist vendor (
    echo 📦 Installing PHP dependencies...
    composer install
)

if not exist node_modules (
    echo 📦 Installing Node.js dependencies...
    npm install
)

REM Generate app key if needed
findstr /c:"APP_KEY=" .env | findstr /c:"APP_KEY=$" >nul
if %errorlevel% equ 0 (
    echo 🔑 Generating application key...
    php artisan key:generate
)

REM Set up database
echo 🗄️  Setting up database...
if not exist database\database.sqlite (
    echo. > database\database.sqlite
)

REM Run migrations
echo Running database migrations...
php artisan migrate --force

REM Create admin user
echo 👤 Setting up admin user...
php artisan make:admin admin@csw.com password

REM Clear caches
echo 🧹 Clearing application caches...
php artisan optimize:clear

REM Start the development server
echo 🌐 Starting development server...
echo Admin Panel: http://127.0.0.1:8000/admin
echo Login: admin@csw.com / password
echo.
php artisan serve
