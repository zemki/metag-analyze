# MART API Reference

> **Interactive docs:** For auto-generated API docs with "Try It" feature,
> visit `/docs/api` when running the app locally. This file serves as a
> quick-start flow guide. For exact request/response schemas, refer to the
> interactive docs.

The MART API lives at two URL prefixes:

| Prefix       | Purpose                    | Auth                            |
|--------------|----------------------------|---------------------------------|
| `/api/mart`  | Auth flow (email/password) | None (per-IP rate limited)      |
| `/mart-api`  | Project data & files       | `Authorization: Bearer {token}` |

All endpoints return JSON. No `Accept: application/json` header is required.

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
Returns project configuration: questionnaires, questions, scales, and pages.

**Query params:**
- `participant_id` *(optional)* — when provided, the response also includes per-case data
  (submissions, device info) and applies per-case date overrides to schedules.
  If omitted and the request is Bearer-authenticated, the controller falls back to the
  authenticated user's case (auto-creating one if none exists).

**Errors:**
- `401` — Missing or invalid Bearer token
- `404` — Project has no MART data

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
`answers` is an **object** keyed by `itemId` (1-based question index), not an array.
Values can be numbers (scale), arrays (multiple choice), strings (text), or file UUIDs
returned from File Upload.

> **Deprecated:** `sheetId` is accepted for backward compatibility but ignored.
> Use `questionnaireId` instead.

**Response (200):**
```json
{ "success": true, "entry_id": 42, "message": "Entry created successfully" }
```

**Errors:**
- `400` — Case does not belong to `projectId`
- `401` — Missing or invalid Bearer token
- `404` — Project or questionnaire schedule not found
- `422` — Validation error, or case is completed / not accepting submissions
  (response includes `case_status`)
- `500` — Cross-DB transaction failed (both main and MART DBs rolled back)

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
`userId`, `projectId`, and `participantId` must match the case created during `check-access`.

**Response (200):**
```json
{ "success": true, "message": "Device information stored successfully", "device_info_id": 1 }
```

**Errors:**
- `401` — Missing or invalid Bearer token
- `403` — `userId` / `participantId` / `projectId` do not match an existing case
- `404` — User not found
- `422` — Validation error (missing or invalid fields)

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
At least one of `androidUsageStats`, `androidEventStats`, or `iOSStats` should be provided
(all three are nullable). Same access requirements as device-infos.

**Response (200):**
```json
{ "success": true, "stat_id": 1, "message": "Stats submitted successfully" }
```

**Errors:**
- `401` — Missing or invalid Bearer token
- `403` — `userId` / `participantId` / `projectId` do not match an existing case
- `404` — User or MART project not found
- `422` — Validation error

### File Upload
```
POST /mart-api/cases/{caseId}/files
```

Two upload modes are supported. Pick whichever fits your client:

**Mode A — multipart/form-data** *(recommended for mobile)*
```
Content-Type: multipart/form-data
```
| Field           | Type   | Required | Notes                                        |
|-----------------|--------|----------|----------------------------------------------|
| `file`          | file   | yes      | Raw binary upload                             |
| `file_type`     | string | no       | Inferred from detected MIME when omitted      |
| `question_uuid` | string | no       | Link to a specific question                   |
| `original_name` | string | no       | Falls back to the uploaded file's client name |

**Mode B — application/json (base64)**
```json
{
  "file_type": "photo",
  "file": "<base64_encoded_content>",
  "question_uuid": "optional-uuid",
  "original_name": "photo.jpg"
}
```
- `file` — plain base64, no `data:` URI prefix
- `file_type` is optional; inferred from detected MIME when omitted

**Common rules (both modes):**
- `file_type` — one of `photo`, `video`, `audio`, `document`
- `question_uuid` *(optional)* — link the file to a specific question; can also be linked
  later when the submission is sent
