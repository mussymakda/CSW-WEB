#!/bin/bash
# CSW Web Application - Development Setup

echo "🚀 Starting CSW Web Application Development Server"
echo "=================================================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Please copy .env.example to .env and configure it."
    exit 1
fi

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Installing Node.js dependencies..."
    npm install
fi

# Generate app key if needed
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Set up database
echo "🗄️  Setting up database..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
fi

# Run migrations
php artisan migrate --force

# Create admin user if it doesn't exist
echo "👤 Setting up admin user..."
php artisan make:admin admin@csw.com password

# Clear caches
echo "🧹 Clearing application caches..."
php artisan optimize:clear

# Start the development server
echo "🌐 Starting development server..."
echo "Admin Panel: http://127.0.0.1:8000/admin"
echo "Login: admin@csw.com / password"
echo ""
php artisan serve
