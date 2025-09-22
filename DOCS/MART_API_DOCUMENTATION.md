# Mart API Documentation

## Overview

This document describes the Mart API integration for the metag-analyze project. The Mart API provides structured data endpoints specifically designed for mobile application integration, with standardized data formats for projects, question sheets, and data submission. The API now supports ESM (Experience Sampling Method) studies with recurring questionnaire schedules.

## Implementation Details

The Mart API consists of:

1. **Custom API Resources** - Located in `app/Http/Resources/Mart/`
   - `ProjectOptionsResource.php` - Formats project metadata
   - `QuestionSheetResource.php` - Formats questionnaire items
   - `ScaleResource.php` - Formats question type information
   - `MartStructureResource.php` - Combines all resources into a unified structure

2. **Controller** - Located in `app/Http/Controllers/`
   - `MartApiController.php` - Handles API requests for structure retrieval and data submission

3. **Routes** - Located in `routes/mart_api.php`
   - Registered in `RouteServiceProvider.php` with the prefix `mart-api`

## Authentication

The Mart API uses Laravel's token-based authentication. For testing purposes, a fixed bearer token can be created as follows:

```php
// Using Laravel Tinker
$token = "mobile_test_token_123456";  // Change this to a secure token in production
$hashedToken = hash('sha256', $token);

$user = App\User::where('email', 'testmobile@example.com')->first();
if (!$user) {
    $user = new App\User();
    $user->email = 'testmobile@example.com';
    $user->password = Hash::make('secure_password');
    $user->save();
    
    $role = App\Role::where('name', 'user')->first();
    $user->roles()->sync($role);
}

$user->forceFill([
    'api_token' => $hashedToken,
    'token_expires_at' => now()->addYears(1),
])->save();
```

## Available Endpoints

### 1. Get Project Structure

Retrieves the project structure, including project options, question sheets, and scales.

- **URL**: `/mart-api/projects/{project}/structure`
- **Method**: GET
- **Authentication**: Bearer Token
- **Response Format**: JSON

### 2. Submit Data

Submits user responses to a case.

- **URL**: `/mart-api/cases/{case}/submit`
- **Method**: POST
- **Authentication**: Bearer Token
- **Request Format**: JSON
- **Required Fields**:
  - `projectId` (number)
  - `uuid` (string)
  - `userId` (string)
  - `participantId` (string)
  - `sheetId` (number)
  - `sheetStarted` (timestamp in milliseconds)
  - `sheetSubmitted` (timestamp in milliseconds)
  - `sheetDuration` (milliseconds)
  - `answers` (object)

## Data Structure

### Questionnaire Scheduling

The API supports two types of questionnaires:

#### Single Questionnaires
One-time questionnaires scheduled for a specific date and time, with optional conditional display based on repeating questionnaire completion.

#### Repeating Questionnaires  
Recurring questionnaires with configurable:
- Daily intervals (e.g., every 4 hours)
- Time windows (e.g., 9am-9pm only)
- Maximum daily submissions
- Minimum break between questionnaires
- Random or fixed timing within intervals

### Project Structure Response

```json
{
  "projectOptions": {
    "projectId": 1,
    "projectName": "Sample Project",
    "options": {
      "startDateAndTime": {
        "date": "2025-04-01",
        "time": "09:00"
      },
      "endDateAndTime": {
        "date": "2025-05-01",
        "time": "21:00"
      },
      "singleQuestionnaires": [
        {
          "questionnaireId": 2,
          "type": "single",
          "startDateAndTime": {
            "date": "2025-04-15",
            "time": "17:00"
          },
          "showProgressBar": true,
          "showNotifications": true,
          "notificationText": "Weekly reflection time"
        }
      ],
      "repeatingQuestionnaires": [
        {
          "questionnaireId": 1,
          "type": "repeating",
          "startDateAndTime": {
            "date": "2025-04-01",
            "time": "09:00"
          },
          "endDateAndTime": {
            "date": "2025-05-01",
            "time": "21:00"
          },
          "minBreakBetweenQuestionnaire": 180,
          "dailyIntervalDuration": 4,
          "maxDailySubmits": 6,
          "dailyStartTime": "09:00",
          "dailyEndTime": "21:00",
          "questAvailableAt": "randomTimeWithinInterval"
        }
      ],
      "showNotifications": true,
      "collectDeviceInfos": true
    }
  },
  "questionSheets": [
    {
      "projectId": 1,
      "sheetId": 1,
      "name": "Sample Project Questions",
      "items": [
        {
          "itemId": 1,
          "scaleId": 1,
          "text": "How would you rate your experience?",
          "options": {
            "randomizationGroup": 1
          }
        }
      ]
    }
  ],
  "scales": [
    {
      "projectId": 1,
      "scaleId": 1,
      "name": "Experience Rating",
      "options": {
        "type": "number",
        "maxValue": 5,
        "minValue": 1
      }
    }
  ]
}
```

### Submit Request Example

```json
{
  "projectId": 1,
  "questionnaireId": 1,
  "uuid": "device-123",
  "userId": "user-456",
  "participantId": "participant-789",
  "sheetId": 1,
  "sheetStarted": 1681459200000,
  "sheetSubmitted": 1681459500000,
  "sheetDuration": 300000,
  "timezone": "Europe/Berlin",
  "answers": {
    "1": 4
  }
}
```

## Complete ESM Project Workflow

This section provides a step-by-step guide to create an ESM project, create cases, and submit data using the MART API.

### Step 1: Create an ESM Project

First, create a new project in the system and configure it for MART/ESM usage:

