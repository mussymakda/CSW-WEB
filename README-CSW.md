# CSW Web - Participant Management System

## 🚀 Quick Start (No XAMPP Required!)

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM

### Optional Extensions
- `intl` PHP extension (for advanced date formatting - not required)

### Easy Development Setup

1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd CSW-WEB
   composer install
   npm install
   ```

2. **One-Command Setup** (Windows)
   ```bash
   ./start-dev.bat         # Command Prompt
   ./start-dev.ps1         # PowerShell
   ```

3. **Access the Application**
   - **Admin Panel**: http://127.0.0.1:8000/admin
   - **Login**: admin@csw.com / password

## 🌟 What Changed - No More XAMPP!

This project now uses:
- **SQLite Database** - File-based, no server setup needed
- **Built-in PHP Server** - `php artisan serve`
- **Automatic Setup Scripts** - One command to get started
- **Simple Development** - No complex server configuration

## 📊 Features

### Excel Import System
- Upload participant progress data via CSV/Excel
- Handles duplicate emails gracefully
- Batch processing for large files
- Real-time error reporting and validation

### Admin Dashboard
- Participant management with progress tracking
- Analytics widgets showing completion rates
- Mobile app slider management
- Course and batch management

## Overview
This system manages participants for a fitness/wellness application with goals and daily schedules.

## Admin Panel
Access the Filament admin panel at: `http://localhost:8000/admin`

**Admin Credentials:**
- Email: admin@csw.com  
- Password: password

## Database Structure

### Participants Table
- `id` - Primary key
- `name` - Participant name
- `email` - Unique email address
- `phone` - Phone number (optional)
- `dob` - Date of birth (optional)
- `profile_picture` - Profile image path (optional)
- `gender` - Enum: male, female, other (optional)
- `weight` - Weight in kg (optional)
- `height` - Height in meters (optional)
- `aceds_no` - ACEDS number (optional)
- `goal_id` - Foreign key to goals table (optional)

### Goals Table
- `id` - Primary key
- `name` - Goal name (e.g., "Weight Loss", "Muscle Gain")
- `display_image` - Goal image path (optional)

### Daily Schedules Table
- `id` - Primary key
- `participant_id` - Foreign key to participants table
- `task` - Task description
- `time` - Time in HH:MM format
- `day` - Enum: monday, tuesday, wednesday, thursday, friday, saturday, sunday

## API Endpoints

Base URL: `http://localhost:8000/api`

### Participants
- `GET /participants` - List all participants with goals and schedules
- `POST /participants` - Create new participant
- `GET /participants/{id}` - Get specific participant with relationships
- `PUT /participants/{id}` - Update participant
- `DELETE /participants/{id}` - Delete participant

### Goals
- `GET /goals` - List all available goals

### Daily Schedules
- `GET /participants/{id}/schedules` - Get all schedules for a participant
- `POST /participants/{id}/schedules` - Add new schedule for a participant

## Admin Features

### Participant Management
- View, create, edit, and delete participants
- Upload profile pictures
- Assign goals to participants
- Manage daily schedules directly within participant records

### Goal Management
- Create and manage fitness goals
- Upload goal images
- View participant count per goal

### Daily Schedule Management
- Schedules are managed as part of participant records
- Add multiple schedule entries per participant
- Color-coded by day of the week
- Time-based sorting

## Sample Data
The system comes pre-seeded with:
- 5 sample goals (Weight Loss, Muscle Gain, etc.)
- 10 sample participants with random data
- Multiple daily schedule entries per participant

## File Storage
- Profile pictures: `storage/app/public/profiles/`
- Goal images: `storage/app/public/goals/`
- Accessible via: `http://localhost:8000/storage/profiles/` and `http://localhost:8000/storage/goals/`

## Features Implemented
✅ Participant model with all required fields
✅ Goal model with display images
✅ Daily schedule model linked to participants
✅ Filament admin panel with full CRUD operations
✅ File upload handling for images
✅ API endpoints for app-level access
✅ Data seeding with sample records
✅ Proper relationships between models
✅ Image storage and public access
✅ Admin user creation
✅ Form validation and proper field types
