<?php

namespace App\Http\Resources\Mart;

use App\Entry;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MartStructureResource extends JsonResource
{
    protected $schedules;
    protected $participantId;

    public function __construct($resource, $schedules = null)
    {
        parent::__construct($resource);
        $this->schedules = $schedules;
    }

    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;
    }

    public function toArray($request)
    {
        $project = $this->resource;
        $inputs = json_decode($project->inputs, true);

        // Check if this is a MART project
        $isMartProject = false;
        $martConfig = null;
        $questions = [];

        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                if (isset($input['type']) && $input['type'] === 'mart') {
                    $isMartProject = true;
                    $martConfig = $input;
                } else {
                    // This is a question/input
                    $questions[] = $input;
                }
            }
        }

        if ($isMartProject && $martConfig) {
            // Handle MART project
            $questionSheet = new QuestionSheetResource($project, $questions, $martConfig);

            // Create scales from MART questions
            $scales = [];
            foreach ($questions as $index => $question) {
                $question['projectId'] = $project->id;
                $scales[] = new ScaleResource((object) $question, $index, true); // true = isMartProject
            }

            // Get pages for MART project
            $pages = $project->pages()->orderBy('sort_order')->get();
            $pageResources = $pages->map(function ($page) {
                return new MartPageResource($page);
            });

            $response = [
                'projectOptions' => new ProjectOptionsResource($project, $martConfig, $this->schedules),
                'questionnaires' => [$questionSheet],
                'scales' => $scales,
                'pages' => $pageResources,
            ];

            // Add participant data if participant_id is provided
            if ($this->participantId) {
                $response['deviceInfos'] = $this->getDeviceInfo($this->participantId);
                $submissions = $this->getSubmissions($this->participantId);
                $response['repeatingSubmits'] = $submissions;
                $response['singleSubmits'] = $submissions;
                $response['lastDataDonationSubmit'] = $this->getLastDataDonationSubmit($this->participantId);
                $response['lastAndroidStatsSubmit'] = $this->getLastAndroidStatsSubmit($this->participantId);
            }

            return $response;
        } else {
            // Handle standard MetaG project (backward compatibility)
            $questionSheet = new QuestionSheetResource($project, $inputs);

            // Create scales from inputs
            $scales = [];
            if (is_array($inputs)) {
                foreach ($inputs as $index => $input) {
                    $input['projectId'] = $project->id;
                    $scales[] = new ScaleResource((object) $input, $index, false); // false = not MART project
                }
            }

            $response = [
                'projectOptions' => new ProjectOptionsResource($project, null, $this->schedules),
                'questionnaires' => [$questionSheet],
                'scales' => $scales,
                'pages' => [], // Standard projects don't have pages
            ];

            // Add participant data if participant_id is provided
            if ($this->participantId) {
                $response['deviceInfos'] = $this->getDeviceInfo($this->participantId);
                $submissions = $this->getSubmissions($this->participantId);
                $response['repeatingSubmits'] = $submissions;
                $response['singleSubmits'] = $submissions;
                $response['lastDataDonationSubmit'] = $this->getLastDataDonationSubmit($this->participantId);
                $response['lastAndroidStatsSubmit'] = $this->getLastAndroidStatsSubmit($this->participantId);
            }

            return $response;
        }
    }

    /**
     * Get device info for participant from User model
     */
    private function getDeviceInfo($participantId)
    {
        // Find user by participant_id from entries
        $entry = Entry::join('cases', 'entries.case_id', '=', 'cases.id')
            ->where('cases.name', $participantId)
            ->first(['entries.inputs']);

        if (!$entry) {
            return [];
        }

        $inputs = json_decode($entry->inputs, true);
        $userId = $inputs['_mart_metadata']['user_id'] ?? null;

        if (!$userId) {
            return [];
        }

        $user = User::where('email', $userId)->first();
        if (!$user || !$user->deviceID) {
            return [];
        }

        $deviceInfo = json_decode($user->deviceID, true);
        return [$deviceInfo]; // Return as array
    }

    /**
     * Get all questionnaire submissions for participant
     */
    private function getSubmissions($participantId)
    {
        $submissions = [];

        // Get all entries for this participant
        $entries = Entry::join('cases', 'entries.case_id', '=', 'cases.id')
            ->where('cases.name', $participantId)
            ->get(['entries.inputs']);

        foreach ($entries as $entry) {
            $inputs = json_decode($entry->inputs, true);
            $metadata = $inputs['_mart_metadata'] ?? null;

            if ($metadata && isset($metadata['questionnaire_id']) && isset($metadata['timestamp'])) {
                $submissions[] = [
                    'questionnaireId' => $metadata['questionnaire_id'],
                    'timestamp' => $metadata['timestamp']
                ];
            }
        }

        return $submissions;
    }

    /**
     * Get last data donation questionnaire submission (manual iOS/Android stats)
     */
    private function getLastDataDonationSubmit($participantId)
    {
        // Stats table is broken, return null for now
        return null;
    }

    /**
     * Get last automatic Android stats submission timestamp
     */
    private function getLastAndroidStatsSubmit($participantId)
    {
        // Stats table is broken, return null for now
        return null;
    }
}
