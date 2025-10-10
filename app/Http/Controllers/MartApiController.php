<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Entry;
use App\Http\Resources\Mart\MartStructureResource;
use App\Mart\MartAnswer;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\MartQuestionnaireSchedule;
use App\Project;
use App\Stat;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MartApiController extends Controller
{
    /**
     * Get project structure for mobile app
     */
    public function getProjectStructure(Request $request, Project $project)
    {
        // Get MART project from MART database
        $martProject = $project->martProject();

        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Get questionnaire schedules from MART database with questions
        $schedules = MartSchedule::forProject($martProject->id)
            ->with('questions')
            ->get();

        // Get participant_id from query parameter if provided
        $participantId = $request->query('participant_id');

        // Pass schedules and participant_id to the resource
        $resource = new MartStructureResource($project, $schedules);
        $resource->setParticipantId($participantId);

        return $resource;
    }

    /**
     * Handle submission from mobile app
     */
    public function submitEntry(Request $request, Cases $case)
    {
        // Validate request according to martTypes.ts Submit type
        // Note: answers values can be numbers (scale), arrays (multiple choice), or strings (text)
        $request->validate([
            'projectId' => 'required|numeric',
            'questionnaireId' => 'required|numeric',
            'userId' => 'required|string',
            'participantId' => 'required|string',
            'sheetId' => 'required|numeric',
            'questionnaireStarted' => 'required|numeric',
            'questionnaireDuration' => 'required|numeric',
            'answers' => 'required|array',
            'answers.*' => 'present', // Explicitly allow any type for answer values
            'timestamp' => 'required|numeric',
            'timestampInherited' => 'nullable|numeric',
            'timezone' => 'required|string',
        ]);

        // Verify case belongs to the project
        if ($case->project_id != $request->projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Case does not belong to the specified project',
            ], 400);
        }

        // PHASE 1: Case Status Validation
        $caseStatus = $case->getStatus();
        if ($caseStatus === \App\Enums\CaseStatus::COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot submit to completed case. Case has finished and no longer accepts submissions.',
                'case_status' => $caseStatus->value,
            ], 422);
        }

        // Only allow submissions to pending or active cases
        if (! in_array($caseStatus, [\App\Enums\CaseStatus::PENDING, \App\Enums\CaseStatus::ACTIVE])) {
            return response()->json([
                'success' => false,
                'message' => 'Case is not accepting submissions',
                'case_status' => $caseStatus->value,
            ], 422);
        }

        // PHASE 2: Project Data Conformity Validation
        $project = Project::find($request->projectId);
        if (! $project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        // Validate answers against project structure (from MART DB)
        $validationResult = $this->validateAnswersAgainstProject($request->answers, $project, $request->questionnaireId);
        if (! $validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Answer validation failed',
                'errors' => $validationResult['errors'],
            ], 422);
        }

        // Handle entity/media selection (skip for MART projects)
        $entityId = null;
        $project = Project::find($request->projectId);

        if ($project && ! $project->isMartProject()) {
            if ($request->has('mediaId') && $request->mediaId) {
                // Use provided media ID if valid
                $mediaExists = DB::table('media')->where('id', $request->mediaId)->exists();
                if ($mediaExists) {
                    $entityId = $request->mediaId;
                }
            } else {
                // Try to find a default entity for the project
                $defaultEntity = DB::table('media')
                    ->join('project_media', 'media.id', '=', 'project_media.media_id')
                    ->where('project_media.project_id', $request->projectId)
                    ->first();

                if ($defaultEntity) {
                    $entityId = $defaultEntity->id;
                }
            }
        }

        // Get MART project and schedule from MART DB
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        $schedule = MartSchedule::forProject($martProject->id)
            ->where('questionnaire_id', $request->questionnaireId)
            ->with('questions')
            ->first();

        if (! $schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Questionnaire schedule not found',
            ], 404);
        }

        // Cross-DB transaction handling
        // Note: Laravel doesn't support true cross-DB transactions, so we handle this manually
        DB::connection('mysql')->beginTransaction();
        DB::connection('mart')->beginTransaction();

        try {
            // Step 1: Create entry in main database
            $entryData = [
                'begin' => date('Y-m-d H:i:s', $request->questionnaireStarted / 1000),
                'end' => date('Y-m-d H:i:s', ($request->questionnaireStarted + $request->questionnaireDuration) / 1000),
                'case_id' => $case->id,
                'media_id' => $entityId,
                'inputs' => json_encode([]), // No longer store answers here
            ];

            $entry = Entry::create($entryData);

            // Step 2: Create MART entry in MART database
            $martEntryData = [
                'main_entry_id' => $entry->id,
                'schedule_id' => $schedule->id,
                'questionnaire_id' => $request->questionnaireId,
                'participant_id' => $request->participantId,
                'user_id' => $request->userId,
                'started_at' => date('Y-m-d H:i:s', $request->questionnaireStarted / 1000),
                'completed_at' => date('Y-m-d H:i:s', ($request->questionnaireStarted + $request->questionnaireDuration) / 1000),
                'duration_ms' => $request->questionnaireDuration,
                'timezone' => $request->timezone,
                'timestamp' => $request->timestamp,
            ];

            $martEntry = MartEntry::create($martEntryData);

            // Step 3: Create MART answers for each question
            $questions = $schedule->questions->keyBy('position');

            foreach ($request->answers as $questionPosition => $answerValue) {
                // Convert position to int for comparison
                $positionInt = (int) $questionPosition;
                $question = $questions->get($positionInt);

                if ($question) {
                    MartAnswer::create([
                        'entry_id' => $martEntry->id,
                        'question_uuid' => $question->uuid,
                        'question_version' => $question->version,
                        'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                    ]);
                }
            }

            // Commit both transactions
            DB::connection('mysql')->commit();
            DB::connection('mart')->commit();

            return response()->json([
                'success' => true,
                'entry_id' => $entry->id,
                'message' => 'Entry created successfully',
            ]);
        } catch (\Exception $e) {
            // Rollback both transactions on error
            DB::connection('mysql')->rollBack();
            DB::connection('mart')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create entry: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transform MART answers format to MetaG inputs format
     */
    private function transformMartAnswersToMetagInputs($martAnswers)
    {
        $metagInputs = [];

        foreach ($martAnswers as $questionId => $answer) {
            // Handle different answer types
            $metagInputs["question_$questionId"] = $answer;
        }

        return $metagInputs;
    }

    /**
     * Store device information from mobile app
     * Now stores in MART database
     */
    public function storeDeviceInfo(Request $request)
    {
        // Validate request
        $request->validate([
            'projectId' => 'required|numeric|exists:projects,id',
            'userId' => 'required|string|email',
            'participantId' => 'required|string',
            'os' => 'required|in:android,ios',
            'osVersion' => 'required|string',
            'model' => 'required|string',
            'manufacturer' => 'required|string',
            'timestamp' => 'required|numeric',
            'timezone' => 'required|string',
        ]);

        // Verify user access
        $user = $this->verifyUserAccess($request);
        if ($user instanceof JsonResponse) {
            return $user; // Return the error response
        }

        // Update or create device info in MART database
        $deviceInfo = MartDeviceInfo::updateOrCreate(
            [
                'participant_id' => $request->participantId,
                'user_id' => $request->userId,
            ],
            [
                'os' => $request->os,
                'os_version' => $request->osVersion,
                'model' => $request->model,
                'manufacturer' => $request->manufacturer,
                'last_updated' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device information stored successfully',
            'device_info_id' => $deviceInfo->id,
        ]);
    }

    /**
     * Submit usage statistics from mobile app
     * Now stores in MART database
     */
    public function submitStats(Request $request)
    {
        // Validate request
        $request->validate([
            'projectId' => 'required|numeric|exists:projects,id',
            'userId' => 'required|string|email',
            'participantId' => 'required|string',
            'timestamp' => 'required|numeric',
            'timezone' => 'required|string',
            'androidUsageStats' => 'nullable|array',
            'androidEventStats' => 'nullable|array',
            'iOSStats' => 'nullable|array',
        ]);

        // Verify user access
        $user = $this->verifyUserAccess($request);
        if ($user instanceof JsonResponse) {
            return $user; // Return the error response
        }

        // Get MART project
        $project = Project::find($request->projectId);
        $martProject = $project->martProject();

        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Get device ID from MART database
        $deviceInfo = MartDeviceInfo::forParticipant($request->participantId)
            ->forUser($request->userId)
            ->first();

        // Prepare stats data for MART database
        $statsData = [
            'mart_project_id' => $martProject->id,
            'user_id' => $request->userId,
            'participant_id' => $request->participantId,
            'timestamp' => $request->timestamp,
            'timezone' => $request->timezone,
            'device_id' => $deviceInfo ? $deviceInfo->id : null,
        ];

        // Add platform-specific stats
        if ($request->has('androidUsageStats')) {
            $statsData['android_usage_stats'] = $request->androidUsageStats;
        }

        if ($request->has('androidEventStats')) {
            $statsData['android_event_stats'] = $request->androidEventStats;
        }

        if ($request->has('iOSStats')) {
            $statsData['ios_stats'] = $request->iOSStats;
            // Extract specific iOS fields if present
            if (isset($request->iOSStats['screenTime'])) {
                $statsData['ios_screen_time'] = $request->iOSStats['screenTime'];
            }
            if (isset($request->iOSStats['pickupCount'])) {
                $statsData['ios_activations'] = $request->iOSStats['pickupCount'];
            }
        }

        // Create stats entry in MART database
        $stat = MartStat::create($statsData);

        return response()->json([
            'success' => true,
            'stat_id' => $stat->id,
            'message' => 'Stats submitted successfully',
        ]);
    }

    /**
     * Verify user exists and has access to the project through a case
     */
    private function verifyUserAccess(Request $request)
    {
        $user = User::where('email', $request->userId)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Verify user has access to the project through a case
        $hasAccess = Cases::where('project_id', $request->projectId)
            ->where('name', $request->participantId)
            ->where('user_id', $user->id)
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have access to this project',
            ], 403);
        }

        return $user;
    }

    /**
     * Validate answers against project structure and constraints
     * Now validates against MART DB schedules, not project.inputs
     */
    private function validateAnswersAgainstProject(array $answers, Project $project, int $questionnaireId): array
    {
        $errors = [];

        // Get MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            return ['valid' => false, 'errors' => ['MART project not found']];
        }

        // Find the schedule for this questionnaire
        $schedule = MartSchedule::forProject($martProject->id)
            ->where('questionnaire_id', $questionnaireId)
            ->with('questions')
            ->first();

        if (! $schedule) {
            return ['valid' => false, 'errors' => ["Questionnaire $questionnaireId not found in MART database"]];
        }

        // Get questions for this schedule
        $questions = $schedule->questions;

        if ($questions->isEmpty()) {
            return ['valid' => false, 'errors' => ['Schedule has no questions']];
        }

        // Build question map (position => question) for validation
        $questionMap = [];
        foreach ($questions as $question) {
            $questionMap[$question->position] = $question;
        }

        // Validate each answer
        foreach ($answers as $questionPosition => $answer) {
            // Convert position to int for comparison
            $positionInt = (int) $questionPosition;

            if (! isset($questionMap[$positionInt])) {
                $errors[] = "Question at position $questionPosition does not exist in schedule";
                continue;
            }

            $question = $questionMap[$positionInt];
            $validationResult = $this->validateSingleAnswer($answer, $question, $positionInt);

            if (! $validationResult['valid']) {
                $errors = array_merge($errors, $validationResult['errors']);
            }
        }

        // Check for missing required questions
        foreach ($questions as $question) {
            // Check both string and int keys for compatibility
            if ($question->is_mandatory && ! isset($answers[$question->position]) && ! isset($answers[(string) $question->position])) {
                $errors[] = "Required question at position {$question->position} ('{$question->text}') is missing";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate a single answer against its question constraints
     * Updated to work with MartQuestion model from MART DB
     */
    private function validateSingleAnswer($answer, $question, int $questionPosition): array
    {
        $errors = [];
        $type = $question->type;
        $config = $question->config ?? [];

        switch ($type) {
            case 'one choice':
                // Must be a single integer within valid range
                if (! is_int($answer)) {
                    $errors[] = "Question $questionPosition: Answer must be a single integer for one choice questions";
                } else {
                    $validOptions = isset($config['options']) ? array_keys($config['options']) : [];
                    if (! in_array($answer, $validOptions)) {
                        $errors[] = "Question $questionPosition: Answer $answer is not a valid option. Valid options: ".implode(', ', $validOptions);
                    }
                }
                break;

            case 'multiple choice':
                // Must be an array of integers
                if (! is_array($answer)) {
                    $errors[] = "Question $questionPosition: Answer must be an array for multiple choice questions";
                } else {
                    $validOptions = isset($config['options']) ? array_keys($config['options']) : [];
                    foreach ($answer as $value) {
                        if (! is_int($value) || ! in_array($value, $validOptions)) {
                            $errors[] = "Question $questionPosition: Answer value $value is not valid. Valid options: ".implode(', ', $validOptions);
                        }
                    }
                }
                break;

            case 'scale':
                // Must be a number within min/max range
                if (! is_numeric($answer)) {
                    $errors[] = "Question $questionPosition: Answer must be a number for scale questions";
                } else {
                    $minValue = $config['minValue'] ?? 1;
                    $maxValue = $config['maxValue'] ?? 10;

                    if ($answer < $minValue || $answer > $maxValue) {
                        $errors[] = "Question $questionPosition: Answer $answer is out of range ($minValue-$maxValue)";
                    }

                    // Check if answer respects step increments if defined
                    if (isset($config['steps']) && $config['steps'] > 1) {
                        $steps = $config['steps'];
                        $remainder = ($answer - $minValue) % $steps;
                        if ($remainder !== 0) {
                            $errors[] = "Question $questionPosition: Answer $answer does not match step increments of $steps";
                        }
                    }
                }
                break;

            case 'text':
                // Must be a string
                if (! is_string($answer)) {
                    $errors[] = "Question $questionPosition: Answer must be a string for text questions";
                } elseif (empty(trim($answer)) && $question->is_mandatory) {
                    $errors[] = "Question $questionPosition: Text answer cannot be empty for mandatory question";
                }
                break;

            default:
                // Unknown type - allow anything
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
