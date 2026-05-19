<?php

namespace App\Http\Resources\Mart;

use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MartStructureResource extends JsonResource
{
    protected $schedules;
    protected $participantId;
    protected $caseId;

    public function __construct($resource, $schedules = null)
    {
        parent::__construct($resource);
        $this->schedules = $schedules;
    }

    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;
    }

    public function setCaseId($caseId)
    {
        $this->caseId = $caseId;
    }

    public function toArray($request)
    {
        $project = $this->resource;
        $inputs = json_decode($project->inputs, true);

        // Check if this is a MART project using the model method
        $isMartProject = $project->isMartProject();
        $martConfig = null;
        $questions = [];

        // Extract MART config and separate questions
        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                if (isset($input['type']) && $input['type'] === 'mart') {
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

            // Build questionnaires from schedules
            if ($this->schedules && $this->schedules->isNotEmpty()) {
                foreach ($this->schedules as $schedule) {
                    $scheduleQuestions = $schedule->questions ?? [];

                    if (!empty($scheduleQuestions)) {
                        // Create questionnaire for this schedule, passing the schedule's
                        // own name so each questionnaire is named distinctly in the response.
                        $questionnaires[] = new QuestionSheetResource(
                            $project,
                            $scheduleQuestions,
                            $martConfig,
                            $schedule->questionnaire_id,
                            $schedule->name
                        );

                        // Create scales for this schedule's questions (skip display-only items)
                        // Use item index to keep scaleId consistent with the item's scaleId reference
                        foreach ($scheduleQuestions as $index => $question) {
                            // Skip display-only items - they have no scale (itemId/scaleId are null)
                            if (($question['type'] ?? '') === 'display') {
                                continue;
                            }
                            $question['projectId'] = $project->id;
                            // Use the item's index so scaleId matches the item's scaleId reference
                            $scales[] = new ScaleResource((object) $question, $index, true); // true = isMartProject
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
                'projectOptions' => new ProjectOptionsResource($project, $martConfig, $this->schedules, $this->caseId),
                'questionnaires' => $questionnaires,
                'scales' => $scales,
                'pages' => $pageResources,
                // Always include these fields (required by martTypes.ts)
                'deviceInfos' => $this->participantId ? $this->getDeviceInfo($this->participantId) : [],
                'repeatingSubmits' => $this->participantId ? $this->getSubmissionsByType($this->participantId, 'repeating') : [],
                'singleSubmits' => $this->participantId ? $this->getSubmissionsByType($this->participantId, 'single') : [],
                'lastDataDonationSubmit' => $this->participantId ? $this->getLastDataDonationSubmit($this->participantId, $project) : null,
                'lastAndroidStatsSubmit' => $this->participantId ? $this->getLastAndroidStatsSubmit($this->participantId) : null,
            ];

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
                'projectOptions' => new ProjectOptionsResource($project, null, $this->schedules, $this->caseId),
                'questionnaires' => [$questionSheet],
                'scales' => $scales,
                'pages' => [], // Standard projects don't have pages
                // Always include these fields (required by martTypes.ts)
                'deviceInfos' => $this->participantId ? $this->getDeviceInfo($this->participantId) : [],
                'repeatingSubmits' => $this->participantId ? $this->getSubmissionsByType($this->participantId, 'repeating') : [],
                'singleSubmits' => $this->participantId ? $this->getSubmissionsByType($this->participantId, 'single') : [],
                'lastDataDonationSubmit' => $this->participantId ? $this->getLastDataDonationSubmit($this->participantId, $project) : null,
                'lastAndroidStatsSubmit' => $this->participantId ? $this->getLastAndroidStatsSubmit($this->participantId) : null,
            ];

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
     * Get questionnaire submissions for a participant filtered by schedule type
     * ('single' or 'repeating'). Joins mart_entries -> mart_schedules so we
     * can return only the submissions whose schedule matches the requested type.
     */
    private function getSubmissionsByType($participantId, string $type)
    {
        $entries = MartEntry::forParticipant($participantId)
            ->join('mart_schedules', 'mart_entries.schedule_id', '=', 'mart_schedules.id')
            ->where('mart_schedules.type', $type)
            ->orderBy('mart_entries.timestamp', 'desc')
            ->get(['mart_entries.questionnaire_id', 'mart_entries.timestamp']);

        return $entries->map(function ($entry) {
            return [
                'questionnaireId' => $entry->questionnaire_id,
                'timestamp' => $entry->timestamp,
            ];
        })->toArray();
    }

    /**
     * Get last data donation submission for the participant.
     *
     * "Data donation" = a schedule flagged either `is_ios_data_donation`
     * or `is_android_data_donation` for this project (both flags appear in
     * `projectOptions.iOSDataDonationQuestionnaire` and
     * `projectOptions.androidDataDonationQuestionnaire` respectively). We
     * return the timestamp of the most recent MartEntry whose schedule_id
     * is one of those flagged schedules — across both platforms, whichever
     * was submitted last wins.
     *
     * Returns { timestamp: number } per martTypes.ts (no questionnaireId).
     */
    private function getLastDataDonationSubmit($participantId, $project)
    {
        $martProject = $project->martProject();
        if (!$martProject) {
            return null;
        }

        $donationScheduleIds = MartSchedule::where('mart_project_id', $martProject->id)
            ->where(function ($q) {
                $q->where('is_ios_data_donation', true)
                  ->orWhere('is_android_data_donation', true);
            })
            ->pluck('id');

        if ($donationScheduleIds->isEmpty()) {
            return null;
        }

        $entry = MartEntry::forParticipant($participantId)
            ->whereIn('schedule_id', $donationScheduleIds)
            ->orderBy('timestamp', 'desc')
            ->first();

        return $entry ? ['timestamp' => $entry->timestamp] : null;
    }

    /**
     * Get last automatic Android stats submission timestamp
     * Returns object per martTypes.ts: { timestamp: number }
     * Now queries MART database
     */
    private function getLastAndroidStatsSubmit($participantId)
    {
        // Get last Android stats submission from MART database
        $stat = MartStat::forParticipant($participantId)
            ->whereNotNull('android_usage_stats')
            ->orderBy('timestamp', 'desc')
            ->first();

        // Return object with timestamp per martTypes.ts
        return $stat ? ['timestamp' => $stat->timestamp] : null;
    }
}
