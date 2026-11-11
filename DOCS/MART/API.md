# MART API Reference

> **Interactive docs:** For auto-generated API docs with "Try It" feature,
> visit `/docs/api` when running the app locally. This file serves as a
> quick-start flow guide. For exact request/response schemas, refer to the
> interactive docs.

Everything the MART app talks to lives under `/api/mobile`:

| Prefix             | Purpose                    | Auth                            |
|--------------------|----------------------------|---------------------------------|
| `/api/mobile/auth` | Auth flow (email/password) | None (per-IP rate limited)      |
| `/api/mobile`      | Project data & files       | `Authorization: Bearer {token}` |

The `auth/` segment is the rule: everything outside it needs a token.

All endpoints return JSON. No `Accept: application/json` header is required.

**Older URLs keep working.** `/api/mart/*` answers like `/api/mobile/auth/*`, and `/mart-api/*`
like `/api/mobile/*`, with the same paths after the prefix. They run the same code and have
the same limits. There's no removal date, but new code should use `/api/mobile`.

---

## ID Conventions

| Field | Type | What it is |
|---|---|---|
| `userId` | string | The participant's email address. Must match the case owner's email, ignoring case. |
| `participantId` | string | The case's `name` field — any string. Auto-generated as `P` + 6 hex chars (e.g. `P1A2B3C`) when the participant registers via the 3-screen MART auth flow; cases created manually in the admin panel can have any string (e.g. `participant-003`). Use whatever `check-access` returns; do not assume the `P…` format. |
| `caseId` | integer | The case's numeric primary key, used only inside URL paths (e.g. `/cases/{caseId}/...`). Different from `participantId`. |
| `projectId` | integer | The project's numeric primary key. |
| `questionnaireId` | integer | The schedule's questionnaire id (1-based per project). |
| `itemId` | integer | A question's place in its questionnaire, from 1. It restarts in each questionnaire and is `null` for `display` items. |
| `scaleId` | integer | The scale an item uses, unique across the whole payload. `null` for `display` items, unless the item shows answer options, disabled. |
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
| `check-email` | `send-password-setup` | 10 minutes |
| `check-email` | `check-password` | 10 minutes |
| `check-password` | `check-access` | 5 minutes |

Exceeding the window returns `403 Flow validation failed` with a `step` hint pointing
the client back to the screen it needs to redo.

