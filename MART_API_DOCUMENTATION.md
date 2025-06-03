# Mart API Documentation

## Overview

This document describes the Mart API integration for the metag-analyze project. The Mart API provides structured data endpoints specifically designed for mobile application integration, with standardized data formats for projects, question sheets, and data submission.

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

### Project Structure Response

```json
{
  "projectOptions": {
    "projectId": 1,
    "projectName": "Sample Project",
    "options": {
      "startDate": "2025-04-01T00:00:00Z",
      "endDate": "2025-05-01T00:00:00Z",
      "startTime": null,
      "endTime": null,
      "breakBetweenQuestionSheets": 0,
      "relatedQuestionSheets": [
        {
          "sheetId": 1,
          "type": "initial"
        }
      ],
      "useNotifications": true
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
  "uuid": "device-123",
  "userId": "user-456",
  "participantId": "participant-789",
  "sheetId": 1,
  "sheetStarted": 1681459200000,
  "sheetSubmitted": 1681459500000,
  "sheetDuration": 300000,
  "answers": {
    "1": 4
  }
}
```

## Usage for Mobile Developers

### Authentication

Include the following header in all requests:

```
Authorization: Bearer mobile_test_token_123456
```

### Workflow

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

## Notes

This API is designed to provide a consistent interface for mobile applications. The data is transformed from the internal structure to match the required format for the mobile application.