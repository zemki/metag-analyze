# CLAUDE.md

## Project Overview

Metag-Analyze: Mobile experience sampling platform (Laravel + Vue 3 + Inertia.js) for MART data collection.

## Bash Commands

```bash
# Testing - ALWAYS run after code changes
vendor/bin/pest                    # Run all tests
vendor/bin/pest --filter=TestName  # Run specific test

# Build & Quality Checks
npm run build                       # Production build
npm run lint:check                  # Check linting
npm run format:check                # Check formatting
npm run test:unit                   # Frontend tests

# Database
php artisan migrate                 # Run migrations
php artisan migrate:status          # Check status

# Database Reset (DESTRUCTIVE - Local Only)
php artisan migrate:fresh-all --seed  # 🚨 Drops BOTH main & MART databases, then seeds
                                      # BLOCKED in production environment
                                      # Requires typing "DELETE ALL DATA" to confirm
                                      # Automatically clears all caches

# Background Services
php artisan queue:work              # Queue worker
php artisan reverb:start            # WebSocket server
```

## Code Style

- Use Laravel conventions for backend
- Use Vue 3 Composition API for components
- ALWAYS test with `vendor/bin/pest` after backend changes
- ALWAYS build with `npm run build` after frontend changes
- Use `App\Models\Team` not `Laravel\Jetstream\Team` in tests
- Check neighboring files for patterns before adding dependencies

## Styling & Frontend (UPDATED: 2025-11-19)

### Tailwind CSS v4
- **IMPORTANT**: Project uses Tailwind CSS v4 (NOT v3)
- Migrated from Sass to plain CSS (`resources/css/app.css`)
- Uses `@tailwindcss/vite` plugin (configured in `vite.config.mjs`)
- CSS custom properties used instead of Sass variables (e.g., `var(--primary)`)

### Key Tailwind v4 Breaking Changes Applied
- `shadow-sm` → `shadow-xs`
- `bg-opacity-75` → `bg-gray-500/75` (inline opacity syntax)
- `outline-none` → `outline-hidden`
- `@tailwindcss/forms` loaded via `@plugin` directive in CSS, not in config

### CSS Files
- **Main CSS**: `resources/css/app.css` (NOT `resources/sass/app.scss`)
- **Variables**: CSS custom properties in `:root` selector
- **Tailwind Import**: `@import "tailwindcss";` (v4 syntax)

## Workflow

1. Make changes
2. Run `vendor/bin/pest` for backend changes
3. Run `npm run build` for frontend changes
4. Verify no errors before proceeding

## Repository Etiquette

### NEVER Do These
- Use `./vendor/bin/sail` commands
- Run `php artisan serve` (user manages server)
- Run `npm run dev` (user manages compilation)
- Run `php artisan db:seed` (user manages seeding)
- Create files unless absolutely necessary
- Add documentation files unless explicitly requested
- Expose or log sensitive data

## Security Configuration (UPDATED: 2025-11-24)

### IP Address Blocking
- **Environment Variable**: `BLOCKED_IPS` in `.env`
- **Format**: Comma-separated list of IP addresses
- **Example**: `BLOCKED_IPS=45.93.9.139,193.168.141.21,45.86.86.223`
- **Behavior**: Blocked IPs receive a 403 Forbidden response for all requests
- **Leave empty to disable**: `BLOCKED_IPS=`
- **Location**: `app/Http/Middleware/BlockIpMiddleware.php`
- **Usage**: Updates take effect immediately after changing `.env` (no deployment required)

**Important Notes:**
- Use this for blocking known malicious IPs during active attacks
- For advanced blocking scenarios, consider implementing database-backed IP blocking via Filament Admin Panel
- This middleware applies to all routes (web, API, admin)

### File Upload Security
- **Location**: `app/Helpers/Helper.php` (`extension()` method)
- **Security Measure**: Content-based MIME type validation using PHP's `finfo`
- **Protection**: Prevents MIME type spoofing attacks (e.g., uploading PHP files disguised as audio/image)
- **Whitelist**: Only allows audio formats (mp3, m4a, aac, wav, ogg, webm, flac) and image formats (jpg, png, gif, webp, svg)
- **Validation**: Files are validated based on their ACTUAL content, not the declared MIME type in the data URI
- **Storage**: Files are encrypted and stored in `storage/app/project{id}/files/` (not web-accessible)
- **Used By**: `app/Files.php` for mobile app file uploads (audio recordings, images)