1. **Via Web Interface**:
   - Navigate to project creation
   - Configure project with MART input type
   - Add questionnaire schedules
   - Note the generated **Project ID**

2. **Project Configuration**:
   ```json
   {
     "name": "Daily Wellbeing Study",
     "description": "ESM study tracking daily mood and activities",
     "inputs": [
       {
         "type": "mart",
         "schedules": {
           "repeating": [
             {
               "questionnaireId": 1,
               "dailyIntervalDuration": 4,
               "maxDailySubmits": 6,
               "dailyStartTime": "09:00",
               "dailyEndTime": "21:00"
             }
           ]
         }
       },
       {
         "name": "Current Mood",
         "type": "radio",
         "answers": {
           "0": "Very bad",
           "1": "Bad", 
           "2": "Neutral",
           "3": "Good",
           "4": "Very good"
         }
       }
     ]
   }
   ```

### Step 2: Create a Case

Create a case for a participant in your ESM project:

1. **Via Web Interface**:
   - Navigate to project cases
   - Click "Create Case"
   - Fill participant details
   - Note the generated **Case ID**

2. **Case Configuration**:
   ```json
   {
     "name": "participant-123",
     "project_id": 1,
     "user_email": "participant@study.com",
     "duration": 14,
     "duration_unit": "days"
   }
   ```

### Step 3: Get Project Structure

Retrieve the complete project structure using the MART API:

```bash
curl -X GET "https://your-domain.com/mart-api/projects/1/structure" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "data": {
    "projectOptions": {
      "projectId": 1,
      "projectName": "Daily Wellbeing Study",
      "options": {
        "repeatingQuestionnaires": [
          {
            "questionnaireId": 1,
            "type": "repeating",
            "dailyIntervalDuration": 4,
            "maxDailySubmits": 6,
            "dailyStartTime": "09:00",
            "dailyEndTime": "21:00"
          }
        ]
      }
    },
    "questionSheets": [
      {
        "projectId": 1,
        "sheetId": 1,
        "name": "Daily Check-in",
        "items": [
          {
            "itemId": 1,
            "scaleId": 1,
            "text": "Current Mood"
          }
        ]
      }
    ],
    "scales": [
      {
        "projectId": 1,
        "scaleId": 1,
        "name": "Current Mood",
        "options": {
          "type": "radio",
          "radioOptions": [
            {"value": 0, "text": "Very bad"},
            {"value": 1, "text": "Bad"},
            {"value": 2, "text": "Neutral"},
            {"value": 3, "text": "Good"},
            {"value": 4, "text": "Very good"}
          ]
        }
      }
    ]
  }
}
```

### Step 4: Submit Entry Data

Submit participant responses using the case ID:

```bash
curl -X POST "https://your-domain.com/mart-api/cases/5/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{
    "projectId": 1,
    "questionnaireId": 1,
    "userId": "participant@study.com",
    "participantId": "participant-123",
    "sheetId": 1,
    "questionnaireStarted": 1725280800000,
    "questionnaireDuration": 120000,
    "answers": {
      "1": 3
    },
    "timezone": "Europe/Berlin",
    "timestamp": 1725280920000
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "entry_id": 486,
  "message": "Entry created successfully"
}
```

### Validation and Error Handling

The API includes comprehensive validation:

1. **Case Status Validation**:
   - Only `pending` or `active` cases accept submissions
   - Completed cases return error 422

2. **Data Validation**:
   - Answer types must match question scales
   - Values must be within allowed ranges
   - All required questions must be answered

3. **Error Response Example**:
```json
{
  "success": false,
  "message": "Answer validation failed",
  "errors": [
    "Question 1: Answer must be a single integer for radio questions",
    "Question 2: Answer 15 is out of range (0-10)"
  ]
}
```

### Testing Workflow

For complete testing documentation, see `MART_API_TESTING.md` which includes:
- Validation test cases
- Error scenarios
- Expected responses
- Copy-paste curl commands

## Usage for Mobile Developers

### Authentication

Include the following header in all requests:

```
Authorization: Bearer mart_test_token_2025
```

### Basic Workflow

1. **Retrieve Project Structure**:
   - Make a GET request to `/mart-api/projects/{project_id}/structure`
   - Parse the response to display question sheets and scales

2. **Submit User Responses**:
   - Collect user responses to questions
   - Format as a Submit object (see example above)
   - Make a POST request to `/mart-api/cases/{case_id}/submit`

### Data Mapping

- `ProjectOptions` - Contains project metadata and timing information
- `QuestionSheet` - Contains groups of questions (items)
- `Scale` - Defines the type and options for each question
- `Submit` - User responses to be sent back to the server

## Database Structure

### mart_questionnaire_schedules
Stores questionnaire scheduling information for ESM studies:
- `questionnaire_id` - Unique identifier for mobile app
- `type` - 'single' or 'repeating'
- `start_date_time` / `end_date_time` - Schedule boundaries
- `daily_interval_duration` - Hours between questionnaires
- `min_break_between` - Minimum minutes between submissions
- `max_daily_submits` - Maximum submissions per day
- `quest_available_at` - 'startOfInterval' or 'randomTimeWithinInterval'

### Data Storage
- Questions remain in `projects.inputs` for compatibility
- MART metadata stored in `entries.inputs` JSON alongside answers
- Questionnaire schedules in dedicated `mart_questionnaire_schedules` table

## Notes

This API is designed to provide a consistent interface for mobile applications. The data is transformed from the internal structure to match the required format for the mobile application. The API now fully supports ESM (Experience Sampling Method) studies with complex scheduling requirements.