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

## ID Conventions

| Field | Type | What it is |
|---|---|---|
| `userId` | string | The participant's email address. Must match the case owner's email. |
| `participantId` | string | The case's `name` field — any string. Auto-generated as `P` + 6 hex chars (e.g. `P1A2B3C`) when the participant registers via the 3-screen MART auth flow; cases created manually in the admin panel can have any string (e.g. `participant-003`). Use whatever `check-access` returns; do not assume the `P…` format. |
| `caseId` | integer | The case's numeric primary key, used only inside URL paths (e.g. `/cases/{caseId}/...`). Different from `participantId`. |
| `projectId` | integer | The project's numeric primary key. |
| `questionnaireId` | integer | The schedule's questionnaire id (1-based per project). |
| `bearerToken` | string | 30-day API access token returned by `check-password`. |
| `refreshToken` | string | 7-day rotation token returned by `check-password`. |

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

Three-screen authentication process. Each screen must be completed in order, with a time
window between steps:

| From | To | Window |
|---|---|---|
| `check-email` | `send-password-setup` | 60 seconds |
| `check-email` | `check-password` | 60 seconds |
| `check-password` | `check-access` | 5 minutes |

Exceeding the window returns `403 Flow validation failed` with a `step` hint pointing
the client back to the screen it needs to redo.

### Screen 1: Check Email
```
POST /api/mart/check-email
```
```json
{ "email": "user@example.com" }
```
**Response:**
```json
{ "email": "user@example.com", "emailExists": true, "emailVerified": true }
```

`emailVerified` is `true` only when the user has both registered and completed
the password-setup link. Use it to decide where to send the user next:

| `emailExists` | `emailVerified` | Next screen |
|---|---|---|
| `false` | `false` | Screen 1b — `/send-password-setup` (new registration) |
| `true`  | `false` | Screen 1b — `/send-password-setup` (resume registration; user never finished) |
| `true`  | `true`  | Screen 2 — `/check-password` (login) |

Soft-deleted users are reported as `emailExists: false` so they can re-register
through Screen 1b cleanly.

### Screen 1b: Register (if new user)
```
POST /api/mart/send-password-setup
```
```json
{ "email": "newuser@example.com" }
```

**Response:**
```json
{ "success": true, "message": "Password setup email sent successfully. Please check your inbox." }
```
The user receives an email with a link to set their password.

**Errors:**
- `400` — Email already registered and verified
- `403` — Flow window expired (see Authentication Flow window table)

### Screen 2: Check Password
```
POST /api/mart/check-password
```
```json
{ "email": "user@example.com", "password": "userpassword" }
```

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
- `403` — Flow window expired (see Authentication Flow window table)

