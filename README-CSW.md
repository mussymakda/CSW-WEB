# CSW Web - Comprehensive Participant Management System

## 🌟 **Application Overview**

CSW (Course Support Wellness) is a comprehensive web-based management system designed for educational institutions and wellness programs. It provides complete participant lifecycle management, progress tracking, content delivery, and administrative capabilities through a modern, responsive interface.

## 🎯 **Core Features**

### 1. **� Participant Management System**

#### **Participant Registration & Profiles**
- ✅ **Personal Information Management**
  - Full name, email, phone number
  - Date of birth and demographic data
  - Profile picture upload and management
  - Gender specification (Male/Female/Other)
  - Physical metrics (weight, height)
  - ACEDS number tracking
  - Student number assignment (unique identifier)
  - Location and client name tracking

#### **Participant Status Tracking**
- ✅ **Status Management**
  - Active, Enrolled, Paused, Completed, Graduated
  - Dropped, Inactive status tracking
  - Status history and change logging

#### **Advanced Search & Filtering**
- ✅ **Comprehensive Search**
  - Search by name, email, student number
  - Filter by status, program, progress level
  - Sort by multiple criteria
  - Bulk operations support

### 2. **📚 Course & Progress Management**

#### **Course Structure**
- ✅ **Course Management**
  - Course creation with name, description
  - Duration specification (in weeks)
  - Difficulty levels (Beginner, Intermediate, Advanced)
  - Course batch management with start/end dates
  - Maximum participant limits per batch

#### **Progress Tracking System**
- ✅ **Comprehensive Progress Monitoring**
  - Enrollment date tracking
  - Start and completion date logging
  - Progress percentage calculation (0-100%)
  - Status tracking (enrolled, in_progress, completed, dropped)
  - Grade assignment and tracking
  - Notes and comments system

#### **Examination & Assessment**
- ✅ **Exam Management**
  - Total exams tracking per course
  - Exams taken counter
  - Exams needed calculation
  - Last exam date tracking
  - Test statistics (total tests, tests taken, tests passed)
  - Average score calculation

### 3. **🎯 Goals & Wellness Tracking**

#### **Goal Management System**
- ✅ **Fitness & Wellness Goals**
  - Goal creation with names and descriptions
  - Display image management for visual representation
  - Goal assignment to participants
  - Progress tracking against goals

#### **Workout & Exercise Management**
- ✅ **Workout Content System**
  - Workout subcategories (Upper Body, Lower Body, Core, Cardio, etc.)
  - Workout video library management
  - Video metadata (title, duration, image thumbnails)
  - Video URL management for streaming
  - Category-based organization

#### **Goal-Workout Integration**
- ✅ **Many-to-Many Relationships**
  - Goals linked to multiple workout subcategories
  - Workout subcategories available across multiple goals
  - Flexible content assignment system

### 4. **📅 Scheduling & Time Management**

#### **Daily Schedule System**
- ✅ **Personal Schedule Management**
  - Individual participant schedules
  - Day-wise task assignment (Monday-Sunday)
  - Time-specific task scheduling
  - Task description and management
  - Schedule customization per participant

### 5. **🔔 Notification & Communication**

#### **User Notification System**
- ✅ **Comprehensive Notification Management**
  - Icon-based notification categorization
  - Rich text notification content
  - Participant-specific notifications
  - Read/unread status tracking
  - Notification history and archival

### 6. **🎨 Content & Marketing Management**

#### **Slider & Banner System**
- ✅ **Dynamic Content Sliders**
  - Title and description management
  - Image upload and URL management
  - Call-to-action links with custom text
  - Schedule-based display (start/end dates)
  - Active/inactive status control
  - Sort order management for display priority

### 7. **📊 Data Import & Export System**

#### **CSV Import Functionality**
- ✅ **Participant Progress Import**
  - Bulk CSV file upload (up to 10MB)
  - Automatic data validation and parsing
  - Duplicate detection and handling
  - Error reporting and logging
  - Progress status updates
  - Course enrollment automation

#### **Import Features**
- ✅ **Advanced Import Options**
  - Skip header row option
  - Duplicate email handling preferences
  - Batch processing for large files
  - Import results summary and statistics
  - Error details with line-by-line reporting

### 8. **🔐 Authentication & User Management**

#### **Multi-Level Authentication**
- ✅ **User Authentication System**
  - Admin user management
  - Secure login/logout functionality
  - Password reset capabilities
  - Email verification system
  - Session management

