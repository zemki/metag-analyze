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
            'questions.*.type' => 'required|in:scale,text,one choice,multiple choice',
            'questions.*.mandatory' => 'required|boolean',
            'questions.*.config' => 'nullable|array',
        ]);

        // Get or create MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            $martProject = MartProject::create(['main_project_id' => $project->id]);
        }

        // Calculate end_date_time if max_total_submits is provided for repeating questionnaires
        if ($validated['type'] === 'repeating' &&
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
            'questions' => 'required|array',
            'questions.*.uuid' => 'required|string',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:scale,text,one choice,multiple choice',
            'questions.*.mandatory' => 'required|boolean',
            'questions.*.config' => 'nullable|array',
            'questions.*.is_ios_data_collection' => 'nullable|boolean',
            'questions.*.is_android_data_collection' => 'nullable|boolean',
            'questions.*.item_group' => 'nullable|string',
        ]);

        DB::connection('mart')->beginTransaction();

        try {
            // Update schedule introductory text if provided
            if (isset($validated['introductory_text'])) {
                $schedule->introductory_text = $validated['introductory_text'];
                $schedule->save();
            }

            foreach ($validated['questions'] as $questionData) {
                $question = MartQuestion::find($questionData['uuid']);

                if (! $question || $question->schedule_id !== $schedule->id) {
                    throw new \Exception("Question {$questionData['uuid']} not found in this schedule");
                }

                // Update question (automatically creates history and increments version)
                $question->updateQuestion([
                    'text' => $questionData['text'],
                    'type' => $questionData['type'],
                    'config' => $questionData['config'] ?? [],
                    'is_mandatory' => $questionData['mandatory'],
                ]);

                // Update the new fields directly (these don't need versioning)
                $question->is_ios_data_collection = $questionData['is_ios_data_collection'] ?? false;
                $question->is_android_data_collection = $questionData['is_android_data_collection'] ?? false;
                $question->item_group = $questionData['item_group'] ?? null;
                $question->save();
            }

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
}
