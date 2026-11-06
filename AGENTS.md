# AGENTS.md

Metag-Analyze: media diary and experience sampling platform (Laravel 11, PHP 8.3, Vue 3, Vite, Tailwind 4).
It serves two mobile clients: the MetaG app (legacy `/api/v1`, `/api/v2`) and the MART app (`/api/mobile`, with `/api/mart` and `/mart-api` kept as aliases).

## Commands

```bash
vendor/bin/pest                          # backend tests (run after every backend change)
vendor/bin/pest --filter=TestName        # one test or class
npm run test:unit                        # frontend tests (Jest)
npm run cypress:run                      # e2e

php artisan migrate                      # both DBs: MART migrations set their own connection
php artisan migrate:status
php artisan migrate:fresh-all --seed     # DESTRUCTIVE: drops main AND MART DB; blocked in production
php artisan queue:work
```

Feature tests render Blade views with `@vite`, so `public/build` must exist. The user builds it before Pest runs on a fresh checkout.

## Rules

- Never run `npm run build` or `npm run dev`. The user compiles the frontend.
- Never run `php artisan serve`, `php artisan db:seed`, or Sail. The user manages server and seeding.
- Never commit or push unless asked. Provide a short commit message instead.
- Do not add documentation files unless asked.
- Backend change: run `vendor/bin/pest`. Frontend change: tell the user to rebuild.
- Tailwind is v4: `@import "tailwindcss"` in `resources/css/app.css`, plain CSS, no Sass. Use v4 class names (`shadow-xs`, `bg-gray-500/75`, `outline-hidden`).
- Filament is v4: forms use `Schema`, navigation icon and label via static methods, `User` implements `FilamentUser` and `HasName`. Read the Filament 4 docs before touching `app/Filament`.
- Check neighbouring files for conventions before adding dependencies or patterns.
- Never log or expose tokens, passwords or participant data.

## Environment

- Laravel Herd on Windows, PHP 8.3, MySQL 8, Node 22. Local URL `https://metag-analyze.test`.
- `.env.testing`: main DB `metagtesting`, MART DB `metag_marttesting`. Tests must never point at the dev databases.
- CI (`.github/workflows/backend_tests.yml`) migrates both DBs, builds the frontend, runs Pest. Jest is not in CI.

## Architecture

### Two databases
- `mysql` (main): projects, cases, entries (metadata only), users, roles, settings.
- `mart` (MART): `mart_projects`, `mart_schedules`, `mart_questions`, `mart_question_history`, `mart_entries`, `mart_answers`, `mart_pages`, `mart_stats`, `mart_device_info`, `mart_case_schedules`, `mart_files`. Models live in `app/Mart/` with `protected $connection = 'mart'`.
- No cross-DB foreign keys. Links: `mart_projects.main_project_id -> projects.id`, `mart_entries.main_entry_id -> entries.id`, `mart_case_schedules.case_id -> cases.id`, `participant_id` = `cases.name`, `user_id` = user email.

```php
$martProject = $project->martProject();   // MartProject|null, cached per request
$martEntry   = $entry->martEntry();       // MartEntry|null
if ($project->isMartProject()) { ... }    // reads the 'mart' marker in projects.inputs

DB::connection('mysql')->beginTransaction();
DB::connection('mart')->beginTransaction();
try {
    // ...
    DB::connection('mysql')->commit();
    DB::connection('mart')->commit();
} catch (\Exception $e) {
    DB::connection('mysql')->rollBack();
    DB::connection('mart')->rollBack();
    throw $e;
}
```

A MART project is a normal `projects` row whose `inputs` JSON contains one object with `type: "mart"` (project options live there) plus a `mart_projects` row. Questions live only in the MART DB.

