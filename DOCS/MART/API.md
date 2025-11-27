# MART API Reference

Base URL: `/mart-api` (authenticated) and `/api/mart` (auth flow)

## Authentication Flow

Three-screen authentication process:

### Screen 1: Check Email
```
POST /api/mart/check-email
```
```json
{ "email": "user@example.com" }
```
Response: `{ "email": "...", "emailExists": true/false }`

### Screen 1b: Register (if new user)
```
POST /api/mart/send-password-setup
```
Sends password setup email.

### Screen 2: Check Password
```
POST /api/mart/check-password
```
```json
{ "email": "...", "password": "..." }
```
Response: `{ "bearerToken": "...", "refreshToken": "..." }`

### Screen 3: Check Project Access
```
POST /api/mart/check-access
```
```json
{ "email": "...", "projectId": 123 }
```
Auto-creates case if user has access.

### Token Refresh
```
POST /api/mart/refresh
Authorization: Bearer {refreshToken}
```

## Authenticated Endpoints

All require: `Authorization: Bearer {token}`

### Get Project Structure
```
GET /mart-api/projects/{id}/structure
GET /mart-api/projects/{id}/structure?participant_id={id}
```
Returns complete project configuration, questionnaires, scales, and pages.

### Submit Entry
```
POST /mart-api/cases/{id}/submit
```
```json
{
  "questionnaireId": 1,
  "projectId": 1,
  "answers": { "uuid-1": ["value"], "uuid-2": ["value"] },
  "timestamp": 1234567890,
  "timezone": "Europe/Berlin"
}
```

### Store Device Info
```
POST /mart-api/device-infos
```

### Submit Stats
```
POST /mart-api/stats
```
Android usage/event statistics.

### File Upload
```
POST /mart-api/cases/{id}/files
```
For photo/audio/video uploads in questionnaire answers.

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| check-email | 5/minute |
| send-password-setup | 3/10 minutes |
| check-password | 10/minute |
| check-access | 10/minute |
| refresh | 10/minute |

## Error Responses

| Code | Meaning |
|------|---------|
| 401 | Invalid/expired token |
| 403 | Access denied |
| 422 | Validation error |
| 429 | Rate limit exceeded |
