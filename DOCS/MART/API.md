# MART API Reference

## Authentication

All requests require Bearer token authentication:

```
Authorization: Bearer [token]
Content-Type: application/json
Accept: application/json
```

## Endpoints

### 1. Get Project Structure

Retrieves complete project configuration including questionnaires and scheduling.

**Endpoint:** `GET /mart-api/projects/{project_id}/structure`

**Response:**
```json
{
  "data": {
    "projectOptions": {
      "projectId": 1,
      "projectName": "Daily Wellbeing Study",
      "options": {
        "startDateAndTime": {
          "date": "01.04.2025",
          "time": "09:00"
        },
        "endDateAndTime": {
          "date": "01.05.2025",
          "time": "21:00"
        },
        "repeatingQuestionnaires": [
          {
            "questionnaireId": 1,
            "type": "repeating",
            "dailyIntervalDuration": 4,
            "maxDailySubmits": 6,
            "minBreakBetweenQuestionnaire": 180,
            "dailyStartTime": "09:00",
            "dailyEndTime": "21:00",
            "questAvailableAt": "randomTimeWithinInterval",
            "showProgressBar": true,
            "showNotifications": true,
            "notificationText": "Time for your check-in!"
          }
        ],
        "singleQuestionnaires": [
          {
            "questionnaireId": 2,
            "type": "single",
            "startDateAndTime": {
              "date": "15.04.2025",
              "time": "17:00"
            }
          }
        ]
      }
    },
    "questionnaires": [
      {
        "projectId": 1,
        "questionnaireId": 1,
        "name": "Daily Check-in",
        "items": [
          {
            "itemId": 1,
            "scaleId": 1,
            "text": "How are you feeling right now?",
            "required": true,
            "options": {
              "randomizationGroupId": null
            }
          }
        ]
      }
    ],
    "scales": [
      {
        "projectId": 1,
        "scaleId": 1,
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

### 2. Submit Entry Data

Submits participant responses for a questionnaire.

**Endpoint:** `POST /mart-api/cases/{case_id}/submit`

**Request Body:**
```json
{
  "projectId": 1,
  "questionnaireId": 1,
  "userId": "user@example.com",
  "participantId": "participant-123",
  "sheetId": 1,
  "questionnaireStarted": 1725280800000,
  "questionnaireDuration": 120000,
  "answers": {
    "1": 3,              // Radio/scale answer
    "2": [0, 2, 5],      // Multiple choice
    "3": "Text answer"   // Text input
  },
  "timezone": "Europe/Berlin",
  "timestamp": 1725280920000
}
```

**Success Response:**
```json
{
  "success": true,
  "entry_id": 486,
  "message": "Entry created successfully"
}
```

### 3. Submit Device Information

Stores device information for analytics.

**Endpoint:** `POST /mart-api/device-infos`

**Request Body:**
```json
{
  "caseId": 5,
  "deviceInfo": {
    "platform": "iOS",
    "version": "16.5",
    "model": "iPhone 14",
    "appVersion": "1.2.0"
  }
}
```

### 4. Submit App Statistics

Tracks app usage statistics.

**Endpoint:** `POST /mart-api/stats`

**Request Body:**
```json
{
  "caseId": 5,
  "stats": {
    "sessionDuration": 300,
    "questionnairesCompleted": 2,
    "timestamp": 1725280920000
  }
}
```

## Question Types

### Radio/Scale
```json
{
  "type": "radio",
  "radioOptions": [
    {"value": 0, "text": "Option 1"},
    {"value": 1, "text": "Option 2"}
  ]
}
```
**Answer format:** Single integer value

### Multiple Choice
```json
{
  "type": "checkbox",
  "checkboxOptions": [
    {"value": 0, "text": "Option A"},
    {"value": 1, "text": "Option B"},
    {"value": 2, "text": "Option C"}
  ]
}
```
**Answer format:** Array of selected values `[0, 2]`

### Text Input
```json
{
  "type": "text",
  "maxLength": 500
}
```
**Answer format:** String value

### Number Range
```json
{
  "type": "number",
  "minValue": 0,
  "maxValue": 10,
  "defaultValue": 5
}
```
**Answer format:** Integer value

## Validation Rules

### Case Status
- Only `pending` or `active` cases accept submissions
- `completed` cases return 422 error

### Answer Validation
- Radio questions: Single integer value required
- Multiple choice: Array of integers
- Text: String within maxLength
- Number: Integer within min/max range
- Required fields must be answered

### Common Validation Errors
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": [
    "Question 1: Answer is required",
    "Question 3: Answer must be between 0 and 10"
  ]
}
```

## Error Handling

### HTTP Status Codes
- `200`: Success
- `401`: Unauthorized (invalid token)
- `404`: Resource not found
- `422`: Validation error
- `500`: Server error

### Common Issues

**Invalid Token**
```json
{"message": "Unauthenticated."}
```
Solution: Check token in Authorization header

**Case Completed**
```json
{
  "success": false,
  "message": "Case is completed and no longer accepting submissions"
}
```
Solution: Use active case or create new one

## Mobile Integration Tips

### Handle Network Failures
- Implement retry logic with exponential backoff
- Store submissions locally when offline
- Sync when connection restored

### Time Synchronization
- Use device timezone for display
- Send UTC timestamps to server
- Include timezone in submission

### Notification Scheduling
- Parse questionnaire schedules
- Calculate next notification time
- Respect quiet hours (dailyStartTime/dailyEndTime)

## Rate Limiting
- Default: 60 requests per minute
- Submissions: 10 per minute per case
- Structure endpoint: Cached for 5 minutes