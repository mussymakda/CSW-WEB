# API Documentation - Flutter Authentication, Onboarding & Goals

## Mobile App APIs

### 1. Get Schedule

**Endpoint:** `GET /api/mobile/schedule`
**Authentication:** Required (Bearer Token)
**Description:** Get participant's daily schedule for a specific date

**Query Parameters:** None

**Authentication Required:** Bearer Token via `/api/auth/login`

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "date": "2025-10-01",
        "day": "tuesday",
        "schedules": [
            {
                "id": 1,
                "task": "Morning Workout",
                "time": "06:30",
                "day": "tuesday",
                "is_completed": false,
                "completed_at": null,
                "completion_notes": null,
                "priority": "high",
                "category": "fitness",
                "location": "Gym"
            }
        ]
    }
}
```

### 2. Get Progress Card

**Endpoint:** `GET /api/mobile/progress-card`
**Authentication:** Required (Bearer Token)
**Description:** Get participant's current course progress information

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "has_active_course": true,
        "progress_percentage": 65.50,
        "course_name": "Advanced Fitness Training",
        "batch_name": "AFT-2025-001",
        "expected_completion_date": "2025-12-15",
        "enrollment_date": "2025-09-01",
        "status": "active",
        "tests_progress": {
            "total": 10,
            "taken": 6,
            "passed": 5
        },
        "exams_progress": {
            "total": 3,
            "taken": 1,
            "needed": 2
        }
    }
}
```

### 3. Get Mobile Sliders

**Endpoint:** `GET /api/mobile/sliders`
**Authentication:** Required (Bearer Token)
**Description:** Get active slider content for mobile app

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "sliders": [
            {
                "id": 1,
                "title": "New Workout Program",
                "description": "Join our latest fitness challenge",
                "image_url": "http://localhost:8000/storage/sliders/image1.jpg",
                "link_url": "https://example.com/program",
                "link_text": "Learn More",
                "start_date": "2025-10-01",
                "end_date": "2025-10-31",
                "sort_order": 1
            }
        ]
    }
}
```

### 4. Get Suggested Workouts

**Endpoint:** `GET /api/mobile/suggested-workouts`
**Authentication:** Required (Bearer Token)
**Description:** Get top 5 suggested workout categories for the participant

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "suggested_workouts": [
            {
                "id": 1,
                "title": "Cardio Training",
                "info": "High intensity cardio workouts",
                "image_url": "http://localhost:8000/storage/workouts/cardio.jpg",
                "related_goals": [
                    {
                        "id": 1,
                        "name": "Weight Loss"
                    }
                ]
            }
        ],
        "total_count": 5
    }
}
```

## First-Time Login Flow

The onboarding process for new participants follows these steps:

1. **Send OTP** - Request OTP via email
2. **Verify OTP & Set Password** - Verify OTP and set new password
3. **Accept Terms** - Accept terms and conditions
4. **Update Profile** - Provide personal information (optional)
5. **Select Goals** - Choose multiple fitness goals
6. **Set Weight** - Provide current weight (optional)
7. **Set Height** - Provide current height (optional)
8. **Complete Onboarding** - Mark onboarding as complete

## Onboarding Endpoints

### 1. Send OTP (Public)
**POST** `/api/onboarding/send-otp`

**Request Body:**
```json
{
    "email": "participant@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP sent successfully to your email",
    "data": {
        "email": "participant@example.com",
        "expires_in_minutes": 10
    }
}
```

### 2. Verify OTP & Set Password (Public)
**POST** `/api/onboarding/verify-otp`

**Request Body:**
```json
{
    "email": "participant@example.com",
    "otp": "123456",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "OTP verified and password updated successfully",
    "data": {
        "participant": {...},
        "token": "1|plainTextTokenHere...",
        "needs_onboarding": true
    }
}
```

### 3. Get Onboarding Status (Protected)
**GET** `/api/onboarding/status`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "status": {
            "email_verified": true,
            "password_changed": true,
            "terms_accepted": false,
            "profile_completed": false,
            "goals_selected": false,
            "weight_provided": false,
            "height_provided": false,
            "onboarding_completed": false
        },
        "needs_onboarding": true,
        "participant": {...}
    }
}
```

### 4. Accept Terms (Protected)
**POST** `/api/onboarding/accept-terms`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Terms and conditions accepted successfully",
    "data": {
        "terms_accepted": true,
        "terms_accepted_at": "2025-10-01T12:30:00.000000Z",
        "needs_onboarding": true
    }
}
```

