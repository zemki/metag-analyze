# Multiple Questionnaires Per Project - Implementation Plan

## Overview
Enable MART projects to have multiple distinct questionnaires with different question sets, addressing the ESM research requirement for varied data collection instruments within a single study.

## Current Architecture
- **Current**: All questionnaire schedules share one question set stored in `project.inputs`
- **Target**: Each questionnaire schedule can have its own unique question set

## Enhanced Option 1 - Implementation Plan

### Phase 1: Database Schema Updates

#### 1.1 Migration for `mart_questionnaire_schedules` table
```php
// New migration: add_questions_to_schedules.php
Schema::table('mart_questionnaire_schedules', function (Blueprint $table) {
    $table->json('questions')->nullable()->after('questionnaire_id');
    $table->integer('questions_version')->default(1)->after('questions');
    $table->json('questions_history')->nullable()->after('questions_version');
    $table->boolean('is_locked')->default(false)->after('questions_history');
    $table->timestamp('locked_at')->nullable()->after('is_locked');
});
```

#### 1.2 Data Structure
```json
// questions field structure (same as project.inputs)
[
    {
        "name": "How are you feeling?",
        "type": "scale",
        "mandatory": true,
        "answers": [],
        "martMetadata": {
            "originalType": "range",
            "minValue": 1,
            "maxValue": 10
        }
    }
]

// questions_history structure
[
    {
        "version": 1,
        "questions": [...],
        "changed_at": "2025-01-15 10:00:00",
        "changed_by": 1
    }
]
```

### Phase 2: Backend Implementation

#### 2.1 Model Updates

**MartQuestionnaireSchedule.php**
```php
protected $fillable = [
    // ... existing fields
    'questions',
    'questions_version',
    'questions_history',
    'is_locked',
    'locked_at',
];

protected $casts = [
    // ... existing casts
    'questions' => 'array',
    'questions_history' => 'array',
    'is_locked' => 'boolean',
    'locked_at' => 'datetime',
];

public function canEditQuestions(): bool
{
    return !$this->is_locked;
}

public function lockQuestions(): void
{
    $this->is_locked = true;
    $this->locked_at = now();
    $this->save();
}

public function updateQuestions(array $newQuestions, $userId): bool
{
    if ($this->is_locked) {
        throw new \Exception('Cannot edit questions - data collection has started');
    }

    // Save current version to history
    if ($this->questions) {
        $history = $this->questions_history ?? [];
        $history[] = [
            'version' => $this->questions_version,
            'questions' => $this->questions,
            'changed_at' => now()->toIso8601String(),
            'changed_by' => $userId
        ];
        $this->questions_history = $history;
        $this->questions_version++;
    }

    $this->questions = $newQuestions;
    return $this->save();
}
```

#### 2.2 API Controller Updates

**MartApiController.php**
```php
public function submitEntry(Request $request, Cases $case)
{
    // Get schedule and lock questions on first submission
    $schedule = MartQuestionnaireSchedule::where('project_id', $request->projectId)
        ->where('questionnaire_id', $request->questionnaireId)
        ->first();

    if ($schedule && !$schedule->is_locked) {
        $schedule->lockQuestions();
    }

    // Get questions from schedule or fallback to project
    $questions = $schedule->questions ?? json_decode($case->project->inputs, true);

    // Store version info in metadata
    $martMetadata = [
        'questionnaire_id' => $request->questionnaireId,
        'questions_version' => $schedule->questions_version ?? 1,
        // ... other metadata
    ];

    // ... rest of submission logic
}

public function updateScheduleQuestions(Request $request, $scheduleId)
{
    $schedule = MartQuestionnaireSchedule::findOrFail($scheduleId);

    // Check permissions
    $this->authorize('update', $schedule->project);

    // Validate questions structure
    $request->validate([
        'questions' => 'required|array',
        'questions.*.name' => 'required|string',
        'questions.*.type' => 'required|in:scale,text,one choice,multiple choice',
        'questions.*.mandatory' => 'required|boolean',
    ]);

    try {
        $schedule->updateQuestions($request->questions, auth()->id());
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}
```

#### 2.3 Resource Updates

**MartStructureResource.php**
```php
public function toArray($request)
{
    // ... existing code

    // Build questionnaires from schedules
    $questionnaires = [];
    foreach ($schedules as $schedule) {
        // Use schedule questions if available, otherwise project inputs
        $questionData = $schedule->questions ?? json_decode($project->inputs, true);

        $questionnaires[] = [
            'questionnaireId' => $schedule->questionnaire_id,
            'name' => $schedule->name,
            'questions' => new QuestionSheetResource($project, $questionData, $martConfig),
            'isLocked' => $schedule->is_locked,
            'version' => $schedule->questions_version ?? 1
        ];
    }

    // ... rest of response
}
```