**Security Features:**
- Decodes base64 data and inspects actual file content
- Rejects any file type not in the whitelist
- Logs security validation failures for monitoring
- Throws exception to prevent insecure file storage
- Maps detected MIME types to safe file extensions

**Important:**
- If file upload fails with "File validation failed", check Laravel logs for details
- Only legitimate audio/image files will be accepted
- Any attempt to upload PHP, executable, or other dangerous files will be rejected

## Core Files

- `app/Http/Controllers/MartApiController.php` - MART API endpoints
- `app/Http/Controllers/ProjectController.php` - Project management
- `app/Cases.php` - Case management and notifications
- `routes/mart_api.php` - MART API routes
- `resources/js/Pages/Project/` - Project UI components

## Developer Environment

- Local URL: `https://metag-analyze.test`
- Laravel Herd or Valet (NOT Sail)
- PHP 8.3+, MySQL 8.0+, Node.js 18+
- Test database separate from development

## MART Database Separation (UPDATED: 2025-09-30)

### Dual-Database Architecture
MART data is now stored in a separate database for better scalability and isolation:

**Main Database (`mysql`):**
- Projects, Cases, Entries (metadata only), Users, Roles, etc.
- Entry model has `mart_entry_id` column linking to MART DB

**MART Database (`mart`):**
- MartProject, MartSchedule, MartQuestion, MartQuestionHistory
- MartEntry, MartAnswer, MartPage, MartStat, MartDeviceInfo
- All MART-specific data with UUID-based question tracking

### IMPORTANT: Cross-Database Patterns

```php
// Getting MART project from main project
$martProject = $project->martProject(); // Returns MartProject or null
if ($martProject) {
    $schedules = MartSchedule::forProject($martProject->id)->get();
}

// Getting MART entry from main entry
$martEntry = $entry->martEntry(); // Returns MartEntry or null

// Cross-DB transactions
$mainDbTransaction = DB::connection('mysql')->beginTransaction();
$martDbTransaction = DB::connection('mart')->beginTransaction();
try {
    // ... operations ...
    $mainDbTransaction->commit();
    $martDbTransaction->commit();
} catch (\Exception $e) {
    $mainDbTransaction->rollBack();
    $martDbTransaction->rollBack();
}

// Always check if MART data exists before querying
if ($project->hasMartData()) {
    // Query MART DB
}
```

### Key MART Models (in app/Mart/)
- **MartProject** - Links to main projects via `main_project_id`
- **MartSchedule** - Questionnaire schedules with timing/notification config
- **MartQuestion** - Individual questions with UUIDs (stable across edits)
- **MartEntry** - Submission metadata, links to main entries
- **MartAnswer** - Individual answers linked to question UUIDs

## MART API Specifics

### Multiple Questionnaires Per Project (UPDATED: 2025-09-30)
- Each MART project can have unlimited questionnaire schedules (single and repeating)
- Each schedule has questions stored in `mart_questions` table (MART DB)
- Questions have UUIDs for stable tracking across versions
- Questions are always editable with automatic version tracking via MartQuestionHistory
- Each submission tracks question UUID and version for accurate data analysis
- Frontend UI complete: Schedule manager with add/edit/history functionality
- See `MART_SEPARATION_PROGRESS.md` for implementation details

### Frontend Components (Multiple Questionnaires)
- `resources/js/components/mart/MartQuestionnaireManager.vue` - Main questionnaire list/manager
- `resources/js/components/mart/AddEditQuestionnaireDialog.vue` - Create/edit questionnaires and questions
- `resources/js/components/mart/VersionHistoryModal.vue` - View question version history
- Integrated into `resources/js/components/editproject.vue` for MART projects only