### 5. Update Profile (Protected)
**POST** `/api/onboarding/update-profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body (JSON or Form Data):**
```json
{
    "name": "John Doe",
    "email": "new@example.com",
    "phone": "+1234567890",
    "dob": "1990-01-01",
    "gender": "male",
    "profile_picture": "multipart/form-data file upload (optional)"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "participant": {
            "id": 1,
            "name": "John Doe",
            "email": "new@example.com",
            "phone": "+1234567890",
            "dob": "1990-01-01",
            "gender": "male",
            "profile_picture": "profiles/abc123.jpg",
            "profile_picture_url": "http://localhost:8000/storage/profiles/abc123.jpg"
        },
        "needs_onboarding": true
    }
}
```

### 5a. Update Profile Picture (Protected)
**POST** `/api/onboarding/update-profile-picture`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
profile_picture: [image file] (JPEG, PNG, JPG, GIF, WEBP - max 5MB)
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile picture updated successfully",
    "data": {
        "profile_picture": "profiles/abc123.jpg",
        "profile_picture_url": "http://localhost:8000/storage/profiles/abc123.jpg",
        "needs_onboarding": true
    }
}
```

### 6. Select Goals (Protected)
**POST** `/api/onboarding/select-goals`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "goal_ids": [1, 3, 5]
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Goals selected successfully",
    "data": {
        "selected_goals": [...],
        "primary_goal": {...},
        "needs_onboarding": true
    }
}
```

### 7. Update Weight (Protected)
**POST** `/api/onboarding/update-weight`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "weight": 75.5
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Weight updated successfully",
    "data": {
        "weight": 75.5,
        "needs_onboarding": true
    }
}
```

### 8. Update Height (Protected)
**POST** `/api/onboarding/update-height`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "height": 1.75
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Height updated successfully",
    "data": {
        "height": 1.75,
        "needs_onboarding": true
    }
}
```

### 9. Complete Onboarding (Protected)
**POST** `/api/onboarding/complete`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Onboarding completed successfully",
    "data": {
        "participant": {...},
        "onboarding_completed": true,
        "onboarding_completed_at": "2025-10-01T12:45:00.000000Z"
    }
}
```

## Authentication Endpoints

### 1. Login
**POST** `/api/auth/login`

**Request Body:**
```json
{
    "email": "participant@example.com",
    "password": "password"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "participant": {
            "id": 1,
            "name": "John Doe",
            "email": "participant@example.com",
            "student_number": "STU12345",
            "goal": {...}
        },
        "token": "1|plainTextTokenHere..."
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

### 2. Logout
**POST** `/api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logout successful"
}
```

### 3. Profile
**GET** `/api/auth/profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "participant@example.com",
        "student_number": "STU12345",
        "phone": "+1234567890",
        "location": "City Name",
        "client_name": "Company Name",
        "dob": "1990-01-01",
        "profile_picture": "profiles/avatar1.jpg",
        "gender": "male",
        "weight": 75.5,
        "height": 1.75,
        "aceds_no": "ACEDS1234",
        "program_description": "Certified Personal Trainer (CPT)",
        "status": "active"
    }
}
```

### 4. Forgot Password
**POST** `/api/auth/forgot-password`

**Request Body:**
```json
{
    "email": "participant@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password reset email sent successfully"
}
```

### 5. Reset Password
**POST** `/api/auth/reset-password`

**Request Body:**
```json
{
    "email": "participant@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123",
    "token": "reset_token_here"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password reset successfully"
}
```

## Goals API

### 1. Get All Goals
**GET** `/api/goals`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Goals retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Weight Loss",
            "display_image": "goals/weight-loss.jpg",
            "display_image_url": "http://localhost:8000/storage/goals/weight-loss.jpg",
            "workout_subcategories": [
                {
                    "id": 1,
                    "title": "Cardio Blast",
                    "info": "High-intensity cardio workouts...",
                    "image": "subcategories/cardio.jpg",
                    "image_url": "http://localhost:8000/storage/subcategories/cardio.jpg",
                    "workout_videos": [
                        {
                            "id": 1,
                            "title": "Morning Cardio",
                            "description": "Start your day with energy...",
                            "video_url": "videos/morning-cardio.mp4",
                            "thumbnail_url": "thumbnails/morning-cardio.jpg",
                            "duration": "00:15:30"
                        }
                    ]
                }
            ]
        }
    ]
}
```

### 2. Get Specific Goal
**GET** `/api/goals/{goalId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Goal retrieved successfully",
    "data": {
        "id": 1,
        "name": "Weight Loss",
        "display_image": "goals/weight-loss.jpg",
        "display_image_url": "http://localhost:8000/storage/goals/weight-loss.jpg",
        "workout_subcategories": [...]
    }
}
```

## Test Credentials

Use these credentials to test the API:

**Email:** `prohaska.lilla@example.org`  
**Password:** `password`

## Authentication Flow

1. **Login** with email/password to get a token
2. **Use the token** in the Authorization header for all protected endpoints
3. **Token never expires** (as requested)
4. **Logout** to revoke the current token

## Base URL

Development: `http://localhost:8000/api`

## Notes

- All API responses follow the same structure with `success`, `message`, and `data` fields
- Tokens are generated using Laravel Sanctum with no expiration
- Password reset functionality uses Laravel's built-in password reset system
- All protected routes require the `Authorization: Bearer {token}` header
- The participant model includes fitness-related fields like weight, height, and fitness goals