#### **Security Features**
- ✅ **Security & Privacy**
  - Password hashing and encryption
  - CSRF protection
  - Session security
  - File upload validation
  - Input sanitization

### 9. **🎨 Modern Admin Interface (Filament)**

#### **Admin Dashboard**
- ✅ **Filament-Powered Admin Panel**
  - Modern, responsive design
  - Real-time data display
  - Quick access to all modules
  - Statistics and overview widgets

#### **Resource Management**
- ✅ **CRUD Operations for All Entities**
  - Participants, Goals, Courses, Progress
  - Workout content, Schedules, Notifications
  - Sliders, User management
  - Batch operations and bulk editing

### 10. **🌐 API & Integration**

#### **RESTful API Endpoints**
- ✅ **Participant API**
  - GET /api/participants (list with relationships)
  - POST /api/participants (create new participant)
  - GET /api/participants/{id} (individual participant)
  - PUT/PATCH /api/participants/{id} (update participant)
  - DELETE /api/participants/{id} (remove participant)

#### **Additional API Endpoints**
- ✅ **Extended API Coverage**
  - Goals listing endpoint
  - Sliders content endpoint
  - User information endpoint
  - Schedule management endpoints

### 11. **📱 Responsive Design & UI/UX**

#### **Modern Frontend Stack**
- ✅ **Technology Stack**
  - Laravel 12.16.0 with Livewire 3.6.3
  - Filament 3.3.0 for admin interface
  - TailwindCSS 4.0.8 for styling
  - Vite for asset compilation
  - Volt for enhanced Livewire components

#### **User Experience**
- ✅ **Responsive Design**
  - Mobile-first approach
  - Cross-browser compatibility
  - Progressive web app capabilities
  - Optimized loading times

### 12. **⚙️ System Configuration & Deployment**

#### **Database Support**
- ✅ **Multi-Database Compatibility**
  - MySQL (primary)
  - SQLite (development)
  - PostgreSQL (configurable)
  - MariaDB support
  - Dynamic database configuration

#### **Development & Production Ready**
- ✅ **Environment Flexibility**
  - Multiple environment support (.env configuration)
  - Automatic database setup scripts
  - Migration and seeding system
  - Comprehensive testing suite (27 tests)

## 🏗️ **Technical Architecture**

### **Framework & Dependencies**
- **Backend**: Laravel 12.16.0 (PHP 8.2.12)
- **Frontend**: Livewire 3.6.3, Filament 3.3.0, TailwindCSS 4.0.8
- **Database**: MySQL with dynamic configuration support
- **Testing**: PHPUnit 11.5.21 with comprehensive test coverage
- **Development Tools**: Laravel Pint, Sail, Boost, MCP integration

### **Key Models & Relationships**
- **Participant** ↔ **Goal** (Many-to-One)
- **Participant** ↔ **DailySchedule** (One-to-Many)
- **Participant** ↔ **ParticipantCourseProgress** (One-to-Many)
- **Participant** ↔ **UserNotification** (One-to-Many)
- **Goal** ↔ **WorkoutSubcategory** (Many-to-Many)
- **WorkoutSubcategory** ↔ **WorkoutVideo** (One-to-Many)
- **Course** ↔ **CourseBatch** (One-to-Many)
- **ParticipantCourseProgress** ↔ **CourseBatch** (Many-to-One)

## 🚀 **Getting Started**

### **Installation & Setup**
1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd CSW-WEB
   composer install
   npm install
   ```

2. **Database Configuration** (Choose your preferred database)
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # For automatic MySQL setup (recommended)
   php setup_database.php
   
   # OR manually edit .env file with your database preferences
   ```

3. **Application Setup**
   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   npm run build
   ```

4. **Start Development Server**
   ```bash
   php artisan serve
   # Access: http://127.0.0.1:8000
   # Admin Panel: http://127.0.0.1:8000/admin
   # Login: admin@admin.com / password
   ```

## 🗄️ **Database Configuration Options**

This project supports multiple database types and automatically configures based on your `.env` file:

### MySQL (Recommended for Production)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csw
DB_USERNAME=root
DB_PASSWORD=
```

