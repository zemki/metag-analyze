# MART API Reference

> **Interactive docs:** For auto-generated API docs with "Try It" feature,
> visit `/docs/api` when running the app locally. This file serves as a
> quick-start flow guide. For exact request/response schemas, refer to the
> interactive docs.

Base URL: `/mart-api` (authenticated) and `/api/mart` (auth flow)

All MART API endpoints return JSON responses. No `Accept: application/json` header is required.

---

## Testing Setup

Before testing the API, you need a MART project with questionnaires:

### 1. Get a Project ID

A researcher creates a MART project via the web UI:
1. Log in at the app URL (e.g., `https://metag-analyze.test`)
2. Go to **Projects** → **New Project**
3. Enable the **MART** option during creation
4. Add at least one questionnaire with questions
5. The project ID is visible in the URL: `/projects/{id}`

Share this `projectId` with the mobile developer.

### 2. Test the Auth Flow

Use the 3-screen flow below to get a bearer token and participantId. You can use the interactive docs at `/docs/api` or an API client like Bruno.

---

## Authentication Flow

Three-screen authentication process. Each screen must be completed in order.

### Screen 1: Check Email
```
POST /api/mart/check-email
```
```json
{ "email": "user@example.com" }
```
**Response:**
```json
{ "email": "user@example.com", "emailExists": true }
```
If `emailExists` is `false`, the user can register via Screen 1b.

### Screen 1b: Register (if new user)
```
POST /api/mart/send-password-setup
```
```json
{ "email": "newuser@example.com" }
```
**Requires:** Email must have been checked via `check-email` within the last minute.

**Response:**
```json
{ "success": true, "message": "Password setup email sent successfully. Please check your inbox." }
```
The user receives an email with a link to set their password.

**Errors:**
- `403` — Email was not checked first (flow validation)
- `400` — Email already registered and verified

### Screen 2: Check Password
```
POST /api/mart/check-password
```
```json
{ "email": "user@example.com", "password": "userpassword" }
```
**Requires:** Email must have been checked via `check-email` within the last minute.

**Response:**
```json
{
  "email": "user@example.com",
  "bearerToken": "access_token_string",
  "refreshToken": "refresh_token_string"
}
```
- `bearerToken` expires in 30 days
- `refreshToken` expires in 7 days

**Errors:**
- `401` — Invalid email or password
- `403` — Email was not checked first (flow validation)

### Screen 3: Check Project Access
```
POST /api/mart/check-access
```
```json
{ "email": "user@example.com", "projectId": 123 }
```
**Requires:** Password must have been checked via `check-password` within the last 5 minutes.

Auto-creates a participant case if the user doesn't have one in this project.

**Response:**
```json
{
  "projectId": 123,
  "participantIsAllowed": true,
  "participantId": "P1A2B3C",
  "caseId": 456
}
```
Save `participantId` and `caseId` — they are required for all subsequent API calls.

**Errors:**
- `403` — Password was not checked first, or project is not a MART project
- `404` — User or project not found

### Token Refresh
```
POST /api/mart/refresh
```
```json
{ "refreshToken": "current_refresh_token" }
```
**Note:** Send the refresh token in the **request body**, not as an Authorization header.

Implements token rotation — both old tokens are invalidated.

**Response:**
```json
{
  "bearerToken": "new_access_token",
  "refreshToken": "new_refresh_token"
}
```

**Errors:**
- `401` — Invalid or expired refresh token

---

## Authenticated Endpoints

All require: `Authorization: Bearer {bearerToken}`

### Get Project Structure
```
GET /mart-api/projects/{projectId}/structure
GET /mart-api/projects/{projectId}/structure?participant_id={participantId}
```
Returns complete project configuration: questionnaires, questions, scales, pages, and
participant-specific data (submissions, device info) when `participant_id` is provided.

### Submit Entry
```
POST /mart-api/cases/{caseId}/submit
```
```json
{
  "projectId": 123,
  "questionnaireId": 1,
  "userId": "user@example.com",
  "participantId": "P1A2B3C",
  "questionnaireStarted": 1234567890000,
  "questionnaireDuration": 300000,
  "answers": { "1": 5, "2": [0, 1] },
  "timestamp": 1234567890000,
  "timezone": "Europe/Berlin"
}
```

### Store Device Info
```
POST /mart-api/device-infos
```
```json
{
  "projectId": 123,
  "userId": "user@example.com",
  "participantId": "P1A2B3C",
  "os": "android",
  "osVersion": "14",
  "model": "Pixel 7",
  "manufacturer": "Google",
  "timestamp": 1234567890000,
  "timezone": "Europe/Berlin"
}
```
**Important:** `userId`, `projectId`, and `participantId` must match the case created during `check-access`. A mismatch returns `403` with a `hint` field explaining what to verify.

### Submit Stats
```
POST /mart-api/stats
```
```json
{
  "projectId": 123,
  "userId": "user@example.com",
  "participantId": "P1A2B3C",
  "timestamp": 1234567890000,
  "timezone": "Europe/Berlin",
  "androidUsageStats": [],
  "androidEventStats": [],
  "iOSStats": {}
}
```
Same access requirements as device-infos.

### File Upload
```
POST /mart-api/cases/{caseId}/files
```
```json
{
  "file_type": "photo",
  "file": "<base64_encoded_content>",
  "question_uuid": "optional-uuid",
  "original_name": "photo.jpg"
}
```
- `file_type`: one of `photo`, `video`, `audio`, `document`
- `file`: base64-encoded file content
- Max size: 50MB
- Files are encrypted at rest

**Response (201):**
```json
{
  "success": true,
  "file_id": "uuid-string",
  "fileUrl": "/mart-api/files/uuid-string",
  "file_type": "photo",
  "mime_type": "image/jpeg",
  "size": 12345
}
```
Use `file_id` (UUID) in questionnaire answer values for file-type questions.
Use `fileUrl` to retrieve the file via `GET /mart-api/files/{file_id}`.

### Retrieve File
```
GET /mart-api/files/{fileId}
```
Returns the decrypted file content with appropriate MIME type headers.

### Delete File
```
DELETE /mart-api/files/{fileId}
```
Only files that have not been linked to a submission can be deleted.

---

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| check-email | 10/minute |
| send-password-setup | 5/10 minutes |
| check-password | 10/minute |
| check-access | 10/minute |
| refresh | 10/minute |

## Error Responses

| Code | Meaning |
|------|---------|
| 400 | Bad request (invalid input) |
| 401 | Invalid/expired token |
| 403 | Access denied or flow validation failed (check `hint` field) |
| 404 | Resource not found |
| 422 | Validation error (check `errors` field) |
| 429 | Rate limit exceeded |
