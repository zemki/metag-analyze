<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Http\Resources\Mart\MartStructureResource;
use App\Mart\MartAnswer;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\Project;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MartApiController extends Controller
{
    /**
     * Get project structure
     *
     * Returns questionnaires, scales, pages, and participant data.
     * Pass `participant_id` as a query param for per-case data.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getProjectStructure(Request $request, Project $project)
    {
        // Get MART project from MART database
        $martProject = $project->martProject();

        if (!$martProject) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "MART project not found",
                ],
                404,
            );
        }

        // Get questionnaire schedules from MART database with questions
        $schedules = MartSchedule::forProject($martProject->id)
            ->with("questions")
            ->get();

        // Get participant_id from query parameter OR request body (mobile app sends in body)
        $participantId =
            $request->query("participant_id") ??
            $request->input("participant_id");

        // Look up case for per-case date overrides
        $case = null;
        if ($participantId) {
            $case = Cases::where("project_id", $project->id)
                ->where("name", $participantId)
                ->first();

            // If a participant_id was explicitly provided but doesn't match any
            // case in this project, fail loudly instead of silently falling back
            // to the bearer-token user's case.
            if (!$case) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "No case found for the provided participant_id",
                    ],
                    404,
                );
            }
        }

        // Fallback: find case by authenticated user if no participant_id
        if (!$case && $request->user()) {
            $case = Cases::where("project_id", $project->id)
                ->where("user_id", $request->user()->id)
                ->first();

            // Auto-create case if authenticated user has no case in this project
            // This supports bearer token testing without the 3-screen auth flow
            if (!$case) {
                $case = Cases::create([
                    "name" => "P" . strtoupper(substr(md5(uniqid()), 0, 6)),
                    "user_id" => $request->user()->id,
                    "project_id" => $project->id,
                    "duration" => "startDay:" . now()->format("d.m.Y") . "|",
                    "first_login_at" => now(),
                ]);

                \Log::info("Auto-created MART case via structure endpoint", [
                    "user_id" => $request->user()->id,
                    "project_id" => $project->id,
                    "case_id" => $case->id,
                    "participant_id" => $case->name,
                ]);

                // Calculate dynamic dates for schedules with "start on login"
                \App\Mart\MartCaseSchedule::calculateForCase($case);
            }

            $participantId = $case->name;
        }

        $caseId = $case?->id;

        // Pass schedules, participant_id, and case_id to the resource
        $resource = new MartStructureResource($project, $schedules);
        $resource->setParticipantId($participantId);
        $resource->setCaseId($caseId);
        $resource->setUserEmail($case?->user?->email);

        return $resource;
    }

    /**
     * Submit entry
     *
     * Stores a questionnaire submission from the mobile app.
     * Validates case status (rejects completed cases with 422) and
     * answers against the questionnaire schedule.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function submitEntry(Request $request, Cases $case)
    {
        // Validate request according to martTypes.ts Submit type
        // Note: answers values can be numbers (scale), arrays (multiple choice), or strings (text)
        $request->validate([
            "projectId" => "required|numeric",
            "questionnaireId" => "required|numeric",
            "userId" => "required|string",
            "participantId" => "required|string",
            "sheetId" => "nullable|numeric", // DEPRECATED: Legacy field from old "QuestionSheets" terminology. Use questionnaireId instead.
            "questionnaireStarted" => "required|numeric",
            "questionnaireDuration" => "required|numeric",
            // present, not required: a questionnaire of display items only
            // has no itemId to answer, and the app sends `answers: {}`.
            'answers' => 'present|array',
            "answers.*" => "present", // Explicitly allow any type for answer values
            "timestamp" => "required|numeric",
            "timestampInherited" => "nullable|numeric",
            "timezone" => "required|string",
        ]);

        // Verify case belongs to the project
        if ($case->project_id != $request->projectId) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Case does not belong to the specified project",
                ],
                400,
            );
        }

        // Verify the userId in the body matches the case owner. Without this,
        // any authenticated caller could attribute submissions to any string.
        // Case-insensitive: the invite keeps the email as typed, login finds the
        // account in any case, and the app sends the email the participant typed.
        if ($case->user && strcasecmp($case->user->email, (string) $request->userId) !== 0) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "userId does not match the case owner",
                ],
                422,
            );
        }

        // PHASE 1: Case Status Validation
        $caseStatus = $case->getStatus();
        if ($caseStatus === \App\Enums\CaseStatus::COMPLETED) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Cannot submit to completed case. Case has finished and no longer accepts submissions.",
                    "case_status" => $caseStatus->value,
                ],
                422,
            );
        }

        // Only allow submissions to pending or active cases
        if (
            !in_array($caseStatus, [
                \App\Enums\CaseStatus::PENDING,
                \App\Enums\CaseStatus::ACTIVE,
            ])
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Case is not accepting submissions",
                    "case_status" => $caseStatus->value,
                ],
                422,
            );
        }

        // PHASE 2: Project Data Conformity Validation
        $project = Project::find($request->projectId);
        if (!$project) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Project not found",
                ],
                404,
            );
        }

        // Validate answers against project structure (from MART DB)
        $validationResult = $this->validateAnswersAgainstProject(
            $request->answers,
            $project,
            $request->questionnaireId,
        );
        if (!$validationResult["valid"]) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Answer validation failed",
                    "errors" => $validationResult["errors"],
                ],
                422,
            );
        }

        // Get MART project and schedule from MART DB
        $martProject = $project->martProject();
        if (!$martProject) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "MART project not found",
                ],
                404,
            );
        }

        $schedule = MartSchedule::forProject($martProject->id)
            ->where("questionnaire_id", $request->questionnaireId)
            ->with("questions")
            ->first();

        if (!$schedule) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Questionnaire schedule not found",
                ],
                404,
            );
        }

        DB::connection("mart")->beginTransaction();

        try {
            // Step 1: Create MART entry in MART database
            $martEntryData = [
                "case_id" => $case->id,
                "schedule_id" => $schedule->id,
                "questionnaire_id" => $request->questionnaireId,
                "participant_id" => $request->participantId,
                "user_id" => $request->userId,
                "started_at" => date(
                    "Y-m-d H:i:s",
                    $request->questionnaireStarted / 1000,
                ),
                "completed_at" => date(
                    "Y-m-d H:i:s",
                    ($request->questionnaireStarted +
                        $request->questionnaireDuration) /
                        1000,
                ),
                "duration_ms" => $request->questionnaireDuration,
                "timezone" => $request->timezone,
                "timestamp" => $request->timestamp,
            ];

            $martEntry = MartEntry::create($martEntryData);

            // Step 2: Create MART answers for each question
            $questions = $this->questionsInItemOrder($schedule);
            // File UUIDs to link, by the UUID of the question they answer.
            $fileIdsToLink = [];

            foreach ($request->answers as $itemId => $answerValue) {
                $question = $questions[(int) $itemId - 1] ?? null;

                if ($question) {
                    // Check if this is a file-type answer (contains file UUIDs)
                    // The app sends [fileUrl, fileId] for upload-type questions,
                    // and only the bare UUID matches below. The editor stores
                    // these types as photo / audio / video / file; the
                    // photoUpload names only exist in the API payload.
                    if (in_array($question->type, ['photo', 'audio', 'video', 'file'], true)) {
                        // Collect file IDs to link to entry
                        $fileIds = is_array($answerValue)
                            ? $answerValue
                            : [$answerValue];
                        foreach ($fileIds as $fileId) {
                            if (
                                is_string($fileId) &&
                                preg_match('/^[0-9a-f-]{36}$/i', $fileId)
                            ) {
                                $fileIdsToLink[$question->uuid][] = $fileId;
                            }
                        }
                    }

                    MartAnswer::create([
                        "entry_id" => $martEntry->id,
                        "question_uuid" => $question->uuid,
                        "question_version" => $question->version,
                        "answer_value" => is_array($answerValue)
                            ? json_encode($answerValue)
                            : $answerValue,
                    ]);
                }
            }

            // Step 3: Link uploaded files to the entry and their question
            $linkErrors = [];
            foreach ($fileIdsToLink as $questionUuid => $fileIds) {
                $linkResults = MartFileController::linkFilesToEntry(
                    $fileIds,
                    $martEntry->id,
                    $case->id,
                    $questionUuid,
                );
                $linkErrors = array_merge($linkErrors, $linkResults['errors']);
            }
            if (! empty($linkErrors)) {
                \Log::warning('Some files could not be linked to entry', [
                    'entry_id' => $martEntry->id,
                    'errors' => $linkErrors,
                ]);
            }

            DB::connection("mart")->commit();

            return response()->json([
                "success" => true,
                "entry_id" => $martEntry->id,
                "message" => "Entry created successfully",
            ]);
        } catch (\Exception $e) {
            DB::connection("mart")->rollBack();

            // Not the exception message, here or in the response: a database
            // error's message holds the SQL values, such as participant IDs
            // and emails.
            \Log::error("MART submit failed", [
                "case_id" => $case->id,
                "exception" => get_class($e),
                "code" => $e->getCode(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return response()->json(
                [
                    "success" => false,
                    "message" => "Failed to create entry",
                ],
                500,
            );
        }
    }

    /**
     * Store device info
     *
     * Stores or updates device info for a participant. `userId` (email),
     * `projectId`, and `participantId` must match an existing case.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function storeDeviceInfo(Request $request)
    {
        // Validate request
        $request->validate([
            "projectId" => "required|numeric|exists:projects,id",
            "userId" => "required|string|email",
            "participantId" => "required|string",
            "os" => "required|in:android,ios",
            "osVersion" => "required|string",
            "model" => "required|string",
            "manufacturer" => "required|string",
            "timestamp" => "required|numeric",
            "timezone" => "required|string",
            // Optional so existing clients keep working unchanged.
            "fcmToken" => "sometimes|nullable|string|max:4096",
        ]);

        // Verify user access
        $user = $this->verifyUserAccess($request);
        if ($user instanceof JsonResponse) {
            return $user; // Return the error response
        }

        // Update or create device info in MART database
        $deviceInfo = MartDeviceInfo::updateOrCreate(
            [
                "participant_id" => $request->participantId,
                "user_id" => $request->userId,
            ],
            [
                "os" => $request->os,
                "os_version" => $request->osVersion,
                "model" => $request->model,
                "manufacturer" => $request->manufacturer,
                "last_updated" => now(),
            ],
        );

        // Registering the push token here is a convenience: it saves the app a
        // second call at login. Token *refresh* still needs the dedicated
        // endpoint below, because FCM can rotate a token at any time, outside
        // any project context.
        $tokenStored = false;
        if ($request->filled("fcmToken")) {
            $tokenStored = $user->addDeviceToken($request->input("fcmToken"));
        }

        return response()->json([
            "success" => true,
            "message" => "Device information stored successfully",
            "device_info_id" => $deviceInfo->id,
            "push_token_registered" => $tokenStored,
        ]);
    }

    /**
     * Register or unregister this device's FCM token for the authenticated user.
     *
     * Without this the notifications the researcher schedules have nowhere to
     * go: routeNotificationForFcm() reads User::$deviceID, and until now
     * nothing ever wrote to it.
     *
     * The app must call this after notification permission is granted, and
     * again whenever FCM issues a new token (Android onNewToken / the iOS
     * delegate equivalent) - otherwise delivery decays as tokens rotate.
     */
    public function storeDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            "token" => "required|string|max:4096",
            "action" => "sometimes|in:register,unregister",
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(
                ["success" => false, "message" => "Unauthenticated"],
                401,
            );
        }

        $unregister = $request->input("action") === "unregister";

        $changed = $unregister
            ? $user->removeDeviceToken($request->input("token"))
            : $user->addDeviceToken($request->input("token"));

        return response()->json([
            "success" => true,
            // false simply means the token was already in (or already absent
            // from) the list - not an error.
            "changed" => $changed,
            "registered_devices" => count((array) ($user->fresh()->deviceID ?? [])),
        ]);
    }

    /**
     * Submit usage stats
     *
     * Stores Android (usage/event) or iOS usage statistics for a participant.
     * `userId` (email), `projectId`, and `participantId` must match an
     * existing case.
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function submitStats(Request $request)
    {
        // Validate request
        $request->validate([
            "projectId" => "required|numeric|exists:projects,id",
            "userId" => "required|string|email",
            "participantId" => "required|string",
            "timestamp" => "required|numeric",
            "timezone" => "required|string",
            "androidUsageStats" => "nullable|array",
            "androidEventStats" => "nullable|array",
            "iOSStats" => "nullable|array",
        ]);

        // Verify user access
        $user = $this->verifyUserAccess($request);
        if ($user instanceof JsonResponse) {
            return $user; // Return the error response
        }

        // Get MART project
        $project = Project::find($request->projectId);
        $martProject = $project->martProject();

        if (!$martProject) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "MART project not found",
                ],
                404,
            );
        }

        // Get device ID from MART database
        $deviceInfo = MartDeviceInfo::forParticipant($request->participantId)
            ->forUser($request->userId)
            ->first();

        // Prepare stats data for MART database
        $statsData = [
            "mart_project_id" => $martProject->id,
            "user_id" => $request->userId,
            "participant_id" => $request->participantId,
            "timestamp" => $request->timestamp,
            "timezone" => $request->timezone,
            "device_id" => $deviceInfo ? $deviceInfo->id : null,
        ];

        // Add platform-specific stats
        if ($request->has("androidUsageStats")) {
            $statsData["android_usage_stats"] = $request->androidUsageStats;
        }

        if ($request->has("androidEventStats")) {
            $statsData["android_event_stats"] = $request->androidEventStats;
        }

        if ($request->has("iOSStats")) {
            $statsData["ios_stats"] = $request->iOSStats;
            // Extract specific iOS fields if present
            if (isset($request->iOSStats["screenTime"])) {
                $statsData["ios_screen_time"] =
                    $request->iOSStats["screenTime"];
            }
            if (isset($request->iOSStats["pickupCount"])) {
                $statsData["ios_activations"] =
                    $request->iOSStats["pickupCount"];
            }
        }

        // Create stats entry in MART database
        $stat = MartStat::create($statsData);

        return response()->json([
            "success" => true,
            "stat_id" => $stat->id,
            "message" => "Stats submitted successfully",
        ]);
    }

    /**
     * Verify user exists and has access to the project through a case
     */
    private function verifyUserAccess(Request $request)
    {
        $user = User::where("email", $request->userId)->first();

        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "User not found",
                    "hint" =>
                        "The userId field must be the email address used during authentication.",
                ],
                404,
            );
        }

        // The token holder may only act for themselves. Otherwise anyone with a
        // token who knows another participant's email and participantId could
        // file device info or stats in their name, or take over their push
        // notifications through fcmToken.
        if ($request->user()?->id !== $user->id) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "userId does not match the authenticated user",
                    "hint" =>
                        "The userId field must be the email address of the account the bearer token belongs to.",
                ],
                403,
            );
        }

        // Verify user has access to the project through a case
        $hasAccess = Cases::where("project_id", $request->projectId)
            ->where("name", $request->participantId)
            ->where("user_id", $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "User does not have access to this project",
                    "hint" =>
                        "Verify that userId (email), projectId, and participantId match a case created during the check-access step.",
                ],
                403,
            );
        }

        return $user;
    }

    /**
     * Validate answers against project structure and constraints
     * Now validates against MART DB schedules, not project.inputs
     */
    private function validateAnswersAgainstProject(
        array $answers,
        Project $project,
        int $questionnaireId,
    ): array {
        $errors = [];

        // Get MART project
        $martProject = $project->martProject();
        if (!$martProject) {
            return ["valid" => false, "errors" => ["MART project not found"]];
        }

        // Find the schedule for this questionnaire
        $schedule = MartSchedule::forProject($martProject->id)
            ->where("questionnaire_id", $questionnaireId)
            ->with("questions")
            ->first();

        if (!$schedule) {
            return [
                "valid" => false,
                "errors" => [
                    "Questionnaire $questionnaireId not found in MART database",
                ],
            ];
        }

        // Get questions for this schedule
        $questions = $schedule->questions;

        if ($questions->isEmpty()) {
            return [
                "valid" => false,
                "errors" => ["Schedule has no questions"],
            ];
        }

        $questionsArray = $this->questionsInItemOrder($schedule);

        // Validate each answer
        // Note: Mobile app sends answers keyed by itemId (index + 1)
        foreach ($answers as $itemId => $answer) {
            // Convert itemId to array index: itemId = index + 1, so index = itemId - 1
            $itemIdInt = (int) $itemId;
            $index = $itemIdInt - 1;

            if (!isset($questionsArray[$index])) {
                $errors[] = "Question with itemId $itemId (index $index) does not exist in schedule";
                continue;
            }

            $question = $questionsArray[$index];
            $validationResult = $this->validateSingleAnswer(
                $answer,
                $question,
                $index,
            );

            if (!$validationResult["valid"]) {
                $errors = array_merge($errors, $validationResult["errors"]);
            }
        }

        // Check for missing required questions
        // Mobile app uses itemId (array index + 1) as key
        foreach ($questionsArray as $index => $question) {
            // The app sends no answer for a display item, which has no itemId,
            // so one saved as mandatory must not fail the submit.
            if ($question->type === 'display') {
                continue;
            }

            $itemId = $index + 1;
            // Check for both string and int keys for compatibility
            if (
                $question->is_mandatory &&
                !isset($answers[$itemId]) &&
                !isset($answers[(string) $itemId])
            ) {
                $errors[] = "Required question '{$question->text}' (itemId $itemId) is missing";
            }
        }

        return [
            "valid" => empty($errors),
            "errors" => $errors,
        ];
    }

    /**
     * A schedule's questions in the order the structure lists them, so that
     * index = itemId - 1. This is not the DB position, which starts at 1 for
     * questions saved in the editor.
     */
    private function questionsInItemOrder(MartSchedule $schedule): array
    {
        return $schedule->questions->sortBy("position")->values()->all();
    }

    /**
     * Validate a single answer against its question constraints.
     *
     * Currently accepts any answer type/value.
     * TODO: Re-enable strict validation once mobile app data format is finalized.
     */
    private function validateSingleAnswer(
        $answer,
        $question,
        int $questionPosition,
    ): array {
        return [
            "valid" => true,
            "errors" => [],
        ];
    }
}