### Core files
- `routes/mobile.php` (the MART app's API), `routes/mart_api.php` and `routes/api.php` under `prefix('mart')` (aliases), `routes/web.php` (researcher questionnaire CRUD).
- `app/Http/Controllers/MartApiController.php`: structure, submit, device-infos, stats.
- `app/Http/Controllers/MartAuthController.php`: 3-screen login, refresh.
- `app/Http/Controllers/MartFileController.php`: encrypted file uploads.
- `app/Http/Controllers/MartQuestionnaireController.php`: researcher CRUD with question versioning.
- `app/Http/Resources/Mart/*`: mobile JSON shape. `app/Mart/MartSchedule::toMobileFormat()` builds schedule options.
- `app/Exports/Mart/*`: Excel export (entries, device info, stats, files), project-wide or per case.
- `resources/js/components/mart/`: `MartQuestionnaireManager.vue`, `AddEditQuestionnaireDialog.vue`, `VersionHistoryModal.vue`; `editproject.vue` hosts them for MART projects.
- `app/Http/Middleware/ForceJsonResponse.php`: all MART routes return JSON, never a login redirect.
- Legacy, do not extend: `app/MartPage.php`, `app/MartQuestionnaireSchedule.php`, `MartScheduleController.php`, main-DB `mart_*` tables.

## MART API contract

The source of truth is `martTypes.ts` from the app developer. Keep the current copy at `DOCS/martTypes.ts` (git-ignored). If it is missing, ask the user for it before changing any API field.

Interactive docs: `/docs/api` (Scramble + Stoplight), HTTP Basic auth via `API_DOCS_USER` / `API_DOCS_PASSWORD` unless `API_DOCS_PROTECT_ACCESS=false` (default true); an empty password returns 503. See `.claude/docs/scramble-laravel-openapi.md`.

### Auth
- Flow: `/api/mobile/auth/check-email` -> `/send-password-setup` or `/check-password` -> `/check-access` -> `/refresh`. Steps are chained through cache keys (email + IP) with 1 and 5 minute windows.
- `check-password` returns `bearerToken` (30 days) and `refreshToken` (7 days). Tokens are stored as `sha256` in `users.api_token` / `users.refresh_token`; the `auth:api` guard is Laravel's token driver.
- `check-access` auto-creates a case named `P` + 6 hex chars and sets `cases.first_login_at`.
- Everything under `/api/mobile` outside `auth/` needs `Authorization: Bearer`.
- `/api/mart/*` and `/mart-api/*` are aliases with no removal date. Add new endpoints to `routes/mobile.php`, and to `routes/mart_api.php` when the alias needs them. `/docs/api` lists `/api/mobile` only.

### Response shape (structure endpoint)
- Keys: `projectOptions`, `questionnaires`, `scales`, `pages`, `deviceInfos`, `repeatingSubmits`, `singleSubmits`, `lastDataDonationSubmit`, `lastAndroidStatsSubmit`.
- Dates `DD.MM.YYYY`, times `HH:MM`. Use `questionnaireId` (not `sheetId`), `randomizationGroupId`, `iOSDataDonationQuestionnaire`, `androidDataDonationQuestionnaire`, `lastDataDonationSubmit`.
- Items: `itemId` is `index + 1` within the questionnaire. `scaleId` is `questionnaireId * 1000 + index + 1`, unique across the payload, and items with identical options share one. Both are `null` for `display` items.
- Answers on submit are keyed by `itemId`: the question's place in position order, not its `position`, which the editor saves from 1.
- Scales have `scaleId` and `options` only (no `name`, no `projectId`). Range defaults go in `rangeOptions`.
- Pages carry both `id` and `pageId`.
- Participant fields are filled when `participant_id` is passed; an unknown `participant_id` is a 404.
- Data-donation schedules are excluded from `singleQuestionnaires`.

### Question types
| Storage (`mart_questions.type`) | API `options.type` | Notes |
|---|---|---|
| `display` | none | text only, no scale |
| `number` | `number` | `minValue`, `maxValue`, `maxDigits` |
| `range` | `range` | `rangeOptions` {minValue, maxValue, steps, defaultValue} |
| `text` / `textarea` | `text` / `textarea` | optional `placeholder` |
| `one choice` | `radio`, or `radioWithText` when `config.includeOtherOption` | `radioOptions` [{value, text}] |
| `multiple choice` | `checkbox`, or `checkboxWithText` | `checkboxOptions` |
| `photo` / `audio` / `video` | `photoUpload` / `audioUpload` / `videoUpload` | optional `*UploadOptions` passed through |

Not implemented yet: `rangeValues`, `fileUpload`.

Question-level options (stored in `config`, emitted under `items[].options`): `randomizationGroupId`, `randomizeAnswers`, `randomPositionWithinQuestionnaire`, `itemGroup`. `noValueAllowed` is derived (`! is_mandatory`, always true for `display`). `posterUrl` is stored in `config` too but emitted on the item. `timer` and `jump` go under scale `options`. Question text may contain HTML. `image_url` / `video_url` are columns and appear as `imageUrl` / `videoUrl`.

Editing a question writes a `mart_question_history` row and bumps `version`; answers store `question_uuid` plus `question_version`.

### Schedules
- `type` is `single` or `repeating`. Config lives in `timing_config` and `notification_config` JSON.
- Dynamic dates: `start_on_first_login` (plus `start_hours_after_login`) and `use_dynamic_end_date` are backend-only. On first login `MartCaseSchedule::calculateForCase()` writes concrete dates to `mart_case_schedules`; the app only ever receives dates.
- `max_total_submits` is backend-only: duration in days = ceil(max_total_submits / max_daily_submits).
- `is_ios_data_donation` and `is_android_data_donation` are mutually exclusive per schedule.

### Submissions and files
- Completed cases reject submissions with 422. `userId` must match the case owner's email.
- Each submit writes a main `entries` row (metadata only) plus `mart_entries` and `mart_answers`.
- `device-infos` and `stats` return 403 when `userId` is not the bearer token's account.
- Push: `POST /api/mobile/device-token` (or `fcmToken` on `device-infos`) stores FCM tokens in `users.deviceID`, newest 10. `notification:cleanID` clears them once the latest case has ended.
- Files: `POST /api/mobile/cases/{case}/files` accepts multipart (`file`) or JSON base64. MIME is detected from content and checked against `MartFile::ALLOWED_MIME_TYPES`; 50 MB max; stored encrypted under `storage/app/mart/`. Upload first, then reference the returned `file_id` in the answer.

## Admin and settings
- Filament panel at `/admin`. Settings keys: `mart_enabled`, `max_studies_per_user`, `api_v2_cutoff_date`, `mart_max_login_attempts`, `mart_lockout_duration`. Read with `Setting::get('key', $default)`.
- `BLOCKED_IPS` in `.env` (comma-separated) returns 403 for all routes via `BlockIpMiddleware`.
- Roles are database rows: `roleadd:*`, `roleremove:*` (admin, researcher, user), `role:cleanup`. `ADMINS` in `.env` grants no role; it controls the Telescope gate and the unverified-users report.
- `mail:sendtest {email}` checks the mail setup. `TRUSTED_PROXIES` lists the proxies in front of the app.
- Legacy MetaG uploads are validated by content in `Helper::extension()`; only audio and image types pass.

## Gotchas
- "entity" replaced "media" in the UI and API v2, but tables and columns are still `media`. MART projects have no media/entity and no backend-only cases.
- Projects created before `api_v2_cutoff_date` use API v1 (`media`), later ones v2 (`entity`). `FORCE_API_V2` overrides.
- Mobile timestamps arrive in milliseconds for MART and in seconds for legacy entries.
- Never call `env()` outside `config/`: it returns null under `config:cache`.
- A plain `throttle:N,M` keys its counter by user or IP only, so limits on the same route or sharing a window share one counter. Give each limit its own third argument, as `routes/api.php` and `routes/mobile.php` do.
- `Cases::isConsultable()` is also true when a case has no `lastDay`, which is how MART cases start.
- `APP_ENV=production` assumes the `/metag` subfolder (Livewire, Telescope, graph breadcrumb). metagtest runs `APP_ENV=local`, so environment checks do not protect it.
- Seeders must use `bcrypt()`, not `Hash::make()`.
- Trigger migrations need `SUPER` or `log_bin_trust_function_creators=1`; they skip with a warning otherwise.
- Case lists and exports for MART come from the MART DB; the main `entries` row holds no answers.

## Docs
- `DOCS/MART/API.md` (endpoint reference), `DOCS/MART/DATA_MAPPING.md`, `DOCS/MART/README.md`.
- `DOCS/ARCHITECTURE.md`, `DOCS/INSTALLATION.md`, `DOCS/DEPLOYMENT.md`, `DOCS/ADMIN_GUIDE.md`.
- `.claude/docs/bruno-api-client.md`, `.claude/docs/scramble-laravel-openapi.md`.