### Screen 3: Check Project Access
```
POST /api/mart/check-access
```
```json
{ "email": "user@example.com", "projectId": 123 }
```

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
Save `participantId` and `caseId`; they are required for all subsequent API calls.
See the [ID Conventions](#id-conventions) table at the top for what each field is.

**Errors:**
- `403` — Flow window expired, or project is not a MART project
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

## Using the Bearer Token

All `/mart-api/*` endpoints require the header:
```
Authorization: Bearer {bearerToken}
```
A missing or invalid token returns `401`:
```json
{ "message": "Unauthenticated." }
```

When you receive `401` on an authenticated endpoint, exchange your `refreshToken` at
`POST /api/mart/refresh` to get a fresh pair. Both tokens are rotated on every refresh,
and the old `refreshToken` is invalidated immediately.

Auth-flow endpoints (`/api/mart/*`) do **not** require a Bearer token; they use cached
flow state (see Screen 1 / 2 / 3 above) plus per-IP rate limits.

---

## Authenticated Endpoints

### Get Project Structure
```
GET /mart-api/projects/{projectId}/structure
GET /mart-api/projects/{projectId}/structure?participant_id={participantId}
```
Returns project configuration: questionnaires, questions, scales, and pages, plus
per-participant data when `participant_id` is provided.

**Response shape (200):**
```json
{
  "data": {
    "projectOptions": {
      "projectId": 1,
      "projectName": "Study",
      "options": {
        "startDateAndTime": { "date": "01.01.2025", "time": "00:00" },
        "endDateAndTime":   { "date": "01.01.2035", "time": "23:59" },
        "collectDeviceInfos": true,
        "iOSDataDonationQuestionnaire": null,
        "androidDataDonationQuestionnaire": null,
        "collectAndroidStats": false
      }
    },
    "questionnaires": [
      { "questionnaireId": 1, "name": "Daily Check-in", "items": [] }
    ],
    "scales": [],
    "pages": [],
    "deviceInfos": [],
    "repeatingSubmits":  [{ "questionnaireId": 1, "timestamp": 1700000120 }],
    "singleSubmits":     [{ "questionnaireId": 2, "timestamp": 1700090000 }],
    "lastDataDonationSubmit": { "timestamp": 1700100000 },
    "lastAndroidStatsSubmit": { "timestamp": 1700100500 }
  }
}
```

**Query params:**
- `participant_id` *(optional)* — when provided, the response also includes per-case data
  (submissions, device info) and applies per-case date overrides to schedules.
  If omitted and the request is Bearer-authenticated, the controller falls back to the
  authenticated user's case (auto-creating one if none exists).
  **If a `participant_id` is provided but does not match any case in the project, the
  endpoint returns `404`** (it does not silently fall back).

**Per-participant fields in the response:**
- `repeatingSubmits` and `singleSubmits` are returned as separate arrays, filtered by
  the schedule's type (`repeating` vs `single`).
- `lastDataDonationSubmit` is `{ timestamp }` (no `questionnaireId`). It returns the
  timestamp of the participant's most recent submission for **any** schedule flagged
  `is_ios_data_donation` **or** `is_android_data_donation` — whichever platform was
  submitted last wins. Returns `null` if the project has no data-donation schedules
  or the participant hasn't donated yet. Does not refer to `mart_stats` rows.
- Data-donation schedules **never appear** in `projectOptions.options.singleQuestionnaires`.
  They're triggered by the participant tapping "Donate" in the app rather than by the
  mobile's scheduler, so including them there would cause spurious scheduling. Look
  them up via `projectOptions.options.iOSDataDonationQuestionnaire` /
  `androidDataDonationQuestionnaire` (which return the donation questionnaire's `id`)
  and find the actual content in the top-level `questionnaires[]` catalog.
- Each questionnaire in `questionnaires[]` carries its own `name` from its schedule
  (rather than a single project-level name reused for every questionnaire).

**Supported question types** (`questionnaires[].items[].type`):

| API value | Storage type | Notes |
|---|---|---|
| `text` | `text` | single-line text input |
| `textarea` | `textarea` | multi-line text input |
| `number` | `number` | integer or decimal entry |
| `range` | `range` | slider with `minValue` / `maxValue` / `steps` |
| `radio` (a.k.a. `one choice`) | `one choice` | single-select |
| `checkbox` (a.k.a. `multiple choice`) | `multiple choice` | multi-select |
| `photoUpload` | `photo` | participant uploads via `POST /mart-api/cases/{caseId}/files`, then references the returned `file_id` in the answer |
| `audioUpload` | `audio` | same flow as `photoUpload` |
| `videoUpload` | `video` | same flow as `photoUpload` |
| `display` | `display` | non-input element (instructions, headers) |

For upload types the answer value is the `file_id` (UUID) returned by the file
upload endpoint; the mobile must upload the bytes first, then submit the entry
referencing the resulting `file_id`. See the "Upload File" / "Retrieve File"
section below.

**Project Availability Window (`projectOptions.options.startDateAndTime` / `endDateAndTime`):**
Both objects always have shape `{ date, time }` with non-null values, so the mobile
client never has to handle nullability. Both are optional on the researcher side; if
they're not set, the API supplies sensible defaults:

| Field | If researcher set it | If not set |
|---|---|---|
| `startDateAndTime.date` | `DD.MM.YYYY` | the current date in `DD.MM.YYYY` |
| `startDateAndTime.time` | `HH:MM` | the current time in `HH:MM` |
| `endDateAndTime.date`   | `DD.MM.YYYY` | the date 10 years from now in `DD.MM.YYYY` |
| `endDateAndTime.time`   | `HH:MM` (default `23:59`) | `23:59` |

The unset-end default of "+10 years" is the API's way of expressing "available
indefinitely" while still giving the client a comparable timestamp.

**Errors:**
- `401` — Missing or invalid Bearer token
- `404` — Project has no MART data, OR `participant_id` was provided but did not match
  any case in this project

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
- `422` — Validation error, OR `userId` does not match the case owner's email,
  OR case is completed / not accepting submissions (response includes `case_status`)
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
| `photo`     | image/jpeg, image/png, image/gif, image/webp, image/heic, image/heif |
| `video`     | video/mp4, video/quicktime, video/webm, video/3gpp, video/x-msvideo |
| `audio`     | audio/mpeg, audio/mp4, audio/wav, audio/ogg, audio/aac, audio/x-m4a, audio/webm, audio/flac |
| `document`  | application/pdf, image/jpeg, image/png |

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

The response uses `Content-Disposition: inline` with the stored `original_name`.
Filenames containing non-ASCII or special characters are encoded per RFC 5987
(`filename*=UTF-8''...`), matching Laravel's built-in behavior for file responses.

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

All endpoints are rate-limited per IP. Exceeding the limit returns `429 Too Many Requests`.

**Auth-flow endpoints (`/api/mart/*`):**

| Endpoint                    | Limit           |
|-----------------------------|-----------------|
| `check-email`               | 30 / minute     |
| `send-password-setup`       | 30 / 10 minutes |
| `check-password`            | 30 / minute     |
| `check-access`              | 30 / minute     |
| `refresh`                   | 30 / minute     |

**Authenticated MART data endpoints (`/mart-api/*`):**

| Endpoint                                  | Limit          |
|-------------------------------------------|----------------|
| `/projects/{project}/structure`           | 300 / minute   |
| `/cases/{case}/submit`                    | 300 / minute   |
| `/device-infos`                           | 300 / minute   |
| `/stats`                                  | 300 / minute   |
| `/cases/{case}/files`, `/files/{id}` (GET/DELETE) | 300 / minute |

---

## Error Responses

Per-endpoint error codes are listed under each endpoint above. The two namespaces
use different error shapes — handle both in your client.

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