### MART API Type Specification
- **Location**: `DOCS/martTypes.ts` (user-managed, git-ignored)
- TypeScript definitions for mobile app API contract
- Defines structure for questionnaires, scales, pages, submissions
- Key types: `ProjectOptions`, `questionnaireOptions`, `Scale`, `Questionnaire`, `Submit`
- **IMPORTANT**: Always check martTypes.ts for exact field names and structure

### IMPORTANT: Data Format Requirements
- Date format: DD.MM.YYYY (e.g., "31.03.2025")
- Response key: `questionnaires` NOT `questionSheets`
- ID fields: `questionnaireId` NOT `sheetId`
- Randomization: `randomizationGroupId` NOT `randomizationGroup`
- iOS Stats: `iOSDataDonationQuestionnaire` NOT `collectIosStats`
- Android Stats: `androidDataDonationQuestionnaire` NOT `collectAndroidStats`
- Stats Submit: `lastDataDonationSubmit` NOT `lastStatsSubmit`
- Omit `name` field from scales
- Default values in `rangeOptions` NOT in root `options`
- Page structure: Include both `id` and `pageId` fields
- Participant data: Optional fields added when `participant_id` provided

### Dynamic End Date Calculation (UPDATED: 2025-11-20)

**Feature**: Questionnaire end dates can be calculated dynamically based on participant's first login.

**Database Fields:**
- `cases.first_login_at` (timestamp) - Tracks when participant first logged in
- `mart_schedules.timing_config` JSON contains:
  - `calculate_end_date_on_login` (boolean) - Enable dynamic calculation
  - `duration_days_after_login` (integer) - Days to add from first login
  - `max_total_submits` (integer) - Total submissions across entire study

**Calculation Formula:**
```
Duration (days) = max_total_submits / maxDailySubmits
End Date = First Login Date + Duration (days)
```

**Login Detection:**
- `app/Http/Controllers/ApiController.php:124-131` - Detects first login for MART projects
- Sets `first_login_at` timestamp on cases table
- Calls `calculateMartDynamicEndDates()` placeholder method (line 446)

**UI:**
- Checkbox in `AddEditQuestionnaireDialog.vue` - "Calculate end date dynamically on first login"
- When checked, start_date_time and end_date_time inputs are disabled
- Researcher enters max_total_submits, system auto-calculates duration

**Behavior:**
- Static dates: Researcher manually sets start/end dates
- Dynamic dates: Start = login time, end = calculated from formula
- Mobile app always receives concrete start/end dates (no calculation on mobile)

**IMPORTANT - API Contract:**
- `max_total_submits` is stored in database and used for backend calculations ONLY
- `max_total_submits` is NOT sent to mobile app via MART API
- Backend calculates concrete start/end dates and sends those to mobile
- Mobile receives only: `startDateAndTime`, `endDateAndTime`, `maxDailySubmits`
- This prevents mobile from needing to implement date calculation logic

### API Testing
```bash
# Structure endpoint
curl -X GET "https://metag-analyze.test/mart-api/projects/1/structure" \
  -H "Authorization: Bearer [case_token]"

# Submit data
curl -X POST "https://metag-analyze.test/mart-api/cases/5/submit" \
  -H "Authorization: Bearer [case_token]" \
  -H "Content-Type: application/json" \
  -d '{"projectId":1,"questionnaireId":1,...}'
```

## Filament Admin Panel (v4.x)

### IMPORTANT: Always Check Filament Docs First
- Filament documentation available at: `~/.claude/skills/docs-analyzer/references/filament-4x/`
- ALWAYS read the docs before implementing Filament features
- Filament 4.x has breaking changes from v3.x

### Common Filament 4.x Patterns
- Use `Schema` instead of `Form` in form methods:
  ```php
  public function form(Schema $schema): Schema
  {
      return $schema->components([...]);
  }
  ```
- Use methods instead of property overrides (PHP 8.3 strict typing):
  ```php
  public static function getNavigationIcon(): ?string
  {
      return 'heroicon-o-cog-6-tooth';
  }

  public static function getNavigationLabel(): string
  {
      return 'Settings';
  }
  ```
- User model must implement `FilamentUser` and `HasName` interfaces
- User model needs `getFilamentName()` and `canAccessPanel(Panel $panel)` methods

