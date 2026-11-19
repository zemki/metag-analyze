<?php

namespace App\Http\Resources\Mart;

use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MartStructureResource extends JsonResource
{
    protected $schedules;
    protected $participantId;
    protected $caseId;
    protected $userEmail;

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

    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
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
                        // The app looks a scale up by id over the whole list, so ids
                        // must be unique across questionnaires. Items with identical
                        // options share one scale; the app copies it for each item.
                        $itemScaleIds = [];
                        foreach ($scheduleQuestions as $index => $question) {
                            // A display item has no scale, unless it shows a choice's
                            // options, disabled. Its itemId stays null either way.
                            $isDisplay = ($question['type'] ?? '') === 'display';
                            if ($isDisplay && MartQuestion::displaySelectionType($question['config'] ?? null) === null) {
                                continue;
                            }
                            $options = (new ScaleResource((object) $question, $index, true))->scaleOptions();
                            $key = json_encode($options);
                            $scales[$key] ??= [
                                'scaleId' => $schedule->questionnaire_id * 1000 + $index + 1,
                                'options' => $options,
                            ];
                            $itemScaleIds[$index] = $scales[$key]['scaleId'];
                        }

                        // Create questionnaire for this schedule, passing the schedule's
                        // own name so each questionnaire is named distinctly in the response.
                        $questionnaires[] = new QuestionSheetResource(
                            $project,
                            $scheduleQuestions,
                            $martConfig,
                            $schedule->questionnaire_id,
                            $schedule->name,
                            $itemScaleIds
                        );
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
                'scales' => array_values($scales),
                'pages' => $pageResources,
                ...$this->participantData($martProject),
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
                ...$this->participantData($project->martProject()),
            ];

            return $response;
        }
    }

    /**
     * The participant's own data, always present (martTypes.ts requires the
     * keys). Case names can repeat across projects, so every lookup is
     * limited to this project, as Directus filters by projectId and
     * participantId.
     */
    private function participantData(?MartProject $martProject): array
    {
        if (! $this->participantId || ! $martProject) {
            return [
                'deviceInfos' => [],
                'repeatingSubmits' => [],
                'singleSubmits' => [],
                'lastDataDonationSubmit' => null,
                'lastAndroidStatsSubmit' => null,
            ];
        }

        return [
            'deviceInfos' => $this->getDeviceInfo($this->participantId),
            'repeatingSubmits' => $this->getSubmissionsByType($this->participantId, 'repeating', $martProject),
            'singleSubmits' => $this->getSubmissionsByType($this->participantId, 'single', $martProject),
            'lastDataDonationSubmit' => $this->getLastDataDonationSubmit($this->participantId, $martProject),
            'lastAndroidStatsSubmit' => $this->getLastAndroidStatsSubmit($this->participantId, $martProject),
        ];
    }

    /**
     * Get device info for participant from MART database. Device infos have
     * no project column, so they are matched on the case owner's email too:
     * a same-named participant in another project is someone else.
     */
    private function getDeviceInfo($participantId)
    {
        if (! $this->userEmail) {
            return [];
        }

        $deviceInfos = MartDeviceInfo::forParticipant($participantId)
            ->forUser($this->userEmail)
            ->get();

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
    private function getSubmissionsByType($participantId, string $type, MartProject $martProject)
    {
        $entries = MartEntry::forParticipant($participantId)
            ->join('mart_schedules', 'mart_entries.schedule_id', '=', 'mart_schedules.id')
            ->where('mart_schedules.mart_project_id', $martProject->id)
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
    private function getLastDataDonationSubmit($participantId, MartProject $martProject)
    {
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
     *
     * Asks for the newest timestamp only: a stats row can hold megabytes of
     * usage and event data, and MySQL refuses to sort rows that outgrow its
     * sort buffer ("Out of sort memory").
     */
    private function getLastAndroidStatsSubmit($participantId, MartProject $martProject)
    {
        $timestamp = MartStat::forParticipant($participantId)
            ->where('mart_project_id', $martProject->id)
            ->whereNotNull('android_usage_stats')
            ->max('timestamp');

        return $timestamp !== null ? ['timestamp' => (int) $timestamp] : null;
    }
}
