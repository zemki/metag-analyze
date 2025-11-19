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
- `resources/js/components/mart/MartScheduleManager.vue` - Main schedule list/manager
- `resources/js/components/mart/AddEditScheduleDialog.vue` - Create/edit schedules and questions
- `resources/js/components/mart/VersionHistoryModal.vue` - View question version history
- Integrated into `resources/js/components/editproject.vue` for MART projects only

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

## Recent Bug Fixes & UI Improvements (2025-11-19)

### Edit Entry Modal
- Fixed issue where some entry values weren't pre-populated when editing
- Properly handles `undefined` and `null` values for all input types
- Multiple choice inputs now correctly converted to arrays
- Location: `resources/js/components/selected-case.vue:792-804`

### Modal Z-Index Issues
- Fixed delete project modal appearing below dark background
- Added proper z-index layering (background: z-40, content: z-50)
- Location: `resources/js/components/global/modal.vue`

### Project Creation UI
- Removed "Add Entity" button from standard project creation (entities now managed via input fields only)
- Location: `resources/js/components/createproject.vue`

### MART Project Toggle
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