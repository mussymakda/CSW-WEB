# 🏃‍♂️ CSW Mobile App API Documentation

## 📋 Table of Contents
- [📱 Mobile App APIs](#-mobile-app-apis)
  - [🗓️ Get Schedule](#️-get-schedule)
  - [📊 Get Progress Card](#-get-progress-card)
  - [🎨 Get Sliders](#-get-sliders)
  - [💪 Get Suggested Workouts](#-get-suggested-workouts)
  - [💡 Get Guidance Tips](#-get-guidance-tips)
  - [🎯 Get Workout Details](#-get-workout-details)
  - [📹 Log Video View](#-log-video-view)
  - [📈 Get Workout History](#-get-workout-history)
  - [🔔 Get Notifications](#-get-notifications)
  - [📧 Contact Us](#-contact-us)
- [🔐 Authentication & Onboarding](#-authentication--onboarding)
- [👤 User Profile Management](#-user-profile-management)
- [❌ Error Codes Reference](#-error-codes-reference)

---

## 🔐 Base Configuration
**Production Base URL:** `https://your-domain.com/api`  
**Staging Base URL:** `https://staging.your-domain.com/api`

**Default Headers:**
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {your_access_token}
```

### 📱 Mobile App Configuration
**Production Environment:**
```dart
final baseUrl = 'https://your-domain.com/api';
```

**Staging Environment:**
```dart
final baseUrl = 'https://staging.your-domain.com/api';
```

### 🧪 Testing
For testing purposes, create test accounts through the registration process.

**Example API Request:**
```bash
curl -X GET "https://your-domain.com/api/mobile/schedule" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {your_access_token}"
```

**Get Authentication Token:**
1. Login via `/api/auth/login` endpoint
2. Use the returned token in the `Authorization: Bearer {token}` header
3. Token expires based on your Sanctum configuration

---

## 📱 Mobile App APIs

### 🗓️ Get Schedule
Get all participant schedules without date filtering

```http
GET /api/mobile/schedule
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Request Parameters:** None

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "schedules": [
            {
                "id": 1,
                "task": "Morning Workout",
                "time": "06:30",
                "day": "monday",
                "is_completed": false,
                "completed_at": null,
                "completion_notes": null,
                "priority": "high",
                "category": "fitness",
                "location": "Gym"
            },
            {
                "id": 2,
                "task": "Evening Yoga",
                "time": "19:00",
                "day": "monday",
                "is_completed": true,
                "completed_at": "2025-10-01 19:45:00",
                "completion_notes": "Great session!",
                "priority": "medium",
                "category": "mindfulness",
                "location": "Home"
            }
        ]
    }
}
```
</details>

<details>
<summary><b>❌ 500 Server Error</b></summary>

```json
{
    "success": false,
    "message": "Unable to load your schedule at the moment. Please try again later.",
    "error_code": "SCHEDULE_FETCH_ERROR"
}
```
</details>

---

### 📊 Get Progress Card
Get participant's current course progress information

```http
GET /api/mobile/progress-card
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success (Active Course)</b></summary>

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
</details>

<details>
<summary><b>✅ 200 Success (No Active Course)</b></summary>

```json
{
    "success": true,
    "data": {
        "has_active_course": false,
        "message": "No active course found"
    }
}
```
</details>

---

### 🎨 Get Sliders
Get active slider content for mobile app

```http
GET /api/mobile/sliders
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "sliders": [
            {
                "id": 1,
                "title": "New Workout Program",
                "description": "Join our latest fitness challenge",
                "image_url": "https://your-domain.com/storage/sliders/image1.jpg",
                "link_url": "https://example.com/program",
                "link_text": "Learn More",
                "start_date": "2025-10-01",
                "end_date": "2025-10-31",
                "sort_order": 1
            },
            {
                "id": 2,
                "title": "Nutrition Tips",
                "description": "Discover healthy eating habits",
                "image_url": "https://your-domain.com/storage/sliders/nutrition.jpg",
                "link_url": "https://example.com/nutrition",
                "link_text": "Read More",
                "start_date": "2025-09-15",
                "end_date": "2025-11-15",
                "sort_order": 2
            }
        ]
    }
}
```
</details>

---

### 💪 Get Suggested Workouts
Get top 5 suggested workout categories for the participant

```http
GET /api/mobile/suggested-workouts
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "suggested_workouts": [
            {
                "id": 1,
                "title": "Cardio Training",
                "info": "High intensity cardio workouts",
                "image_url": "https://your-domain.com/storage/workouts/cardio.jpg",
                "related_goals": [
                    {
                        "id": 1,
                        "name": "Weight Loss"
                    },
                    {
                        "id": 3,
                        "name": "Improve Endurance"
                    }
                ]
            },
            {
                "id": 2,
                "title": "Strength Training",
                "info": "Build muscle and increase strength",
                "image_url": "https://your-domain.com/storage/workouts/strength.jpg",
                "related_goals": [
                    {
                        "id": 2,
                        "name": "Build Muscle"
                    }
                ]
            }
        ],
        "total_count": 5
    }
}
```
</details>

---

### 💡 Get Guidance Tips
Get guidance tips for mobile app webview

```http
GET /api/mobile/guidance-tips
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "guidance_tips": [
            {
                "id": 1,
                "name": "Beginner's Guide to Fitness",
                "image_url": "https://your-domain.com/storage/guidance-tips/guide1.jpg",
                "link": "https://example.com/beginners-guide",
                "sort_order": 1
            },
            {
                "id": 2,
                "name": "Nutrition Basics",
                "image_url": "https://your-domain.com/storage/guidance-tips/nutrition.jpg",
                "link": "https://example.com/nutrition-basics",
                "sort_order": 2
            }
        ]
    }
}
```
</details>

---

### 🎯 Get Workout Details
Get workout subcategory details with videos grouped by duration

```http
GET /api/mobile/workout-details/{subcategoryId}
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| subcategoryId | integer | Yes | Workout subcategory ID |

**Example Request:**
```http
GET /api/mobile/workout-details/1
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "workout_subcategory": {
            "id": 1,
            "title": "Cardio Training",
            "info": "<h3>High Intensity Cardio</h3><p>Burn calories and improve cardiovascular health with these dynamic workouts.</p>",
            "image_url": "https://your-domain.com/storage/workouts/cardio.jpg"
        },
        "videos_by_duration": {
            "all": [
                {
                    "id": 1,
                    "title": "Quick Cardio Burst",
                    "duration_minutes": 5,
                    "duration_formatted": "5m",
                    "video_url": "https://example.com/video1.mp4",
                    "image_url": "https://your-domain.com/storage/videos/thumb1.jpg",
                    "is_viewed": false
                },
                {
                    "id": 2,
                    "title": "Intense HIIT Session",
                    "duration_minutes": 15,
                    "duration_formatted": "15m",
                    "video_url": "https://example.com/video2.mp4",
                    "image_url": "https://your-domain.com/storage/videos/thumb2.jpg",
                    "is_viewed": true
                }
            ],
            "5m": [
                {
                    "id": 1,
                    "title": "Quick Cardio Burst",
                    "duration_minutes": 5,
                    "duration_formatted": "5m",
                    "video_url": "https://example.com/video1.mp4",
                    "image_url": "https://your-domain.com/storage/videos/thumb1.jpg",
                    "is_viewed": false
                }
            ],
            "10m": [],
            "15m": [
                {
                    "id": 2,
                    "title": "Intense HIIT Session",
                    "duration_minutes": 15,
                    "duration_formatted": "15m",
                    "video_url": "https://example.com/video2.mp4",
                    "image_url": "https://your-domain.com/storage/videos/thumb2.jpg",
                    "is_viewed": true
                }
            ],
            "20m": [],
            "30m": [],
            "45m": [],
            "50m": []
        }
    }
}
```
</details>

<details>
<summary><b>❌ 400 Bad Request</b></summary>

```json
{
    "success": false,
    "message": "Please provide a valid workout category.",
    "error_code": "INVALID_WORKOUT_ID"
}
```
</details>

<details>
<summary><b>❌ 404 Not Found</b></summary>

```json
{
    "success": false,
    "message": "The requested workout category was not found.",
    "error_code": "WORKOUT_NOT_FOUND"
}
```
</details>

---

### 📹 Log Video View
Log when participant views a workout video

```http
POST /api/mobile/log-video-view
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "workout_video_id": 1,
    "duration_watched_seconds": 120
}
```

**Body Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| workout_video_id | integer | Yes | ID of the workout video |
| duration_watched_seconds | integer | No | Seconds watched (0-7200) |

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Video view logged successfully",
    "data": {
        "video_view_id": 15
    }
}
```
</details>

<details>
<summary><b>❌ 422 Validation Error</b></summary>

```json
{
    "success": false,
    "message": "Unable to track video progress.",
    "error_code": "VIDEO_TRACKING_VALIDATION_ERROR",
    "errors": {
        "workout_video_id": [
            "The specified video was not found."
        ]
    }
}
```
</details>

---

### 📈 Get Workout History
Get daily workout history with viewed videos grouped by subcategory

```http
GET /api/mobile/workout-history
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "workout_history": [
            {
                "date": "2025-10-01",
                "workout_subcategories": [
                    {
                        "subcategory": {
                            "id": 1,
                            "title": "Cardio Training",
                            "info": "High intensity cardio workouts",
                            "image_url": "https://your-domain.com/storage/workouts/cardio.jpg"
                        },
                        "videos_watched": [
                            {
                                "id": 1,
                                "title": "Quick Cardio Burst",
                                "duration_minutes": 5,
                                "duration_formatted": "5m",
                                "duration_watched_seconds": 300,
                                "viewed_at": "08:30"
                            },
                            {
                                "id": 2,
                                "title": "HIIT Session",
                                "duration_minutes": 15,
                                "duration_formatted": "15m",
                                "duration_watched_seconds": 900,
                                "viewed_at": "09:00"
                            }
                        ]
                    },
                    {
                        "subcategory": {
                            "id": 2,
                            "title": "Strength Training",
                            "info": "Build muscle and strength",
                            "image_url": "https://your-domain.com/storage/workouts/strength.jpg"
                        },
                        "videos_watched": [
                            {
                                "id": 5,
                                "title": "Upper Body Workout",
                                "duration_minutes": 20,
                                "duration_formatted": "20m",
                                "duration_watched_seconds": 1200,
                                "viewed_at": "18:00"
                            }
                        ]
                    }
                ]
            },
            {
                "date": "2025-09-30",
                "workout_subcategories": [
                    {
                        "subcategory": {
                            "id": 1,
                            "title": "Cardio Training",
                            "info": "High intensity cardio workouts",
                            "image_url": "https://your-domain.com/storage/workouts/cardio.jpg"
                        },
                        "videos_watched": [
                            {
                                "id": 3,
                                "title": "Morning Run Simulation",
                                "duration_minutes": 30,
                                "duration_formatted": "30m",
                                "duration_watched_seconds": 1800,
                                "viewed_at": "07:00"
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```
</details>

---

### 🔔 Get Notifications
Get notifications that are current or past (ready to show)

```http
GET /api/mobile/notifications
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "id": 1,
                "title": "Workout Reminder",
                "message": "Time for your daily cardio workout!",
                "type": "workout_reminder",
                "scheduled_for": "2025-10-01 08:00:00",
                "is_read": false,
                "read_at": null
            },
            {
                "id": 2,
                "title": "Progress Update",
                "message": "Great job! You've completed 65% of your course.",
                "type": "progress_update",
                "scheduled_for": "2025-10-01 12:00:00",
                "is_read": true,
                "read_at": "2025-10-01 12:05:00"
            }
        ]
    }
}
```
</details>

---

### 📧 Contact Us
Send contact us message with optional file attachment

```http
POST /api/mobile/contact-us
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data
```

**Form Data Parameters:**
| Parameter | Type | Required | Max Size | Allowed Types | Description |
|-----------|------|----------|----------|---------------|-------------|
| title | string | Yes | 255 chars | - | Subject/title of the message |
| email | string | Yes | 255 chars | - | Participant's email address |
| description | string | Yes | 5000 chars | - | Detailed message description |
| attachment | file | No | 10MB | PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, TXT, ZIP | Optional file attachment |

**Example Request Body:**
```
title: "App Issue - Unable to complete workout"
email: "participant@example.com"
description: "I'm having trouble completing my workout videos. The app crashes when I try to play videos longer than 10 minutes. This happens consistently on my Android device."
attachment: [FILE: screenshot.jpg]
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Your message has been sent successfully! We will get back to you within 24 hours.",
    "data": {
        "submitted_at": "2025-10-01 14:30:00",
        "reference_id": "CSW-123-1696168200"
    }
}
```
</details>

<details>
<summary><b>❌ 422 Validation Error</b></summary>

```json
{
    "success": false,
    "message": "Please check your input and try again.",
    "error_code": "VALIDATION_ERROR",
    "errors": {
        "title": [
            "Please provide a subject for your message."
        ],
        "description": [
            "Description must be at least 10 characters long."
        ],
        "email": [
            "Please provide a valid email address."
        ]
    }
}
```
</details>

<details>
<summary><b>❌ 422 File Upload Error</b></summary>

```json
{
    "success": false,
    "message": "Unable to process your attachment. Please try with a different file or contact us without an attachment.",
    "error_code": "ATTACHMENT_UPLOAD_ERROR"
}
```
</details>

<details>
<summary><b>❌ 503 Service Unavailable</b></summary>

```json
{
    "success": false,
    "message": "We are currently experiencing technical difficulties. Please try again later or contact us directly.",
    "error_code": "EMAIL_SEND_ERROR"
}
```
</details>

---

## 🔐 Authentication & Onboarding

### 🔑 Login
Authenticate participant and get access token

```http
POST /api/auth/login
Host: your-domain.com
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "participant@example.com",
    "password": "password123"
}
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "participant": {
            "id": 1,
            "name": "John Doe",
            "email": "participant@example.com",
            "phone": "+1234567890",
            "email_verified_at": "2025-10-01 10:00:00",
            "onboarding_completed": false
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "needs_onboarding": true
    }
}
```
</details>

### 📱 Send OTP
Send OTP for first-time login verification

```http
POST /api/onboarding/send-otp
Host: your-domain.com
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "participant@example.com"
}
```

### ✅ Verify OTP & Set Password
Verify OTP and set new password

```http
POST /api/onboarding/verify-otp
Host: your-domain.com
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "participant@example.com",
    "otp": "123456",
    "new_password": "newSecurePassword123",
    "new_password_confirmation": "newSecurePassword123"
}
```

### 📄 Accept Terms
Accept terms and conditions

```http
POST /api/onboarding/accept-terms
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "terms_accepted": true
}
```

### 👤 Update Profile
Update participant profile information

```http
POST /api/onboarding/update-profile
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data
```

**Form Data:**
```
name: "John Doe"
email: "new@example.com"
phone: "+1234567890"
dob: "1990-01-01"
gender: "male"
profile_picture: [FILE: photo.jpg]
```

### 🎯 Select Goals
Choose multiple fitness goals

```http
POST /api/onboarding/select-goals
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "goal_ids": [1, 3, 5]
}
```

### ⚖️ Update Weight
Set current weight

```http
POST /api/onboarding/update-weight
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "weight_kg": 75.5
}
```

### 📏 Update Height
Set current height

```http
POST /api/onboarding/update-height
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "height_cm": 180
}
```

### ✨ Complete Onboarding
Mark onboarding as complete

```http
POST /api/onboarding/complete
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

---

## 👤 User Profile Management

### 📋 Get User Profile
Get authenticated user's complete profile information

```http
GET /api/user/profile
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "prohaska.lilla@example.org",
        "phone": "81605 53161",
        "name": "Mustansir Makda",
        "profile_picture": "http://your-domain.com/storage/profile-pictures/photo.jpg",
        "date_of_birth": "04/10/1994",
        "gender": "male",
        "height": 180,
        "weight": 75.5,
        "fitness_level": "intermediate",
        "goals": [
            {
                "id": 1,
                "name": "Weight Loss",
                "description": "Lose weight and improve fitness"
            }
        ],
        "is_onboarding_completed": true,
        "created_at": "2025-10-01T10:00:00.000000Z",
        "updated_at": "2025-10-01T14:30:00.000000Z"
    }
}
```
</details>

---

### ✏️ Update User Profile
Update authenticated user's profile information

```http
PUT /api/user/profile
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Mustansir Makda",
    "email": "mustansir.makda@gmail.com",
    "phone": "81605 53161",
    "date_of_birth": "04/10/1994",
    "gender": "male",
    "height": 180,
    "weight": 75.5,
    "fitness_level": "intermediate",
    "goal_ids": [1, 3]
}
```

**Body Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | No | Full name (max 255 chars) |
| email | string | No | Email address (must be unique) |
| phone | string | No | Phone number (max 20 chars) |
| date_of_birth | string | No | Date in dd/mm/yyyy format |
| gender | string | No | male, female, other, prefer_not_to_say |
| height | number | No | Height in centimeters (100-250) |
| weight | number | No | Weight in kilograms (20-500) |
| fitness_level | string | No | beginner, intermediate, advanced |
| goal_ids | array | No | Array of goal IDs |

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "id": 1,
        "email": "mustansir.makda@gmail.com",
        "phone": "81605 53161",
        "name": "Mustansir Makda",
        "profile_picture": null,
        "date_of_birth": "04/10/1994",
        "gender": "male",
        "height": 180,
        "weight": 75.5,
        "fitness_level": "intermediate",
        "goals": [
            {
                "id": 1,
                "name": "Weight Loss",
                "description": "Lose weight and improve fitness"
            },
            {
                "id": 3,
                "name": "Build Muscle",
                "description": "Increase muscle mass and strength"
            }
        ],
        "is_onboarding_completed": true,
        "updated_at": "2025-10-01T14:35:00.000000Z"
    }
}
```
</details>

---

### 📸 Upload Profile Picture
Upload or update user's profile picture

```http
POST /api/user/profile/picture
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Content-Type: multipart/form-data
```

**Form Data:**
```
profile_picture: [FILE: image.jpg]
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Profile picture updated successfully",
    "data": {
        "profile_picture": "http://your-domain.com/storage/profile-pictures/abc123.jpg"
    }
}
```
</details>

---

### 🗑️ Delete Profile Picture
Remove user's profile picture

```http
DELETE /api/user/profile/picture
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "message": "Profile picture removed successfully",
    "data": {
        "profile_picture": null
    }
}
```
</details>

---

### ⚙️ Get Account Setup Data
Get available options for account setup (goals, fitness levels, etc.)

```http
GET /api/user/setup-data
Host: your-domain.com
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Response Examples:**

<details>
<summary><b>✅ 200 Success</b></summary>

```json
{
    "success": true,
    "data": {
        "goals": [
            {
                "id": 1,
                "name": "Weight Loss",
                "description": "Lose weight and improve fitness"
            },
            {
                "id": 2,
                "name": "Build Muscle",
                "description": "Increase muscle mass and strength"
            },
            {
                "id": 3,
                "name": "Improve Endurance",
                "description": "Enhance cardiovascular fitness"
            }
        ],
        "fitness_levels": [
            {
                "id": "beginner",
                "name": "Beginner",
                "description": "Just getting started with fitness"
            },
            {
                "id": "intermediate",
                "name": "Intermediate", 
                "description": "Regular exercise routine"
            },
            {
                "id": "advanced",
                "name": "Advanced",
                "description": "Experienced fitness enthusiast"
            }
        ],
        "genders": [
            {
                "id": "male",
                "name": "Male"
            },
            {
                "id": "female",
                "name": "Female"
            },
            {
                "id": "other",
                "name": "Other"
            },
            {
                "id": "prefer_not_to_say",
                "name": "Prefer not to say"
            }
        ]
    }
}
```
</details>

---

## ❌ Error Codes Reference

| Error Code | Description | HTTP Status | User Action |
|------------|-------------|-------------|-------------|
| `VALIDATION_ERROR` | Input validation failed | 422 | Check input and retry |
| `SCHEDULE_FETCH_ERROR` | Unable to load schedule | 500 | Try again later |
| `PROGRESS_FETCH_ERROR` | Unable to load progress | 500 | Try again later |
| `SLIDERS_FETCH_ERROR` | Unable to load promotional content | 500 | Try again later |
| `SUGGESTED_WORKOUTS_ERROR` | Unable to load workout suggestions | 500 | Try again later |
| `GUIDANCE_TIPS_ERROR` | Unable to load guidance tips | 500 | Try again later |
| `INVALID_WORKOUT_ID` | Invalid workout category ID | 400 | Provide valid workout ID |
| `WORKOUT_NOT_FOUND` | Workout category not found | 404 | Select different workout |
| `WORKOUT_DETAILS_ERROR` | Unable to load workout details | 500 | Try again later |
| `VIDEO_TRACKING_VALIDATION_ERROR` | Video tracking validation failed | 422 | Check video ID |
| `VIDEO_TRACKING_ERROR` | Unable to track video progress | 500 | Continue workout normally |
| `WORKOUT_HISTORY_ERROR` | Unable to load workout history | 500 | Try again later |
| `NOTIFICATIONS_FETCH_ERROR` | Unable to load notifications | 500 | Try again later |
| `ATTACHMENT_UPLOAD_ERROR` | File upload failed | 422 | Try different file |
| `EMAIL_SEND_ERROR` | Email sending failed | 503 | Try again or contact directly |
| `CONTACT_US_ERROR` | General contact us error | 500 | Try again later |
| `PROFILE_FETCH_ERROR` | Unable to load profile | 500 | Try again later |
| `PROFILE_UPDATE_ERROR` | Unable to update profile | 500 | Try again later |
| `PROFILE_PICTURE_UPDATE_ERROR` | Profile picture upload failed | 500 | Try again later |
| `PROFILE_PICTURE_DELETE_ERROR` | Unable to remove profile picture | 500 | Try again later |
| `SETUP_DATA_FETCH_ERROR` | Unable to load setup data | 500 | Try again later |

---

## 🛠️ Environment Configuration

Add these environment variables to your `.env` file:

```env
# Mail Configuration for Contact Us
MAIL_ADMIN_EMAIL=admin@yourcompany.com
MAIL_FROM_ADDRESS=noreply@yourcompany.com
MAIL_FROM_NAME="CSW Support Team"

# App Configuration
APP_URL=https://your-domain.com
```

---

## 📦 Postman Collection

You can import this documentation into Postman:

1. Open Postman
2. Click "Import" 
3. Paste this documentation URL
4. Set up environment variables:
   - `base_url`: https://your-domain.com/api
   - `bearer_token`: Your authentication token

---

*📝 Last updated: October 1, 2025*
