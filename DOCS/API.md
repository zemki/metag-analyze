# Mobile API Guide

**The endpoint reference is generated from the code and lives at `/docs/api`.**
Request and response schemas, status codes, parameters and a "Try It" console
are all there, and they cannot drift from the implementation. This file covers
what an OpenAPI document does not say well: how the pieces fit together, and how
to exercise them.

To read the generated docs locally, set `API_DOCS_PROTECT_ACCESS=false` in your
`.env` and open <http://localhost:8000/docs/api>. With the flag `true` — which
is what any deployment should use — the page asks for the `API_DOCS_USER` /
`API_DOCS_PASSWORD` credentials, and refuses to serve at all (503) if no
password is configured.

## Base URL

Everything the mobile app talks to is under **`/api/mobile`**:

| Path | Purpose | Auth |
|------|---------|------|
| `/api/mobile/auth/*` | The 3-screen auth flow | None (per-IP rate limited) |
| `/api/mobile/*` | Project data, submissions and files | `Authorization: Bearer {token}` |

The `auth/` segment is the rule: everything outside it needs a token. This
replaced the earlier split across `/api/mart/*` and `/mart-api/*`, which the old
URLs no longer serve.

All endpoints return JSON. No `Accept: application/json` header is required.

Separately, `/api/check-email` and `/api/send-password-setup` are an older
registration helper that takes a `project_id` and answers in a different shape.
The mobile app should use the `/api/mobile/auth` flow below.

---

## ID Conventions

| Field | Type | What it is |
|---|---|---|
| `userId` | string | The participant's email address. Must match the case owner's email. |
| `participantId` | string | The case's `name` field — any string. Auto-generated as `P` + 6 hex chars (e.g. `P1A2B3C`) when the participant registers via the 3-screen auth flow; cases created manually in the admin panel can have any string (e.g. `participant-003`). Use whatever `check-access` returns; do not assume the `P…` format. |
| `caseId` | integer | The case's numeric primary key, used only inside URL paths (e.g. `/cases/{caseId}/…`). Different from `participantId`. |
| `projectId` | integer | The project's numeric primary key. |
| `questionnaireId` | integer | The schedule's questionnaire id (1-based per project). |
| `bearerToken` | string | 30-day API access token returned by `check-password`. |
| `refreshToken` | string | 7-day rotation token returned by `check-password`. |

---

## The authentication flow

Three screens, completed in order. Each step checks that the previous one left
its mark in the cache, which is why they work without a token — and why they
expire:

| From | To | Window |
|---|---|---|
| `check-email` | `send-password-setup` | 60 seconds |
| `check-email` | `check-password` | 60 seconds |
| `check-password` | `check-access` | 5 minutes |

Miss the window and you get `403 Flow validation failed` with a `step` field
naming the screen to return to.

1. **`POST /api/mobile/auth/check-email`** — does this email exist, and is it
   verified? A new user is sent to `send-password-setup`, which emails them a
   link to set a password.
2. **`POST /api/mobile/auth/check-password`** — returns the `bearerToken`
   (30 days) and `refreshToken` (7 days).
3. **`POST /api/mobile/auth/check-access`** — confirms the participant may join
   the project and creates their case if it does not exist yet. This is where
   `participantId` comes from.

### Using and refreshing the token

Send it on every non-`auth/` endpoint:

```bash
Authorization: Bearer {bearerToken}
```

A missing or invalid token returns `401 {"message": "Unauthenticated."}`. When
that happens, POST the `refreshToken` **in the request body** (not as a header)
to `/api/mobile/auth/refresh`. Both tokens rotate on every refresh and the old
refresh token is invalidated immediately.

---

## Trying it out

You need a project with at least one questionnaire. A researcher creates one in
the web UI under **Projects → New Project**, adds a questionnaire with
questions, and reads the project id out of the URL (`/projects/{id}`).

Then walk the flow:

```bash
curl -X POST http://localhost:8000/api/mobile/auth/check-email \
  -H 'Content-Type: application/json' \
  -d '{"email":"participant@example.com"}'
```

```bash
curl -X POST http://localhost:8000/api/mobile/auth/check-password \
  -H 'Content-Type: application/json' \
  -d '{"email":"participant@example.com","password":"secret"}'
```

```bash
curl http://localhost:8000/api/mobile/projects/1/structure?participant_id=P1A2B3C \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

The `/docs/api` console does the same thing with a form instead of curl, and
carries the token between calls for you.

---

## Push notifications

Scheduled reminders reach participants over Firebase Cloud Messaging. The server
sends to whatever FCM registration tokens it holds for the user — so **the app
must register its token, or nothing is delivered**. There is no fallback: an
unregistered user is silently skipped.

```
POST /api/mobile/device-token
Authorization: Bearer <token>

{ "token": "<fcm registration token>" }
```

```json
{ "success": true, "changed": true, "registered_devices": 1 }
```

`changed: false` is not an error — it means the token was already registered.
Up to 10 tokens are kept per user (newest win), so a participant can have
several devices.

To unregister, e.g. on logout:

```json
{ "token": "<fcm registration token>", "action": "unregister" }
```

`POST /api/mobile/device-infos` also accepts an optional `fcmToken` field, which
saves a round trip at login. Use the dedicated endpoint for refreshes.

**When to call it**

| | |
|---|---|
| After notification permission is granted | register |
| Whenever FCM issues a new token | register — Android `onNewToken`, iOS `messaging:didReceiveRegistrationToken:` |
| On logout | unregister |

Tokens rotate on reinstall, restore and cleared app data. An app that registers
only once will stop receiving notifications and give no sign of it.

**Platform prerequisites.** Android needs `google-services.json` and, from
Android 13, the `POST_NOTIFICATIONS` runtime permission. iOS additionally
requires an APNs authentication key (`.p8`) uploaded to the Firebase console —
FCM relays through APNs, so without it Android works and iOS silently receives
nothing.

---

## Rate limits

Per IP. Exceeding a limit returns `429 Too Many Requests`.

| Endpoints | Limit |
|---|---|
| `/api/mobile/auth/send-password-setup` | 30 / 10 minutes |
| All other `/api/mobile/auth/*` | 30 / minute |
| Every `/api/mobile/*` data endpoint | 300 / minute |

The data limit is deliberately far above Laravel's default of 60: participants
poll `/structure` frequently, submit entries in bursts, and push device-info and
stats payloads alongside questionnaires.

---

## Error shapes

Per-endpoint codes are in the generated docs. There are two shapes, and a client
has to handle both.

Auth-flow endpoints return:

```json
{
  "error": "Flow validation failed",
  "message": "Please check your email first",
  "step": "email_check_required"
}
```

The optional `step` tells the client which screen to go back to.

Data endpoints return:

```json
{
  "success": false,
  "message": "Case does not belong to the specified project"
}
```

Validation errors (`422`) on either add an `errors` object:

```json
{
  "message": "The userId field is required.",
  "errors": {
    "userId": ["The userId field is required."],
    "participantId": ["The participantId field is required."]
  }
}
```