- Max size: 50MB
- Files are validated by actual content (not declared MIME) and encrypted at rest
- When a MIME type maps to multiple buckets (e.g. `image/jpeg` → `photo` or `document`),
  the first match wins in declaration order (`photo` wins over `document`). Send
  `file_type` explicitly if you need the other bucket.

**Allowed MIME types:**
| `file_type` | Accepted MIME types |
|-------------|---------------------|
| `photo`     | image/jpeg, image/png, image/gif, image/webp |
| `video`     | video/mp4, video/quicktime, video/webm |
| `audio`     | audio/mpeg, audio/mp4, audio/aac, audio/wav, audio/ogg, audio/webm, audio/flac |
| `document`  | application/pdf |

Files not matching the whitelist are rejected with `422` and logged.

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

**Errors:**
- `400` — Case does not belong to a MART project, or base64 decode failed (base64 mode only)
- `401` — Missing or invalid Bearer token
- `422` — Validation error, or file content is not in the allowed MIME whitelist,
  or file exceeds 50MB
- `500` — File storage failure

### Retrieve File
```
GET /mart-api/files/{fileId}
```
Returns the decrypted file binary with the original `Content-Type` header.
Authorization: the authenticated user must own the case that uploaded the file.

**Errors:**
- `401` — Missing or invalid Bearer token
- `403` — User does not own the case linked to the file
- `404` — File not found

### Delete File
```
DELETE /mart-api/files/{fileId}
```
Only files that have not been linked to a submission can be deleted.

**Response (200):**
```json
{ "success": true, "message": "File deleted successfully" }
```

**Errors:**
- `400` — File is already linked to a submission
- `401` — Missing or invalid Bearer token
- `403` — User does not own the case linked to the file
- `404` — File not found

---

## Rate Limits

All auth-flow endpoints are rate-limited per IP. Exceeding the limit returns `429 Too Many Requests`.

| Endpoint                    | Limit          |
|-----------------------------|----------------|
| `check-email`               | 5 / minute     |
| `send-password-setup`       | 3 / 10 minutes |
| `check-password`            | 10 / minute    |
| `check-access`              | 10 / minute    |
| `refresh`                   | 10 / minute    |

Authenticated `/mart-api/*` endpoints are not individually rate-limited.

---

## Authentication

All `/mart-api/*` endpoints require the header:
```
Authorization: Bearer {bearerToken}
```
A missing or invalid token returns `401`:
```json
{ "message": "Unauthenticated." }
```

When you receive `401` on an authenticated endpoint, exchange your `refreshToken` at
`POST /api/mart/refresh` to get a fresh pair. Both tokens are rotated on every refresh —
the old `refreshToken` is invalidated immediately.

Auth-flow endpoints (`/api/mart/*`) do **not** require a Bearer token; they use cached
flow state (see Screen 1 / 2 / 3 above) plus per-IP rate limits.

---

## Error Responses

### Status codes

| Code | Meaning |
|------|---------|
| 400  | Bad request (invalid input, failed base64, wrong project) |
| 401  | Missing / invalid / expired Bearer token |
| 403  | Access denied or auth-flow validation failed |
| 404  | Resource (project, case, user, file, schedule) not found |
| 422  | Validation error — see `errors` field for per-field messages |
| 429  | Rate limit exceeded |
| 500  | Server error (e.g. cross-DB transaction rollback) |

### Error shapes

The two namespaces use different error shapes — handle both in your client.

**Auth-flow endpoints** (`/api/mart/*`) return:
```json
{
  "error": "Flow validation failed",
  "message": "Please check your email first",
  "step": "email_check_required"
}
```
The optional `step` field tells the client which screen to return to.

**Data endpoints** (`/mart-api/*`) return:
```json
{
  "success": false,
  "message": "Case does not belong to the specified project"
}
```
Validation errors (`422`) on both namespaces additionally include an `errors` object:
```json
{
  "message": "The userId field is required.",
  "errors": {
    "userId": ["The userId field is required."],
    "participantId": ["The participantId field is required."]
  }
}
```