### Phase 3: Frontend Implementation

#### 3.1 Components Needed

1. **QuestionnaireScheduleManager.vue**
   - List all schedules with their question counts
   - Edit questions button (disabled if locked)
   - Lock status indicator
   - Version display

2. **QuestionEditor.vue**
   - Reuse existing question builder logic
   - Add warning when editing existing schedule
   - Show lock status prominently

3. **ProjectCreationFlow.vue**
   ```
   Step 1: Project Info
   Step 2: Add Questionnaire Schedules
     - For each schedule:
       a. Schedule details (name, type, timing)
       b. Question builder
   Step 3: Review and Create
   ```

#### 3.2 API Endpoints

```javascript
// Get schedule with questions
GET /api/schedules/{id}

// Update schedule questions (only if not locked)
PUT /api/schedules/{id}/questions

// Get question edit status
GET /api/schedules/{id}/can-edit

// Copy questions from another schedule
POST /api/schedules/{id}/copy-questions
```

### Phase 4: Migration Strategy

#### 4.1 Backward Compatibility
```php
// In MartStructureResource
private function getQuestionsForSchedule($schedule, $project)
{
    // Priority order:
    // 1. Schedule-specific questions
    if ($schedule->questions) {
        return $schedule->questions;
    }

    // 2. Legacy project.inputs (backward compatibility)
    if ($project->inputs) {
        return json_decode($project->inputs, true);
    }

    // 3. Empty array
    return [];
}
```

#### 4.2 Migration Script for Existing Projects
```php
// Artisan command: php artisan mart:migrate-questions
foreach (MartQuestionnaireSchedule::all() as $schedule) {
    if (!$schedule->questions && $schedule->project->inputs) {
        $schedule->questions = json_decode($schedule->project->inputs, true);
        $schedule->save();
    }
}
```

### Phase 5: Testing Plan

#### 5.1 Test Scenarios
1. Create project with 3 different questionnaires
2. Edit questions before any submissions (should work)
3. Submit entry → verify schedule locks
4. Try editing after submission (should fail)
5. Verify version tracking in history
6. Test backward compatibility with old projects

#### 5.2 Test Data Structure
```php
// Test project with multiple questionnaires
$schedules = [
    [
        'name' => 'Morning Check-in',
        'type' => 'repeating',
        'questions' => [
            ['name' => 'How did you sleep?', 'type' => 'scale'],
            ['name' => 'Morning mood', 'type' => 'scale']
        ]
    ],
    [
        'name' => 'Random Prompt',
        'type' => 'repeating',
        'questions' => [
            ['name' => 'Current activity?', 'type' => 'multiple choice'],
            ['name' => 'Who are you with?', 'type' => 'multiple choice']
        ]
    ],
    [
        'name' => 'Evening Reflection',
        'type' => 'repeating',
        'questions' => [
            ['name' => 'Day summary', 'type' => 'text'],
            ['name' => 'Stress level today', 'type' => 'scale']
        ]
    ]
];
```

### Phase 6: Documentation

#### 6.1 Update CLAUDE.md
- Document new multiple questionnaire capability
- Add warning about question locking
- Provide migration instructions

#### 6.2 User Documentation
- How to create multiple questionnaires
- Understanding question locking
- Best practices for ESM studies

### Timeline

| Phase | Duration | Tasks |
|-------|----------|-------|
| Phase 1 | 0.5 days | Database migration |
| Phase 2 | 2 days | Backend implementation |
| Phase 3 | 3 days | Frontend components |
| Phase 4 | 0.5 days | Migration & compatibility |
| Phase 5 | 1 day | Testing |
| Phase 6 | 0.5 days | Documentation |
| **Total** | **7.5 days** | |

### Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Breaking existing projects | High | Backward compatibility layer |
| Users edit after data collection | Medium | Clear lock indicators & warnings |
| Complex frontend UI | Medium | Reuse existing question builder |
| Data integrity issues | High | Version tracking & history |

### Success Criteria
- ✅ Each schedule can have unique questions
- ✅ Questions editable until first submission
- ✅ Clear version tracking
- ✅ Backward compatibility maintained
- ✅ Frontend UI intuitive for researchers
- ✅ All tests passing