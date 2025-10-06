# Multiple Questionnaires Per Project - Implementation Summary

## Overview
Successfully implemented support for multiple unique questionnaires per MART project, with question versioning and edit history tracking. Each questionnaire schedule can now have its own distinct set of questions, enabling proper ESM (Experience Sampling Method) research workflows.

## Implementation Date
September 30, 2025

## Key Requirements Met
✅ Multiple questionnaires per project (unlimited single and repeating)
✅ Each questionnaire has its own unique question set
✅ Questions are always editable (no locking mechanism per Roland's requirements)
✅ Version tracking for data analysis
✅ Edit history preserved
✅ Backward compatibility maintained with non-MART projects
✅ All tests passing (6 tests, 63 assertions)

## Database Changes

### Migration: `2025_09_30_094549_add_questions_to_mart_schedules.php`

Added three fields to `mart_questionnaire_schedules` table:

```php
$table->json('questions')->nullable();           // Store question set per schedule
$table->integer('questions_version')->default(1); // Track version number
$table->json('questions_history')->nullable();    // Store edit history
```

**Run migration:**
```bash
php artisan migrate
```

### Data Structure

**Questions Field:**
```json
[
  {
    "name": "How are you feeling?",
    "type": "scale",
    "mandatory": true,
    "answers": [],
    "martMetadata": {
      "originalType": "range",
      "minValue": 1,
      "maxValue": 10,
      "steps": 1
    }
  }
]
```

**Questions History Field:**
```json
[
  {
    "version": 1,
    "questions": [...],
    "changed_at": "2025-09-30T12:00:00Z"
  }
]
```

## Backend Changes

### 1. Model Updates

#### `app/MartQuestionnaireSchedule.php`

**Added Fields:**
- `questions` (array cast)
- `questions_version` (integer cast)
- `questions_history` (array cast)

**New Method:**
```php
public function updateQuestions(array $newQuestions): bool
```
- Saves current version to history before updating
- Increments version number automatically
- Returns boolean success status

**Usage Example:**
```php
$schedule = MartQuestionnaireSchedule::find(1);
$schedule->updateQuestions([
    ['name' => 'New question', 'type' => 'text', 'mandatory' => false]
]);
// Version increments from 1 to 2
// Previous questions saved in history
```

### 2. Resource Updates

#### `app/Http/Resources/Mart/MartStructureResource.php`

**Changed:** Now builds multiple questionnaires from schedules instead of one from project.inputs

**Before:**
```php
$questionnaires = [$questionSheet]; // One questionnaire for all schedules
```

**After:**
```php
foreach ($schedules as $schedule) {
    $scheduleQuestions = $schedule->questions ?? [];
    $questionnaires[] = new QuestionSheetResource(..., $schedule->questionnaire_id);
}
```

**Result:** Returns array of questionnaires, one per schedule with unique questions

#### `app/Http/Resources/Mart/QuestionSheetResource.php`

**Added:** Support for dynamic `questionnaire_id`

**Before:**
```php
'questionnaireId' => 1  // Hardcoded
```

**After:**
```php
'questionnaireId' => $this->questionnaireId  // From schedule
```

### 3. Controller Updates

#### `app/Http/Controllers/MartApiController.php`

**Added:** Version tracking in submission metadata

```php
// Get schedule to retrieve current version
$schedule = MartQuestionnaireSchedule::where('project_id', $request->projectId)
    ->where('questionnaire_id', $request->questionnaireId)
    ->first();

$martMetadata = [
    // ... existing fields
    'questions_version' => $schedule ? $schedule->questions_version : 1,
];
```

**Purpose:** Links each participant response to the specific question version they answered

#### `app/Http/Controllers/MartScheduleController.php` (NEW)

**Created three endpoints:**

1. **Create Schedule**
```php
POST /projects/{project}/schedules
```
Creates new questionnaire schedule with questions

2. **Update Questions**
```php
PUT /schedules/{schedule}/questions
```
Updates questions and increments version

3. **View History**
```php
GET /schedules/{schedule}/history
```
Returns version history and current version

### 4. Routes

**Added to `routes/web.php`:**
```php
Route::post('/projects/{project}/schedules', 'MartScheduleController@store');
Route::put('/schedules/{schedule}/questions', 'MartScheduleController@updateQuestions');
Route::get('/schedules/{schedule}/history', 'MartScheduleController@history');
```

**Authentication:** All routes require authentication via existing middleware

## Testing Updates

### `tests/Feature/MartApiTest.php`

**Updated:**
1. Added `questions` field to schedule creation in setUp()
2. Added assertions for multiple questionnaires
3. Added assertions for questions_version in metadata

**Test Results:**
```
✓ it returns project structure with questionnaire schedules
✓ it submits entry with questionnaire id
✓ it returns participant data when participant id provided
✓ it stores device info
✓ it requires questionnaire id for submission
✓ it returns correct schedule format for mobile

Tests: 6 passed (63 assertions)
```

**New Assertions:**
- Verifies 2 questionnaires returned (one per schedule)
- Verifies each questionnaire has correct questionnaireId
- Verifies each questionnaire has correct number of questions
- Verifies questions_version stored in entry metadata

## API Response Changes

### Structure Endpoint Response

**Before:**
```json
{
  "questionnaires": [
    {
      "questionnaireId": 1,
      "name": "Questionnaire",
      "items": [/* all questions for all schedules */]
    }
  ]
}
```

**After:**
```json
{
  "questionnaires": [
    {
      "questionnaireId": 1,
      "name": "Daily Check-in",
      "items": [/* questions specific to schedule 1 */]
    },
    {
      "questionnaireId": 2,
      "name": "Weekly Reflection",
      "items": [/* questions specific to schedule 2 */]
    }
  ]
}
```

### Submission Metadata

**New Field in Entry:**
```json
{
  "_mart_metadata": {
    "questionnaire_id": 1,
    "participant_id": "Participant_001",
    "questions_version": 2,  // NEW: tracks which version was answered
    "timestamp": 1727690400000
  }
}
```

## Usage Example

### Creating a MART Project with Multiple Questionnaires

```php
// 1. Create project
$project = Project::create([
    'name' => 'Morning & Evening Study',
    'inputs' => json_encode([
        ['type' => 'mart', 'questionnaireName' => 'ESM Study', 'projectOptions' => [...]]
    ])
]);

// 2. Create morning questionnaire schedule
MartQuestionnaireSchedule::create([
    'project_id' => $project->id,
    'questionnaire_id' => 1,
    'name' => 'Morning Check-in',
    'type' => 'single',
    'start_date_time' => ['date' => '2025-10-01', 'time' => '08:00'],
    'questions' => [
        [
            'name' => 'How did you sleep?',
            'type' => 'scale',
            'mandatory' => true,
            'martMetadata' => ['originalType' => 'range', 'minValue' => 1, 'maxValue' => 10]
        ]
    ]
]);

// 3. Create evening questionnaire schedule
MartQuestionnaireSchedule::create([
    'project_id' => $project->id,
    'questionnaire_id' => 2,
    'name' => 'Evening Reflection',
    'type' => 'single',
    'start_date_time' => ['date' => '2025-10-01', 'time' => '20:00'],
    'questions' => [
        [
            'name' => 'How was your day?',
            'type' => 'text',
            'mandatory' => false,
            'martMetadata' => ['originalType' => 'textarea']
        ]
    ]
]);
```

### Editing Questions Mid-Study

```php
$schedule = MartQuestionnaireSchedule::find(1);

// Update questions (version increments automatically)
$schedule->updateQuestions([
    [
        'name' => 'How did you sleep? (Updated)',
        'type' => 'scale',
        'mandatory' => true,
        'martMetadata' => ['originalType' => 'range', 'minValue' => 0, 'maxValue' => 10]
    ]
]);

// Check version
echo $schedule->questions_version; // 2

// View history
$history = $schedule->questions_history;
// Returns array with version 1 questions and timestamp
```

## Backward Compatibility

### Non-MART Projects
- ✅ Zero changes required
- ✅ Continue using `project.inputs` as before
- ✅ Never interact with schedules table

### MART Projects in Development
- ✅ No "old MART projects" exist yet
- ✅ All new MART projects use schedule questions
- ✅ No fallback logic needed

## Data Analysis Considerations

### Analyzing Responses with Version Tracking

When analyzing participant data, filter by version if questions changed:

```php
// Get all entries for a questionnaire
$entries = Entry::where('case_id', $caseId)->get();

foreach ($entries as $entry) {
    $metadata = json_decode($entry->inputs, true)['_mart_metadata'];
    $version = $metadata['questions_version'];

    // Group by version for analysis
    if ($version == 1) {
        // Analyze using version 1 questions
    } else {
        // Analyze using version 2 questions
    }
}
```

### Accessing Historical Questions

```php
$schedule = MartQuestionnaireSchedule::find(1);

// Current questions
$currentQuestions = $schedule->questions;
$currentVersion = $schedule->questions_version;

// Historical versions
foreach ($schedule->questions_history as $historyEntry) {
    $version = $historyEntry['version'];
    $questions = $historyEntry['questions'];
    $changedAt = $historyEntry['changed_at'];

    echo "Version $version changed at $changedAt\n";
}
```

## Frontend Integration (To Be Implemented)

### Required Components

1. **Schedule Manager**
   - List all questionnaire schedules
   - Show version numbers
   - Edit/Add schedule buttons

2. **Question Editor**
   - Reuse existing question builder
   - Display current version
   - Warning about mid-study changes

3. **Version History View**
   - Modal showing all versions
   - Diff view (optional)
   - Timestamps and version numbers

### Recommended User Flow

```
1. Create Project
   └─> Add Questionnaire Schedules
       ├─> Schedule 1: Set timing + Build questions
       ├─> Schedule 2: Set timing + Build questions
       └─> Schedule 3: Set timing + Build questions

2. Edit Existing Project
   └─> Schedule List
       ├─> Edit Questions (creates new version)
       ├─> View History
       └─> Add New Schedule
```

## Technical Notes

### Version Increment Logic
- First time setting questions: version = 1
- Each subsequent update: version++
- Previous version saved to history with timestamp
- No maximum version limit

### Question Validation
```php
'questions' => 'required|array',
'questions.*.name' => 'required|string',
'questions.*.type' => 'required|in:scale,text,one choice,multiple choice',
'questions.*.mandatory' => 'required|boolean',
```

### Authorization
All schedule endpoints use Laravel's policy authorization:
```php
$this->authorize('update', $schedule->project);
```

## Known Limitations

1. **No automatic version migration** - If question types change significantly, data analysis scripts need manual updates
2. **No question ID tracking** - Questions identified by array index, not persistent IDs
3. **No diff view** - History shows full questions, not changes
4. **Frontend not implemented** - Backend complete, UI pending

## Future Enhancements (Optional)

1. **Question IDs**: Assign persistent IDs to questions for better tracking
2. **Diff View**: Show what changed between versions
3. **Version Comments**: Let researchers add notes about why changes were made
4. **Rollback**: Allow reverting to previous question versions
5. **Export**: Include version history in project exports

## Files Modified

### Backend Core
- `database/migrations/2025_09_30_094549_add_questions_to_mart_schedules.php` (NEW)
- `app/MartQuestionnaireSchedule.php` (UPDATED)
- `app/Http/Controllers/MartScheduleController.php` (NEW)
- `app/Http/Controllers/MartApiController.php` (UPDATED)
- `app/Http/Resources/Mart/MartStructureResource.php` (UPDATED)
- `app/Http/Resources/Mart/QuestionSheetResource.php` (UPDATED)

### Routing
- `routes/web.php` (UPDATED)

### Seeds
- `database/seeds/MartProjectSeeder.php` (UPDATED - schedules now include questions)

### Testing
- `tests/Feature/MartApiTest.php` (UPDATED - added version tracking assertions)

### Documentation
- `CLAUDE.md` (UPDATED - added multiple questionnaire notes)
- `MULTIPLE_QUESTIONNAIRES_PLAN.md` (original plan)
- `MULTIPLE_QUESTIONNAIRES_IMPLEMENTATION.md` (this file)

## Summary

Successfully implemented multiple questionnaires per MART project with complete version tracking. The system now supports proper ESM research workflows where different questionnaires can have completely different questions at different times (e.g., "Morning Mood" vs "Evening Reflection"). All changes maintain backward compatibility with existing non-MART projects, and all tests pass successfully.

Next steps are frontend implementation to provide UI for managing schedules and editing questions.