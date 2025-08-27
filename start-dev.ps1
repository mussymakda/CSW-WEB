# CSW Web Application - Development Setup (PowerShell)

Write-Host "🚀 Starting CSW Web Application Development Server" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green

# Check if .env exists
if (-not (Test-Path .env)) {
    Write-Host "❌ .env file not found. Please copy .env.example to .env and configure it." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Install dependencies if needed
if (-not (Test-Path vendor)) {
    Write-Host "📦 Installing PHP dependencies..." -ForegroundColor Yellow
    composer install
}

if (-not (Test-Path node_modules)) {
    Write-Host "📦 Installing Node.js dependencies..." -ForegroundColor Yellow
    npm install
}

# Generate app key if needed
$envContent = Get-Content .env
if ($envContent -match "APP_KEY=$") {
    Write-Host "🔑 Generating application key..." -ForegroundColor Yellow
    php artisan key:generate
}

# Set up database
Write-Host "🗄️  Setting up database..." -ForegroundColor Yellow
if (-not (Test-Path database\database.sqlite)) {
    New-Item -Path database\database.sqlite -ItemType File -Force | Out-Null
}

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force

# Create admin user
Write-Host "👤 Setting up admin user..." -ForegroundColor Yellow
php artisan make:admin admin@csw.com password

# Clear caches
Write-Host "🧹 Clearing application caches..." -ForegroundColor Yellow
php artisan optimize:clear

# Start the development server
Write-Host "🌐 Starting development server..." -ForegroundColor Green
Write-Host "Admin Panel: http://127.0.0.1:8000/admin" -ForegroundColor Cyan
Write-Host "Login: admin@csw.com / password" -ForegroundColor Cyan
Write-Host ""
php artisan serve
