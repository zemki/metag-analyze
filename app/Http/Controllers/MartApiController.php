<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Cases;
use App\Entry;
use App\Http\Resources\Mart\MartStructureResource;
use App\MartQuestionnaireSchedule;
use App\Project;
use App\Stat;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MartApiController extends Controller
{
    /**
     * Get project structure for mobile app
     */
    public function getProjectStructure(Project $project)
    {
        // Get questionnaire schedules for this project
        $schedules = MartQuestionnaireSchedule::forProject($project->id)->get();
        
        // Pass schedules to the resource
        return new MartStructureResource($project, $schedules);
    }

    /**
     * Handle submission from mobile app
     */
    public function submitEntry(Request $request, Cases $case)
    {
        // Validate request according to martTypes.ts Submit type
        $request->validate([
            'projectId' => 'required|numeric',
            'questionnaireId' => 'required|numeric',
            'userId' => 'required|string',
            'participantId' => 'required|string',
            'sheetId' => 'required|numeric',
            'questionnaireStarted' => 'required|numeric',
            'questionnaireDuration' => 'required|numeric',
            'answers' => 'required|array',
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

        // Handle entity/media selection (skip for MART projects)
        $entityId = null;
        $project = Project::find($request->projectId);

        if ($project && !$project->isMartProject()) {
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

        // Transform MART answers to MetaG inputs format
        $transformedInputs = $this->transformMartAnswersToMetagInputs($request->answers);
        
        // Include MART metadata in the inputs
        $martMetadata = [
            'questionnaire_id' => $request->questionnaireId,
            'sheet_id' => $request->sheetId,
            'duration' => $request->questionnaireDuration,
            'timezone' => $request->timezone,
            'timestamp' => $request->timestamp,
        ];
        
        if ($request->has('timestampInherited')) {
            $martMetadata['timestamp_inherited'] = $request->timestampInherited;
        }
        
        // Merge MART metadata with answers
        $allInputs = array_merge($transformedInputs, ['_mart_metadata' => $martMetadata]);

        // Transform to Entry format
        $entryData = [
            'begin' => date('Y-m-d H:i:s', $request->questionnaireStarted / 1000),
            'end' => date('Y-m-d H:i:s', ($request->questionnaireStarted + $request->questionnaireDuration) / 1000),
            'case_id' => $case->id,
            'media_id' => $entityId, // Use resolved entity ID or null
            'inputs' => json_encode($allInputs),
        ];

        // Create entry
        $entry = Entry::create($entryData);

        return response()->json([
            'success' => true,
            'entry_id' => $entry->id,
            'message' => 'Entry created successfully',
        ]);
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

        // Store device info in a structured format
        $deviceInfo = [
            'os' => $request->os,
            'osVersion' => $request->osVersion,
            'model' => $request->model,
            'manufacturer' => $request->manufacturer,
            'lastUpdated' => now()->toISOString(),
        ];

        // Update user's deviceID with structured device info
        $user->deviceID = json_encode($deviceInfo);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device information stored successfully',
        ]);
    }

    /**
     * Submit usage statistics from mobile app
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

        // Prepare stats data
        $statsData = [
            'userId' => $request->userId,
            'projectId' => $request->projectId,
            'participantId' => $request->participantId,
            'timestamp' => $request->timestamp,
            'timezone' => $request->timezone,
            'deviceID' => $user->deviceID,
        ];

        // Add platform-specific stats
        if ($request->has('androidUsageStats')) {
            $statsData['androidUsageStats'] = $request->androidUsageStats;
        }

        if ($request->has('androidEventStats')) {
            $statsData['androidEventStats'] = $request->androidEventStats;
        }

        if ($request->has('iOSStats')) {
            $statsData['iosStats'] = $request->iOSStats;
            // Extract specific iOS fields if present
            if (isset($request->iOSStats['screenTime'])) {
                $statsData['iosScreenTime'] = $request->iOSStats['screenTime'];
            }
            if (isset($request->iOSStats['pickupCount'])) {
                $statsData['iosActivations'] = $request->iOSStats['pickupCount'];
            }
        }

        // Create stats entry
        $stat = Stat::create($statsData);

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
}
