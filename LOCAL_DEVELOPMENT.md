# Laravel Local Development Setup

This guide will help you run the CSW Laravel project locally in the standard Laravel structure.

## Quick Local Setup

### 1. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if you have package.json)
npm install
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set local database (SQLite is easiest for local development)
# Edit .env file:
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 3. Database Setup
```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed test data (optional)
php artisan db:seed --class=TestDataSeeder
```

### 4. Start Development Server
```bash
# Start Laravel development server
php artisan serve

# Your app will be available at: http://localhost:8000
```

### 5. Frontend Assets (if needed)
```bash
# Build frontend assets
npm run dev

# Or for production build
npm run build
```

## Local Development URLs

- **Main App**: http://localhost:8000
- **Admin Panel (Filament)**: http://localhost:8000/admin
- **API Endpoints**: http://localhost:8000/api/

## Default Admin Access
- **Email**: admin@fitandfocusedacademics.com
- **Password**: password123

## Local Environment Settings (.env)
```bash
APP_NAME="CSW Fitness Academy"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

QUEUE_CONNECTION=sync
CACHE_DRIVER=file
SESSION_DRIVER=file

# Disable production features for local development
OLLAMA_ENABLED=false
AI_NOTIFICATIONS_ENABLED=false
```

## Running Tests Locally
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Api/AuthenticationTest.php

# Run with coverage (if you have Xdebug)
php artisan test --coverage
```

## Local Development Features
✅ All your implemented features work locally:
- PHPUnit test suite
- Database queue processing
- API rate limiting
- Security headers
- Filament admin panel
- API endpoints (46 endpoints)
- All 13 models with relationships

This is much cleaner than trying to deploy to shared hosting right away!