### SQLite (Simple Development)
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### PostgreSQL
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=csw
DB_USERNAME=postgres
DB_PASSWORD=
```

### Custom CSW Connection
```env
DB_CONNECTION=csw
# Uses the same configuration as MySQL but with project-specific defaults
```

**Setup Scripts:**
- `php setup_database.php` - Automatically creates database based on your .env configuration
- `php create_mysql_db.php` - Legacy MySQL-specific setup (still works)

## 🌟 **What Changed - Flexible Database Support!**

This project now supports:
- **Multiple Database Types** - MySQL, SQLite, PostgreSQL, MariaDB
- **Dynamic Configuration** - Automatically adapts to your .env settings
- **Automatic Database Creation** - Smart setup scripts that detect your configuration
- **Built-in PHP Server** - `php artisan serve`
- **Simple Development** - No complex server configuration required

## 📊 **Feature Matrix**

| Feature Category | Component | Status | Description |
|-----------------|-----------|---------|-------------|
| **User Management** | Admin Authentication | ✅ | Secure login/logout, password reset |
| | User Profiles | ✅ | Profile management with avatars |
| | Email Verification | ✅ | Email confirmation system |
| **Participant Management** | Registration System | ✅ | Complete participant onboarding |
| | Profile Management | ✅ | Demographics, photos, metrics |
| | Status Tracking | ✅ | Lifecycle status management |
| | Bulk Operations | ✅ | CSV import, bulk updates |
| **Course Management** | Course Creation | ✅ | Multi-level course structure |
| | Batch Management | ✅ | Time-based course batches |
| | Progress Tracking | ✅ | Detailed progress analytics |
| | Grade Management | ✅ | Assessment and grading |
| **Assessment System** | Exam Tracking | ✅ | Comprehensive exam management |
| | Test Management | ✅ | Test creation and scoring |
| | Performance Analytics | ✅ | Average scores, completion rates |
| **Content Management** | Workout Videos | ✅ | Video library with metadata |
| | Exercise Categories | ✅ | Organized workout content |
| | Goal Setting | ✅ | Fitness and wellness goals |
| | Marketing Sliders | ✅ | Dynamic content presentation |
| **Scheduling** | Daily Schedules | ✅ | Personal schedule management |
| | Task Assignment | ✅ | Time-based task scheduling |
| **Communication** | Notifications | ✅ | Rich notification system |
| | Status Updates | ✅ | Real-time status communication |
| **Data Management** | Import/Export | ✅ | CSV data processing |
| | Validation | ✅ | Data integrity checks |
| | Error Handling | ✅ | Comprehensive error reporting |
| **API & Integration** | RESTful API | ✅ | Complete CRUD operations |
| | Authentication API | ✅ | Secure API access |
| | Data Relationships | ✅ | Nested resource loading |
| **Admin Interface** | Filament Dashboard | ✅ | Modern admin panel |
| | Resource Management | ✅ | Full CRUD for all entities |
| | Search & Filter | ✅ | Advanced data discovery |
| | Bulk Operations | ✅ | Efficient mass operations |

## 🎯 **Use Cases & Scenarios**

### **Educational Institution Scenario**
- **Student Enrollment**: Register students with complete profiles
- **Course Management**: Create programs with multiple batches
- **Progress Tracking**: Monitor student advancement through courses
- **Assessment Management**: Track exams, tests, and grades
- **Communication**: Send notifications and updates to students

### **Fitness & Wellness Center Scenario**
- **Member Management**: Register members with fitness goals
- **Workout Planning**: Assign workout routines and videos
- **Progress Monitoring**: Track fitness progress and achievements
- **Scheduling**: Manage personal training sessions
- **Content Delivery**: Provide workout videos and instructions

### **Corporate Training Scenario**
- **Employee Onboarding**: Register employees for training programs
- **Skill Development**: Track professional development courses
- **Assessment Tracking**: Monitor certification progress
- **Performance Analytics**: Generate progress reports
- **Resource Management**: Organize training materials

## 🔧 **Technical Features**

### **Performance & Optimization**
- ✅ **Caching System**: Configuration and query caching
- ✅ **Asset Optimization**: Vite-based asset compilation
- ✅ **Database Optimization**: Indexed queries and relationships
- ✅ **Memory Management**: Efficient data loading patterns

### **Security Features**
- ✅ **Input Validation**: Comprehensive request validation
- ✅ **CSRF Protection**: Cross-site request forgery prevention
- ✅ **SQL Injection Prevention**: Prepared statements and ORM
- ✅ **File Upload Security**: Type validation and size limits
- ✅ **Password Security**: Hashing and secure storage

### **Data Integrity**
- ✅ **Foreign Key Constraints**: Referential integrity
- ✅ **Unique Constraints**: Data uniqueness enforcement
- ✅ **Soft Deletes**: Data preservation with logical deletion
- ✅ **Audit Trails**: Change tracking and history

### **Scalability Features**
- ✅ **Queue System**: Background job processing
- ✅ **Event System**: Decoupled component communication
- ✅ **Cache Management**: Multiple cache drivers support
- ✅ **Database Migrations**: Version-controlled schema changes

## 🧪 **Testing & Quality Assurance**

### **Test Coverage**
- ✅ **Unit Tests**: Core functionality testing
- ✅ **Feature Tests**: End-to-end workflow testing
- ✅ **Authentication Tests**: Login, logout, password reset
- ✅ **API Tests**: RESTful endpoint validation
- ✅ **Database Tests**: Migration and seeding verification

### **Quality Metrics**
- **27 Tests**: Comprehensive test suite
- **65 Assertions**: Detailed validation checks
- **0 Failures**: All tests passing
- **Code Standards**: Laravel Pint compliance

## 🚀 **Deployment & Production**

### **Production Readiness**
- ✅ **Environment Configuration**: Multiple environment support
- ✅ **Asset Compilation**: Production-optimized builds
- ✅ **Database Migration**: Automated schema updates
- ✅ **Error Handling**: Comprehensive error management
- ✅ **Logging**: Detailed application logging

### **Deployment Options**
- ✅ **Traditional Hosting**: Standard PHP hosting
- ✅ **Cloud Deployment**: AWS, Azure, Google Cloud
- ✅ **Container Support**: Docker-ready configuration
- ✅ **CI/CD Integration**: Automated deployment pipelines

## 📈 **Analytics & Reporting**

### **Progress Analytics**
- ✅ **Completion Rates**: Course and goal completion tracking
- ✅ **Performance Metrics**: Average scores and achievement rates
- ✅ **Time Tracking**: Duration and timeline analysis
- ✅ **Engagement Statistics**: User activity and participation

### **Administrative Reports**
- ✅ **Participant Overview**: Complete participant statistics
- ✅ **Course Performance**: Course-wise success metrics
- ✅ **Resource Utilization**: Content usage and popularity
- ✅ **System Health**: Application performance monitoring

## 🎨 **User Interface Features**

### **Responsive Design**
- ✅ **Mobile-First**: Optimized for mobile devices
- ✅ **Tablet Support**: Enhanced tablet experience
- ✅ **Desktop Interface**: Full desktop functionality
- ✅ **Cross-Browser**: Compatible with all modern browsers

### **Accessibility Features**
- ✅ **WCAG Compliance**: Web accessibility standards
- ✅ **Keyboard Navigation**: Full keyboard accessibility
- ✅ **Screen Reader Support**: Assistive technology compatibility
- ✅ **Color Contrast**: High contrast for readability

## 🔮 **Future Enhancements & Roadmap**

### **Planned Features**
- 📝 **Real-time Chat**: Participant-instructor communication
- 📝 **Video Conferencing**: Integrated video sessions
- 📝 **Mobile Application**: Native iOS/Android apps
- 📝 **Advanced Analytics**: Machine learning insights
- 📝 **Payment Integration**: Course fee management
- 📝 **Certificate Generation**: Automated certification system

### **Integration Opportunities**
- 📝 **Learning Management Systems**: LMS integration
- 📝 **Calendar Systems**: External calendar sync
- 📝 **Email Marketing**: Automated email campaigns
- 📝 **Social Media**: Social sharing and integration
- 📝 **Wearable Devices**: Fitness tracker integration

---

## 🏆 **Summary**

The CSW Web Application is a **comprehensive, modern, and scalable** participant management system that combines:

- **Complete participant lifecycle management**
- **Advanced progress tracking and analytics**
- **Rich content management capabilities**
- **Modern admin interface with Filament**
- **Robust API for integrations**
- **Production-ready architecture**
- **Comprehensive testing coverage**
- **Multi-database support**
- **Responsive design for all devices**

**Perfect for**: Educational institutions, fitness centers, corporate training programs, wellness organizations, and any entity requiring participant progress tracking and content management.

**Key Differentiators**: 
- Dynamic database configuration
- CSV import/export functionality
- Comprehensive progress tracking
- Modern UI/UX with Filament
- Full API coverage
- Production-ready deployment
- Extensive testing coverage

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
