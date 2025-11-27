<?php

namespace App\Http\Controllers;

use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\MartQuestionnaireSchedule;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MartQuestionnaireController extends Controller
{
    /**
     * Get all questionnaires for a project.
     * Now queries MART database
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        // Get MART project
        $martProject = $project->martProject();

        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Get questionnaires with questions from MART database
        $questionnaires = MartSchedule::forProject($martProject->id)
            ->with('questions')
            ->orderBy('questionnaire_id')
            ->get();

        return response()->json([
            'success' => true,
            'questionnaires' => $questionnaires,
        ]);
    }

    /**
     * Store a new questionnaire for a project.
     * Now creates in MART database with separate question records
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'questionnaire_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'introductory_text' => 'nullable|string',
            'type' => 'required|in:single,repeating',
            'start_date_time' => 'nullable|array',
            'end_date_time' => 'nullable|array',
            'start_on_first_login' => 'nullable|boolean',
            'start_hours_after_login' => 'nullable|integer|min:0|max:168',
            'use_dynamic_end_date' => 'nullable|boolean',
            'show_progress_bar' => 'boolean',
            'show_notifications' => 'boolean',
            'notification_text' => 'nullable|string',
            'is_ios_data_donation' => 'nullable|boolean',
            'is_android_data_donation' => 'nullable|boolean',
            'daily_interval_duration' => 'nullable|integer',
            'min_break_between' => 'nullable|integer',
            'max_daily_submits' => 'nullable|integer',
            'max_total_submits' => 'nullable|integer',
            'daily_start_time' => 'nullable|string',
            'daily_end_time' => 'nullable|string',
            'quest_available_at' => 'nullable|in:startOfInterval,randomTimeWithinInterval',
            'show_after_repeating' => 'nullable|array',
            'questions' => 'required|array',
            'questions.*.text' => 'required|string',
            'questions.*.image_url' => 'nullable|url|max:2048',
            'questions.*.video_url' => 'nullable|url|max:2048',
            'questions.*.type' => 'required|in:number,range,text,textarea,one choice,multiple choice,display',
            'questions.*.mandatory' => 'required|boolean',
            'questions.*.config' => 'nullable|array',
        ]);

        // Enforce mutual exclusivity: only one data donation type allowed
        $isIos = $validated['is_ios_data_donation'] ?? false;
        $isAndroid = $validated['is_android_data_donation'] ?? false;
        if ($isIos && $isAndroid) {
            return response()->json([
                'success' => false,
                'message' => 'A questionnaire can only be designated as iOS OR Android data donation, not both.',
            ], 422);
        }

        // Reject start_on_first_login for repeating questionnaires
        if ($validated['type'] === 'repeating' && ($validated['start_on_first_login'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Start on first login is not supported for repeating questionnaires.',
            ], 422);
        }

        // Get or create MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            $martProject = MartProject::create(['main_project_id' => $project->id]);
        }

        // Calculate end_date_time if max_total_submits is provided for repeating questionnaires
        // Skip if start_on_first_login is true (dates will be calculated at participant's first login)
        $startOnFirstLogin = $validated['start_on_first_login'] ?? false;
        $useDynamicEndDate = $validated['use_dynamic_end_date'] ?? false;

        if ($validated['type'] === 'repeating' &&
            ! $startOnFirstLogin &&
            ! $useDynamicEndDate &&
            isset($validated['max_total_submits']) &&
            $validated['max_total_submits'] > 0 &&
            isset($validated['start_date_time']) &&
            isset($validated['daily_start_time']) &&
            isset($validated['daily_end_time']) &&
            isset($validated['daily_interval_duration']) &&
            isset($validated['max_daily_submits'])
        ) {
            $validated['end_date_time'] = \App\MartQuestionnaireSchedule::calculateEndDateTime(
                $validated['start_date_time'],
                $validated['daily_start_time'],
                $validated['daily_end_time'],
                $validated['daily_interval_duration'],
                $validated['max_daily_submits'],
                $validated['max_total_submits']
            );
        }

        DB::connection('mart')->beginTransaction();

        try {
            // Build timing and notification configs
            $timingConfig = [
                'start_date_time' => $validated['start_date_time'] ?? null,
                'end_date_time' => $validated['end_date_time'] ?? null,
                'start_on_first_login' => $validated['start_on_first_login'] ?? false,
                'start_hours_after_login' => $validated['start_hours_after_login'] ?? 0,
                'use_dynamic_end_date' => $validated['use_dynamic_end_date'] ?? false,
                'daily_interval_duration' => $validated['daily_interval_duration'] ?? null,
                'min_break_between' => $validated['min_break_between'] ?? null,
                'max_daily_submits' => $validated['max_daily_submits'] ?? null,
                'max_total_submits' => $validated['max_total_submits'] ?? null,
                'daily_start_time' => $validated['daily_start_time'] ?? null,
                'daily_end_time' => $validated['daily_end_time'] ?? null,
                'quest_available_at' => $validated['quest_available_at'] ?? null,
                'show_after_repeating' => $validated['show_after_repeating'] ?? null,
            ];

            $notificationConfig = [
                'show_progress_bar' => $validated['show_progress_bar'] ?? false,
                'show_notifications' => $validated['show_notifications'] ?? false,
                'notification_text' => $validated['notification_text'] ?? null,
            ];

            // Create schedule
            $schedule = MartSchedule::create([
                'mart_project_id' => $martProject->id,
                'questionnaire_id' => $validated['questionnaire_id'],
                'name' => $validated['name'],
                'introductory_text' => $validated['introductory_text'] ?? null,
                'type' => $validated['type'],
                'timing_config' => $timingConfig,
                'notification_config' => $notificationConfig,
                'is_ios_data_donation' => $validated['is_ios_data_donation'] ?? false,
                'is_android_data_donation' => $validated['is_android_data_donation'] ?? false,
            ]);

            // Create questions
            foreach ($validated['questions'] as $index => $questionData) {
                MartQuestion::create([
                    'schedule_id' => $schedule->id,
                    'position' => $index + 1,
                    'text' => $questionData['text'],
                    'image_url' => $questionData['image_url'] ?? null,
                    'video_url' => $questionData['video_url'] ?? null,
                    'type' => $questionData['type'],
                    'config' => $questionData['config'] ?? [],
                    'is_mandatory' => $questionData['mandatory'],
                    'version' => 1,
                ]);
            }

            DB::connection('mart')->commit();

            // Create notifications for existing users when new repeating schedule is added
            if ($schedule->type === 'repeating' && ($notificationConfig['show_notifications'] ?? false)) {
                $cases = $project->cases()->whereNotNull('user_id')->with('user')->get();
                foreach ($cases as $case) {
                    if ($case->user) {
                        $case->createNotificationsFromSchedules($case->user, collect([$schedule]));
                    }
                }
            }

            // Load questions for response
            $schedule->load('questions');

            return response()->json([
                'success' => true,
                'schedule' => $schedule,
            ]);
        } catch (\Exception $e) {
            DB::connection('mart')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create questionnaire: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update questions for a questionnaire.
     * Now updates individual MartQuestion records with version tracking
     */
    public function updateQuestions(Request $request, MartSchedule $schedule)
    {
        // Get main project for authorization
        $martProject = $schedule->martProject;
        $mainProject = $martProject->mainProject();

        if (! $mainProject) {
            return response()->json([
                'success' => false,
                'message' => 'Main project not found',
            ], 404);
        }

        $this->authorize('update', $mainProject);

        $validated = $request->validate([
            'introductory_text' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:single,repeating',
            'start_date_time' => 'nullable|array',
            'end_date_time' => 'nullable|array',
            'start_on_first_login' => 'nullable|boolean',
            'start_hours_after_login' => 'nullable|integer|min:0|max:168',
            'use_dynamic_end_date' => 'nullable|boolean',
            'show_progress_bar' => 'nullable|boolean',
            'show_notifications' => 'nullable|boolean',
            'notification_text' => 'nullable|string',
            'is_ios_data_donation' => 'nullable|boolean',
            'is_android_data_donation' => 'nullable|boolean',
            'daily_interval_duration' => 'nullable|integer',
            'min_break_between' => 'nullable|integer',
            'max_daily_submits' => 'nullable|integer',
            'max_total_submits' => 'nullable|integer',
            'daily_start_time' => 'nullable|string',
            'daily_end_time' => 'nullable|string',
            'quest_available_at' => 'nullable|in:startOfInterval,randomTimeWithinInterval',
            'questions' => 'required|array',
            'questions.*.uuid' => 'nullable|string',
            'questions.*.text' => 'required|string',
            'questions.*.image_url' => 'nullable|url|max:2048',
            'questions.*.video_url' => 'nullable|url|max:2048',
            'questions.*.type' => 'required|in:number,range,text,textarea,one choice,multiple choice,display',
            'questions.*.mandatory' => 'required|boolean',
            'questions.*.config' => 'nullable|array',
        ]);

        // Enforce mutual exclusivity: only one data donation type allowed
        $isIos = $validated['is_ios_data_donation'] ?? false;
        $isAndroid = $validated['is_android_data_donation'] ?? false;
        if ($isIos && $isAndroid) {
            return response()->json([
                'success' => false,
                'message' => 'A questionnaire can only be designated as iOS OR Android data donation, not both.',
            ], 422);
        }

        // Reject start_on_first_login for repeating questionnaires
        $effectiveType = $validated['type'] ?? $schedule->type;
        $effectiveStartOnLogin = $validated['start_on_first_login'] ?? ($schedule->timing_config['start_on_first_login'] ?? false);
        if ($effectiveType === 'repeating' && $effectiveStartOnLogin) {
            return response()->json([
                'success' => false,
                'message' => 'Start on first login is not supported for repeating questionnaires.',
            ], 422);
        }

        DB::connection('mart')->beginTransaction();

        try {
            // Update schedule basic fields
            if (isset($validated['introductory_text'])) {
                $schedule->introductory_text = $validated['introductory_text'];
            }
            if (isset($validated['name'])) {
                $schedule->name = $validated['name'];
            }
            if (isset($validated['type'])) {
                $schedule->type = $validated['type'];
            }

            // Update timing config if any timing fields are provided
            $timingFields = ['start_date_time', 'end_date_time', 'start_on_first_login', 'start_hours_after_login',
                'use_dynamic_end_date', 'daily_interval_duration', 'min_break_between', 'max_daily_submits',
                'max_total_submits', 'daily_start_time', 'daily_end_time', 'quest_available_at'];

            $hasTimingUpdates = collect($timingFields)->contains(fn($field) => array_key_exists($field, $validated));

            if ($hasTimingUpdates) {
                $currentTiming = $schedule->timing_config ?? [];
                foreach ($timingFields as $field) {
                    if (array_key_exists($field, $validated)) {
                        $currentTiming[$field] = $validated[$field];
                    }
                }
                $schedule->timing_config = $currentTiming;
            }

            // Update notification config if any notification fields are provided
            $notifFields = ['show_progress_bar', 'show_notifications', 'notification_text'];
            $hasNotifUpdates = collect($notifFields)->contains(fn($field) => array_key_exists($field, $validated));

            if ($hasNotifUpdates) {
                $currentNotif = $schedule->notification_config ?? [];
                foreach ($notifFields as $field) {
                    if (array_key_exists($field, $validated)) {
                        $currentNotif[$field] = $validated[$field];
                    }
                }
                $schedule->notification_config = $currentNotif;
            }

            // Update data donation fields (mutually exclusive)
            if (array_key_exists('is_ios_data_donation', $validated)) {
                $schedule->is_ios_data_donation = $validated['is_ios_data_donation'];
            }
            if (array_key_exists('is_android_data_donation', $validated)) {
                $schedule->is_android_data_donation = $validated['is_android_data_donation'];
            }

            $schedule->save();

            // Track which UUIDs we've processed to detect deleted questions
            $processedUuids = [];

            foreach ($validated['questions'] as $index => $questionData) {
                if (!empty($questionData['uuid'])) {
                    // Update existing question
                    $question = MartQuestion::find($questionData['uuid']);

                    if (! $question || $question->schedule_id !== $schedule->id) {
                        throw new \Exception("Question {$questionData['uuid']} not found in this schedule");
                    }

                    // Update question (automatically creates history and increments version)
                    $saved = $question->updateQuestion([
                        'text' => $questionData['text'],
                        'image_url' => $questionData['image_url'] ?? null,
                        'video_url' => $questionData['video_url'] ?? null,
                        'type' => $questionData['type'],
                        'config' => $questionData['config'] ?? [],
                        'is_mandatory' => $questionData['mandatory'],
                    ]);

                    if (!$saved) {
                        throw new \Exception("Failed to save question {$questionData['uuid']}");
                    }

                    // Update position
                    $question->position = $index + 1;
                    $question->save();

                    $processedUuids[] = $questionData['uuid'];
                } else {
                    // Create new question
                    $newQuestion = MartQuestion::create([
                        'schedule_id' => $schedule->id,
                        'position' => $index + 1,
                        'text' => $questionData['text'],
                        'image_url' => $questionData['image_url'] ?? null,
                        'video_url' => $questionData['video_url'] ?? null,
                        'type' => $questionData['type'],
                        'config' => $questionData['config'] ?? [],
                        'is_mandatory' => $questionData['mandatory'],
                        'version' => 1,
                    ]);

                    $processedUuids[] = $newQuestion->uuid;
                }
            }

            // Delete questions that were removed (not in the request)
            $schedule->questions()
                ->whereNotIn('uuid', $processedUuids)
                ->delete();

            DB::connection('mart')->commit();

            return response()->json([
                'success' => true,
                'schedule' => $schedule->fresh()->load('questions'),
            ]);
        } catch (\Exception $e) {
            DB::connection('mart')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get question version history for a questionnaire.
     * Now queries MartQuestionHistory for each question
     */
    public function history(MartSchedule $schedule)
    {
        // Get main project for authorization
        $martProject = $schedule->martProject;
        $mainProject = $martProject->mainProject();

        if (! $mainProject) {
            return response()->json([
                'success' => false,
                'message' => 'Main project not found',
            ], 404);
        }

        $this->authorize('view', $mainProject);

        // Get questions with their history
        $questions = $schedule->questions()->with('history')->get();

        $historyData = $questions->map(function ($question) {
            return [
                'question_uuid' => $question->uuid,
                'question_text' => $question->text,
                'current_version' => $question->version,
                'history' => $question->history,
            ];
        });

        return response()->json([
            'success' => true,
            'questions' => $historyData,
        ]);
    }

    /**
     * Delete a questionnaire (schedule) and its questions.
     * Preserves MartQuestionHistory for data integrity.
     */
    public function destroy(MartSchedule $schedule)
    {
        // Get main project for authorization
        $martProject = $schedule->martProject;
        $mainProject = $martProject->mainProject();

        if (! $mainProject) {
            return response()->json([
                'success' => false,
                'message' => 'Main project not found',
            ], 404);
        }

        $this->authorize('update', $mainProject);

        DB::connection('mart')->beginTransaction();

        try {
            // Delete per-case schedule overrides (if table exists)
            try {
                \App\Mart\MartCaseSchedule::where('schedule_id', $schedule->id)->delete();
            } catch (\Illuminate\Database\QueryException $e) {
                // Table may not exist in some environments, skip silently
                \Log::debug('MartCaseSchedule table not found, skipping deletion');
            }

            // Delete questions (MartQuestionHistory is preserved - it uses question_uuid, not foreign key)
            $schedule->questions()->delete();

            // Delete the schedule itself
            $schedule->delete();

            DB::connection('mart')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Questionnaire deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::connection('mart')->rollBack();

            \Log::error('Failed to delete questionnaire', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete questionnaire: ' . $e->getMessage(),
            ], 500);
        }
    }
}