### Settings System (UPDATED: 2025-11-19)
- Settings stored in `settings` table with key-value pairs
- Use `Setting::get('key', $default)` to retrieve values
- Use `Setting::set('key', $value, $userId, $type)` to update values with type
- Settings page at `/admin/settings` for admins only

**Available Settings:**
- `mart_enabled` (boolean) - Enable/disable MART project creation for all users
- `max_studies_per_user` (integer) - Maximum number of projects a user can create
- `api_v2_cutoff_date` (date) - Projects created before this date use API v1 (media field), after use API v2 (entity field)

## Database Migrations & Seeders (UPDATED: 2025-11-20)

### Password Hashing in Seeders
- **IMPORTANT**: Always use `bcrypt()` helper for password hashing in seeders
- DO NOT use `Hash::make()` in seeders (can cause facade loading issues)
- Example: `'password' => bcrypt('password123')`
- Files updated: All seeders now use `bcrypt()` consistently

### Trigger Creation & Permissions
- Database triggers require SUPER privilege or `log_bin_trust_function_creators=1`
- Migrations that create triggers now gracefully handle permission errors
- When SUPER privilege unavailable, triggers are skipped with logged warnings
- Application logic enforces constraints when triggers can't be created
- Files: `database/migrations/*_add_data_collection_uniqueness_constraints*.php`

### Destructive Command Safety
- `php artisan migrate:fresh-all` - Resets BOTH main and MART databases
- BLOCKED in production environment (checks `app()->environment()`)
- Requires double confirmation including typing "DELETE ALL DATA"
- Automatically clears Redis cache after migration
- Manually drops MART tables before running migrate:fresh to avoid cross-DB issues

## Recent Bug Fixes & UI Improvements

### Entry Timestamp Handling (UPDATED: 2025-11-24)
- **Fixed**: Mobile app sends Unix timestamps (seconds) for begin/end dates, causing "Invalid date" display
- **Solution**: Added automatic timestamp conversion in `EntryController::store()` and `update()`
- Backend now detects 10-digit numeric timestamps and converts to MySQL datetime format
- Preserves existing datetime strings without modification
- Location: `app/Http/Controllers/EntryController.php:71-78, 140-147`

### Audio Player Redesign (UPDATED: 2025-11-24)
- **Complete redesign** with research-focused aesthetic matching project palette
- **Features**:
  - Waveform visualization with 60 animated bars (pseudo-random based on file ID)
  - Gradient-based color scheme (teal to blue) with warm background
  - Pulsing progress indicator with glowing line animation
  - Professional control panel with tactile button feedback
  - Custom-styled volume slider with gradient thumb
  - Card-based layout with sophisticated shadows
- **Technical**: Uses CSS-only animations, responsive design, hover effects
- Location: `resources/js/components/audioplayer.vue`

### Edit Entry Modal (2025-11-19)
- Fixed issue where some entry values weren't pre-populated when editing
- Properly handles `undefined` and `null` values for all input types
- Multiple choice inputs now correctly converted to arrays
- Location: `resources/js/components/selected-case.vue:792-804`

### Modal Z-Index Issues (2025-11-19)
- Fixed delete project modal appearing below dark background
- Added proper z-index layering (background: z-40, content: z-50)
- Location: `resources/js/components/global/modal.vue`

### Project Creation UI (2025-11-19)
- Removed "Add Entity" button from standard project creation (entities now managed via input fields only)
- Location: `resources/js/components/createproject.vue`

### MART Project Toggle (2025-11-19)
- MART project creation can now be disabled via admin settings
- When disabled, MART button is grayed out and shows "MART projects are currently disabled by administrator"
- Setting synced from Filament admin panel to frontend

## Project-Specific Warnings

- Completed cases reject submissions (422 error)
- Per-questionnaire settings: showProgressBar, showNotifications, notificationText
- Bearer token authentication required for all API calls
- Test database separate from development database
- never run npm run build - i will compile the npm run dev
- entity is a replacement for media, but the database still has media as table and fields
- **NEVER use Sass/SCSS** - project migrated to plain CSS with Tailwind v4