### Screen 1: Check Email
```
POST /api/mobile/auth/check-email
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
POST /api/mobile/auth/send-password-setup
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
POST /api/mobile/auth/check-password
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
POST /api/mobile/auth/check-access
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
POST /api/mobile/auth/refresh
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

All `/api/mobile/*` endpoints require the header:
```
Authorization: Bearer {bearerToken}
```
A missing or invalid token returns `401`:
```json
{ "message": "Unauthenticated." }
```

When you receive `401` on an authenticated endpoint, exchange your `refreshToken` at
`POST /api/mobile/auth/refresh` to get a fresh pair. Both tokens are rotated on every refresh,
and the old `refreshToken` is invalidated immediately.

Auth-flow endpoints (`/api/mobile/auth/*`) do **not** require a Bearer token; they use cached
flow state (see Screen 1 / 2 / 3 above) plus per-IP rate limits.

---

## Authenticated Endpoints

### Get Project Structure
```
GET /api/mobile/projects/{projectId}/structure
GET /api/mobile/projects/{projectId}/structure?participant_id={participantId}
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
        "collectAndroidStats": false,
        "minBreakBetweenDataDonationQuestionnaires": 1,
        "minBreakBetweenAndroidStats": 6
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

**Minimum breaks** (`projectOptions.options`, both optional, in hours, decimals allowed):
- `minBreakBetweenDataDonationQuestionnaires`: the minimum break between two data donation
  questionnaires. It only matters when the project has one.
- `minBreakBetweenAndroidStats`: the minimum break between two Android stats collections,
  which run after a single or repeating questionnaire is submitted.
- Both are stored in `projects.inputs`, in the MART entry's `projectOptions`. The app applies
  a break only when its key is present, so a key is sent only when its value is a number of
  0 or more, and left out when empty. The editor deletes the key when its field is emptied,
  negative or not a number.

**Query params:**
- `participant_id` *(optional)* — when provided, the response also includes per-case data
  (submissions, device info) and applies per-case date overrides to schedules.
  If omitted and the request is Bearer-authenticated, the controller falls back to the
  authenticated user's case (auto-creating one if none exists).
  **If a `participant_id` is provided but does not match any case in the project, the
  endpoint returns `404`** (it does not silently fall back).

**Per-participant fields in the response:**
- All of them come from this project only, as in Directus (projectId + participantId).
  Case names can repeat across projects, so another project's participant of the same
  name never shows up. `deviceInfos` has no project column, so it also matches the
  case owner's email.
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
| `text` | `text` | single-line text input, with an optional `textOptions.placeholder` |
| `textarea` | `textarea` | multi-line text input, with an optional `textOptions.placeholder` |
| `number` | `number` | integer or decimal entry |
| `range` | `range` | slider with `minValue` / `maxValue` / `steps` |
| `rangeValues` | `range values` | slider with a label on each step, from `rangeValueOptions` |
| `radio` (a.k.a. `one choice`) | `one choice` | single-select, optionally with an "other" option |
| `checkbox` (a.k.a. `multiple choice`) | `multiple choice` | multi-select, optionally with an "other" option |
| `photoUpload` | `photo` | participant uploads via `POST /api/mobile/cases/{caseId}/files`, then references the returned `file_id` in the answer |
| `audioUpload` | `audio` | same flow as `photoUpload` |
| `videoUpload` | `video` | same flow as `photoUpload` |
| `fileUpload` | `file` | any file, within `fileUploadOptions`; same flow as `photoUpload` |
| `display` | `display` | non-input element (instructions, headers). It has no `type` of its own: with answer options it gets a `radio` or `checkbox` scale, shown disabled, see below |

For upload types the mobile uploads the bytes first, then submits the entry. The app's
answer is `[fileUrl, file_id]`: the `fileUrl` from the upload response with the app's API
base in front, then the `file_id` (UUID). The submit links the file whose UUID is in the
answer to the entry, if it belongs to the submitting case and isn't linked yet, and sets its `question_uuid` to
the answered question, replacing the zero UUID the app uploads with. This works the same for
every upload type. See the "File Upload" / "Retrieve File" sections below.

When a researcher configures upload-specific options in the admin (e.g.
`allowFileSelection`, `quality` for photo; `maxFileSize`, `maxAudioLength`
for audio; `maxVideoLength`, `maxFileSize` for video), the API emits them
under `scaleOptions.photoUploadOptions` / `audioUploadOptions` /
`videoUploadOptions` respectively. The full field set per upload type is
defined in `martTypes.ts` — the API passes whatever the researcher set
through verbatim; unset fields are omitted, and the mobile applies its own
defaults. `fileUploadOptions` is the exception, see "File uploads" below.

**File uploads:** a `fileUpload` scale carries `fileUploadOptions` with `maxFileSize` (MB)
and `allowedFormats`. The app checks the picked file before it uploads: its size against
`maxFileSize`, and `"."` plus its lowercased extension against `allowedFormats`, which is
also the picker's accept list. So the API sends `maxFileSize` as a number and each format
lowercase with one leading dot, listed once, and leaves out a key without a value: the app
reads a missing one as no limit. `martTypes.ts` also names a `fileType`, which the app never
reads, so it isn't sent. The questionnaire routes reject a `file` question with a 422 unless
`maxFileSize` is from 1 to 50 and `allowedFormats` lists at least one format, each one from
`MartFile::FILE_UPLOAD_EXTENSIONS` (any case, with or without the dot; a blank entry is
refused). Otherwise the app takes a file the upload endpoint refuses, and the app stops the
whole submit. Those are the extensions whose files the server takes, and the ones the editor
offers. `.svg` is left out because an inline SVG can run script on our origin.

```json
{
  "type": "fileUpload",
  "fileUploadOptions": { "maxFileSize": 20, "allowedFormats": [".jpg", ".jpeg", ".pdf"] }
}
```

**"Other" options:** there's no separate type. An "other" option is the last entry in
`radioOptions` or `checkboxOptions`, with the value `"text"` and the researcher's label, or
`Other` if there is none. `textOptions.type` says which field the app shows, `text` or
`textarea`. The value stays `"text"` for both: the app only enables a checkbox's field for
`"text"`.

```json
{
  "type": "radio",
  "radioOptions": [
    { "value": 1, "text": "Yes" },
    { "value": 2, "text": "No" },
    { "value": "text", "text": "Sonstiges" }
  ],
  "textOptions": { "type": "textarea" }
}
```

**Sliders with labels:** a `rangeValues` scale has no "other" option and no jump. The app
slides from the first `value` to the last and shows the label of the chosen value, so the values
run from 1 in order. Every label is a step: a blank one is sent with empty `text`. The
questionnaire routes reject a `range values` question with fewer than 2 options with a 422,
because the app crashes on an empty list. A default sits next to `rangeValueOptions`, at the top
of `options`. Without one, the app records nothing until the participant moves the slider: an
optional question sends `[]`, and a mandatory one keeps Next disabled.

```json
{
  "type": "rangeValues",
  "rangeValueOptions": [
    { "value": 1, "text": "sehr schlecht" },
    { "value": 2, "text": "ok" },
    { "value": 3, "text": "sehr gut" }
  ],
  "defaultValue": 2
}
```

**Answer options on a text-only item:** the app greys out the options of any item whose
`itemId` is `null`, and sends no answer for it. A `display` question with `displaySelection`
(`radio` or `checkbox`) in its config gets the scale a single or multiple choice question with
the same options gets, "other" option included, so the two can share one scale: the app copies
the scale for each item and decides from the item's `itemId` whether its options can be picked.
The item keeps `itemId: null`, `noValueAllowed: true` and `randomizeAnswers: false`, and still
takes its place, so later items keep their `itemId`. Its scale never has a `defaultValue` or a
`jump`, whatever the config holds: the app copies a default into the answer and jumps on it even
without an `itemId`, so a display item could skip questions unseen. A timer is sent, see
"Timers" below. The questionnaire routes reject a `displaySelection` other than `radio` or
`checkbox`, fewer than 2 options, or a blank one, with a 422. Without `displaySelection`, a
`display` item has no scale, so no timer either.

```json
{
  "itemId": null,
  "scaleId": 1008,
  "text": "Es wird eine deaktivierte Auswahl angezeigt",
  "options": { "randomizationGroupId": null, "randomizeAnswers": false, "randomPositionWithinQuestionnaire": false, "itemGroup": null, "noValueAllowed": true }
}
```

**Option values:** `radio`, `checkbox` and `rangeValues` options are numbered from 1, in their
saved order. That's the option number in the editor, the value in an answer and the value in an
export. `mart_questions.config` keeps 0-based keys, and the API adds 1 to `value`, `jumpCondition`
and a choice `defaultValue`. Values can't start at 0 because the app drops a `radio` value of 0.
It does the same to a `number` or `range` answer of 0, which arrives as `[]`, like a skipped
question. Only an app change can fix that.

**Default answers:** `defaultValue` sits at the top of a scale's `options` for every type,
and is only there when the researcher set one. It's a number for `range` and `number`, an
option's `value` for `radio`, `checkbox` and `rangeValues`, and a string for `text` and
`textarea`. The app has two limits: it ignores a `range` default of `0`, and it never applies
one to `number`. `range` also
keeps `rangeOptions.defaultValue`, which `martTypes.ts` requires and which the app doesn't
read: that one falls back to `minValue`.

**Jumps:** `jump` sits in a scale's `options` as `{ "jumpCondition": 1, "jumpOver": 2 }`.
`jumpCondition` is always a number, the `value` of the option that triggers the jump. The app
compares it strictly with the chosen `radio` value, so a `checkbox` scale can't trigger a jump.

**Timers:** `timer` sits in a scale's `options` as `{ "time": 30, "showCountdown": true }`, with
`time` in seconds. The app starts it when the item opens, whatever the item's `itemId`. Next does
nothing until the timer runs out, then the app moves on by itself. On an item with
`noValueAllowed: true`, a display item included, the button isn't greyed out meanwhile.
When a timed item is the last one, that move goes past the end: the app shows a blank page with a
disabled "Weiter", and the participant has to tap Back to reach "Absenden".

**Item options** (`questionnaires[].items[].options`): the block is always present, and the
values come from the question's configuration.

| Field | Default | Notes |
|---|---|---|
| `randomizationGroupId` | `null` | Groups questions for randomized presentation |
| `randomizeAnswers` | `false` | Shuffles the answer options. The app and the editor use it for `radio` and `checkbox` only. |
| `randomPositionWithinQuestionnaire` | `false` | The app moves the item to a random place. From `config.randomPositionWithinQuestionnaire`, which the editor saves only when ticked. See below. |
| `itemGroup` | `null` | String. Items in a group show together on one page. |
| `noValueAllowed` | | The inverse of "mandatory": `true` for optional questions. Always `true` for `display` items. |

**Random position:** the app reads `randomPositionWithinQuestionnaire` only for an item without a
`randomizationGroupId`, and not for the first such item right after a randomization group, which
stays in place. An item in a group is shuffled within its group instead; the editor shows a hint
when both are set. The app takes the moved items out, then puts them back one at a time, each at
a random index from 1 to the list's current length minus 1: never first, and never last while at
least two other items stay in place. Afterwards, a flagged item whose two neighbours share an
`itemGroup` (both empty counts) takes their `itemGroup`. Answers stay keyed by `itemId`. The
questionnaire routes take only `true`, `false` or `null` in the config, with a 422 otherwise.

An item can also carry `imageUrl`, `videoUrl` and `posterUrl` next to `options`. They're left
out when empty. `imageUrl` and `videoUrl` come from the `image_url` and `video_url` columns,
`posterUrl` from `config.posterUrl`. The app shows the poster on a video file until it plays, and
not on a YouTube or Vimeo link, which it embeds. The editor offers the poster only with a video
URL and drops it when the video URL is cleared. The questionnaire routes check it like
`image_url` and `video_url`: a URL of at most 2048 characters, or a 422. On the questions route,
an `image_url` or `video_url` sent as null clears the column.

**Scale IDs:** the app looks a scale up by `scaleId` over the whole `scales` list, so an id
never repeats across questionnaires. It's `questionnaireId * 1000 + index + 1`, where `index`
is the item's place in its questionnaire, from 0. Items with identical `options` share the
first item's scale, so `scales` can be shorter than the list of items. Don't store these ids:
they can change when a questionnaire is edited. Submits use `itemId` and exports use the
question's UUID.

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
POST /api/mobile/cases/{caseId}/submit
```
```json
{
  "projectId": 123,
  "questionnaireId": 1,
  "userId": "user@example.com",
  "participantId": "P1A2B3C",
  "questionnaireStarted": 1234567890000,
  "questionnaireDuration": 300000,
  "answers": { "1": 5, "2": [1, 2] },
  "timestamp": 1234567890000,
  "timezone": "Europe/Berlin"
}
```
`answers` is an **object** keyed by `itemId` (1-based question index), not an array.
Values can be numbers (scale), arrays (multiple choice), strings (text), or file UUIDs
returned from File Upload. The app sends every value as an array, such as `[1]`. An upload
answer is `[fileUrl, file_id]`, stored as sent; the export shows both, comma-separated. An "other"
answer holds the option's value and the typed text, such as `["text", "typed words"]`, and the
export shows it as `text, typed words`. A `rangeValues` answer is the chosen option's `value`:
a number such as `[2]` when the participant keeps the default, and a string such as `["2"]`
once they move the slider. Both are stored as sent, and the export shows `2`.

The app sends a key for every item with an `itemId`, `[]` when it's unanswered, and none for
a `display` item. So a `display` item is never required, even one saved as mandatory, and the
export has no column for it. A questionnaire of display items only gives `answers: {}`: the key
must be there, but it can be empty.

> **Deprecated:** `sheetId` is accepted for backward compatibility but ignored.
> Use `questionnaireId` instead.

**Response (200):**
```json
{ "success": true, "entry_id": 42, "message": "Entry created successfully" }
```
`entry_id` is the submission's `mart_entries` id. Submissions have no row in the main `entries`
table.

**Errors:**
- `400` — Case does not belong to `projectId`
- `401` — Missing or invalid Bearer token
- `404` — Project or questionnaire schedule not found
- `422` — Validation error, OR `userId` does not match the case owner's email (ignoring case),
  OR case is completed / not accepting submissions (response includes `case_status`)
- `500` — Cross-DB transaction failed (both main and MART DBs rolled back)

### Store Device Info
```
POST /api/mobile/device-infos
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
  "timezone": "Europe/Berlin",
  "fcmToken": "optional-fcm-registration-token"
}
```
`userId`, `projectId`, and `participantId` must match the case created during `check-access`,
and `userId` must be the account the Bearer token belongs to.

`fcmToken` is optional and saves a second call at login. It's stored on that account. Use
Register Device Token below for token refreshes.

**Response (200):**
```json
{
  "success": true,
  "message": "Device information stored successfully",
  "device_info_id": 1,
  "push_token_registered": true
}
```

**Errors:**
- `401` — Missing or invalid Bearer token
- `403` — `userId` is not the token's account, or `userId` / `participantId` / `projectId`
  do not match an existing case
- `404` — User not found
- `422` — Validation error (missing or invalid fields)

### Register Device Token
```
POST /api/mobile/device-token
```
```json
{ "token": "fcm-registration-token" }
```
Reminders reach participants through Firebase Cloud Messaging, so **the app must register
its token or nothing is delivered**. Tokens belong to the account the Bearer token identifies.

To remove a token, for example on logout:
```json
{ "token": "fcm-registration-token", "action": "unregister" }
```

**Response (200):**
```json
{ "success": true, "changed": true, "registered_devices": 1 }
```
`changed: false` isn't an error: the token was already registered, or already gone. Each
account keeps its 10 newest tokens, and tokens are removed once the participant's case has ended.

| Call it | Action |
|---|---|
| After notification permission is granted | `register` (the default) |
| Whenever FCM issues a new token | `register` |
| On logout | `unregister` |

**Errors:**
- `401` — Missing or invalid Bearer token
- `422` — Validation error

### Submit Stats
```
POST /api/mobile/stats
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
- `403` — `userId` is not the token's account, or `userId` / `participantId` / `projectId`
  do not match an existing case
- `404` — User or MART project not found
- `422` — Validation error

### File Upload
```
POST /api/mobile/cases/{caseId}/files
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

The app uses mode B. It reads the file as a `data:` URL and sends what follows the first
comma, with `question_uuid` `"00000000-0000-0000-0000-000000000000"` and `file_type` from
the scale type: `photo`, `video`, `audio`, or `file` for a `fileUpload` question. When an
upload fails, the app sets "File upload failed." on that upload item and stops the submit, so
no answer of the questionnaire is sent. The message is usually off screen, and a retry fails
the same way.

**Common rules (both modes):**
- Only the participant who owns the case can upload to it: another account gets `403`
- `file_type` — one of `photo`, `video`, `audio`, `document`, or `file`, which names no
  bucket and is inferred from the detected MIME like a missing `file_type`
- `question_uuid` *(optional)* — stored as sent. The submit that links the file replaces it
  with the question the answer belongs to
- Max size: 50MB, checked on the decoded file. That's also the largest `maxFileSize` a
  `fileUpload` question can have, which the app checks before it uploads. Whether the server
  takes a file that size depends on its `post_max_size`, `memory_limit` and nginx body limit,
  because the base64 body is about 1.37 times the file. Measured without the framework, a
  20 MB file is a 27 MB body and peaks at about 180 MB of memory; a 50 MB file is a 68 MB body
  and about 440 MB.
- Files are validated by actual content (not declared MIME) and encrypted at rest
- When a MIME type maps to multiple buckets (e.g. `image/jpeg` → `photo` or `document`),
  the first match wins in declaration order (`photo` wins over `document`). Send
  `file_type` explicitly if you need the other bucket.

**Allowed MIME types:**
| `file_type` | Accepted MIME types |
|-------------|---------------------|
| `photo`     | image/jpeg, image/png, image/gif, image/webp, image/heic, image/heif |
| `video`     | video/mp4, video/quicktime, video/webm, video/3gpp, video/x-msvideo |
| `audio`     | audio/mpeg, audio/mp4, audio/wav, audio/x-wav, audio/ogg, audio/aac, audio/x-hx-aac-adts, audio/x-m4a, audio/webm, audio/flac, audio/x-flac, video/mp4, video/webm, video/3gpp |
| `document`  | application/pdf, image/jpeg, image/png |

Files not matching the whitelist are rejected with `422` and logged.

**Audio recordings:** the app records with the first of `audio/mp4`, `audio/webm;codecs=opus`,
`audio/webm` and `audio/ogg;codecs=opus` that the phone supports, and uploads the recording
with `file_type` `audio`, as it does a picked audio file. So recordings arrive in mp4, webm,
ogg or 3gp containers, and the content check reads most of them as video (`video/mp4`,
`video/webm`, `video/3gpp`). The `audio` bucket accepts those types too. Sent without a
`file_type`, or with `file`, the same content still lands in `video`, because `video` comes
first. `audio/x-wav`, `audio/x-hx-aac-adts` and `audio/x-flac` are the names libmagic gives
WAV, ADTS AAC and (in older versions) FLAC files.

**Response (201):**
```json
{
  "success": true,
  "file_id": "uuid-string",
  "fileUrl": "/api/mobile/files/uuid-string",
  "file_type": "photo",
  "mime_type": "image/jpeg",
  "size": 12345
}
```
Use `file_id` (UUID) in questionnaire answer values for file-type questions.
Use `fileUrl` to retrieve the file via `GET /api/mobile/files/{file_id}`. `fileUrl` is a
path: the app puts its API base in front of it.

**Errors:**
- `400` — Case does not belong to a MART project, or base64 decode failed (base64 mode only)
- `401` — Missing or invalid Bearer token
- `403` — The Bearer token's account does not own the case
- `422` — Validation error, or file content is not in the allowed MIME whitelist,
  or file exceeds 50MB
- `500` — File storage failure

### Retrieve File
```
GET /api/mobile/files/{fileId}
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
DELETE /api/mobile/files/{fileId}
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

Exceeding a limit returns `429 Too Many Requests`.

**Auth-flow endpoints (`/api/mobile/auth/*`), per IP:**

| Endpoint                    | Limit           |
|-----------------------------|-----------------|
| `send-password-setup`       | 30 / 10 minutes |
| All other auth endpoints, together | 30 / minute |

**Data endpoints (`/api/mobile/*`), per account:**

| Endpoint                                  | Limit          |
|-------------------------------------------|----------------|
| All data endpoints, together              | 300 / minute   |

---

## Error Responses

Per-endpoint error codes are listed under each endpoint above. Auth-flow and data
endpoints use different error shapes — handle both in your client.

**Auth-flow endpoints** (`/api/mobile/auth/*`) return:
```json
{
  "error": "Flow validation failed",
  "message": "Please check your email first",
  "step": "email_check_required"
}
```
The optional `step` field tells the client which screen to return to.

**Data endpoints** (`/api/mobile/*`) return:
```json
{
  "success": false,
  "message": "Case does not belong to the specified project"
}
```
Validation errors (`422`) on both additionally include an `errors` object:
```json
{
  "message": "The userId field is required.",
  "errors": {
    "userId": ["The userId field is required."],
    "participantId": ["The participantId field is required."]
  }
}
```
