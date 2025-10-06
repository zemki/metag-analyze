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

## Project-Specific Warnings

- Completed cases reject submissions (422 error)
- Per-questionnaire settings: showProgressBar, showNotifications, notificationText
- Bearer token authentication required for all API calls
- Test database separate from development database
