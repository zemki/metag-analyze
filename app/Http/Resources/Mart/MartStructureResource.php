<?php

namespace App\Http\Resources\Mart;

use App\Entry;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartStat;
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
            // Handle MART project with multiple questionnaires per schedule
            $questionnaires = [];
            $scales = [];
            $scaleIndex = 0;

            // Build questionnaires from schedules
            if ($this->schedules && $this->schedules->isNotEmpty()) {
                foreach ($this->schedules as $schedule) {
                    $scheduleQuestions = $schedule->questions ?? [];

                    if (!empty($scheduleQuestions)) {
                        // Create questionnaire for this schedule
                        $questionnaires[] = new QuestionSheetResource($project, $scheduleQuestions, $martConfig, $schedule->questionnaire_id);

                        // Create scales for this schedule's questions
                        foreach ($scheduleQuestions as $question) {
                            $question['projectId'] = $project->id;
                            $scales[] = new ScaleResource((object) $question, $scaleIndex, true); // true = isMartProject
                            $scaleIndex++;
                        }
                    }
                }
            }

            // Get pages for MART project from MART database
            $martProject = $project->martProject();
            $pages = $martProject ? $martProject->pages()->orderBy('sort_order')->get() : collect();
            $pageResources = $pages->map(function ($page) {
                return new MartPageResource($page);
            });

            $response = [
                'projectOptions' => new ProjectOptionsResource($project, $martConfig, $this->schedules),
                'questionnaires' => $questionnaires,
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
     * Get device info for participant from MART database
     */
    private function getDeviceInfo($participantId)
    {
        // Query MART database for device info
        $deviceInfos = MartDeviceInfo::forParticipant($participantId)->get();

        return $deviceInfos->map(function ($deviceInfo) {
            return [
                'os' => $deviceInfo->os,
                'osVersion' => $deviceInfo->os_version,
                'model' => $deviceInfo->model,
                'manufacturer' => $deviceInfo->manufacturer,
                'lastUpdated' => $deviceInfo->last_updated ? $deviceInfo->last_updated->toISOString() : null,
            ];
        })->toArray();
    }

    /**
     * Get all questionnaire submissions for participant from MART database
     */
    private function getSubmissions($participantId)
    {
        // Query MART database for entries
        $entries = MartEntry::forParticipant($participantId)->get();

        return $entries->map(function ($entry) {
            return [
                'questionnaireId' => $entry->questionnaire_id,
                'timestamp' => $entry->timestamp,
            ];
        })->toArray();
    }

    /**
     * Get last data donation questionnaire submission (manual iOS/Android stats)
     * Now queries MART database
     */
    private function getLastDataDonationSubmit($participantId)
    {
        // Get last stat submission with iOS or Android stats from MART database
        $stat = MartStat::forParticipant($participantId)
            ->where(function ($query) {
                $query->whereNotNull('ios_stats')
                    ->orWhereNotNull('android_usage_stats')
                    ->orWhereNotNull('android_event_stats');
            })
            ->orderBy('timestamp', 'desc')
            ->first();

        return $stat ? $stat->timestamp : null;
    }

    /**
     * Get last automatic Android stats submission timestamp
     * Now queries MART database
     */
    private function getLastAndroidStatsSubmit($participantId)
    {
        // Get last Android stats submission from MART database
        $stat = MartStat::forParticipant($participantId)
            ->whereNotNull('android_usage_stats')
            ->orderBy('timestamp', 'desc')
            ->first();

        return $stat ? $stat->timestamp : null;
    }
}
