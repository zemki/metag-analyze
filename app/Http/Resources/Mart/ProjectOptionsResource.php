<?php

namespace App\Http\Resources\Mart;

use App\MartQuestionnaireSchedule;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectOptionsResource extends JsonResource
{
    protected $martConfig;
    protected $schedules;
    
    public function __construct($resource, $martConfig = null, $schedules = null)
    {
        parent::__construct($resource);
        $this->martConfig = $martConfig;
        $this->schedules = $schedules;
    }
    
    public function toArray($request)
    {
        // Get questionnaire schedules if not provided
        if ($this->schedules === null) {
            $this->schedules = MartQuestionnaireSchedule::forProject($this->id)->get();
        }
        
        // Separate schedules by type
        $singleQuestionnaires = [];
        $repeatingQuestionnaires = [];
        
        if ($this->schedules) {
            foreach ($this->schedules as $schedule) {
                if ($schedule->isSingle()) {
                    $singleQuestionnaires[] = $schedule->toMobileFormat();
                } else {
                    $repeatingQuestionnaires[] = $schedule->toMobileFormat();
                }
            }
        }
        
        if ($this->martConfig && isset($this->martConfig['projectOptions'])) {
            // MART project - use MART configuration with schedules
            $projectOptions = $this->martConfig['projectOptions'];
            
            // Convert date/time format
            $startDateTime = null;
            $endDateTime = null;
            
            if (isset($projectOptions['startDateAndTime'])) {
                $startDate = $projectOptions['startDateAndTime']['date'];
                $startTime = $projectOptions['startDateAndTime']['time'] ?? '00:00';
                $startDateTime = date('Y-m-d\TH:i:s\Z', strtotime("$startDate $startTime"));
            }
            
            if (isset($projectOptions['endDateAndTime'])) {
                $endDate = $projectOptions['endDateAndTime']['date'];
                $endTime = $projectOptions['endDateAndTime']['time'] ?? '23:59';
                $endDateTime = date('Y-m-d\TH:i:s\Z', strtotime("$endDate $endTime"));
            }
            
            return [
                'projectId' => $this->id,
                'projectName' => $this->name,
                'options' => [
                    'startDateAndTime' => [
                        'date' => $projectOptions['startDateAndTime']['date'] ?? null,
                        'time' => $projectOptions['startDateAndTime']['time'] ?? '00:00'
                    ],
                    'endDateAndTime' => [
                        'date' => $projectOptions['endDateAndTime']['date'] ?? null,
                        'time' => $projectOptions['endDateAndTime']['time'] ?? '23:59'
                    ],
                    'showProgressBar' => $projectOptions['showProgressBar'] ?? true,
                    'showNotifications' => $projectOptions['showNotifications'] ?? true,
                    'notificationText' => $projectOptions['notificationText'] ?? 'You have a new questionnaire available',
                    'notificationConfig' => $projectOptions['notificationConfig'] ?? null,
                    'collectDeviceInfos' => $projectOptions['collectDeviceInfos'] ?? true,
                    'collectIosStats' => $projectOptions['collectIosStats'] ?? false,
                    'collectAndroidStats' => $projectOptions['collectAndroidStats'] ?? false,
                    'initialHoursOfAndroidStats' => $projectOptions['initialHoursOfAndroidStats'] ?? 24,
                    'overlapAndroidStatsHours' => $projectOptions['overlapAndroidStatsHours'] ?? 2,
                    'pages' => $this->pages()->pluck('id')->toArray(),
                    'pagesToShowInMenu' => $this->pages()->where('show_on_first_app_start', false)->pluck('id')->toArray(),
                    'pagesToShowOnFirstAppStart' => $this->pages()->where('show_on_first_app_start', true)->pluck('id')->toArray(),
                    // Add questionnaire schedules
                    'singleQuestionnaires' => $singleQuestionnaires,
                    'repeatingQuestionnaires' => $repeatingQuestionnaires,
                ]
            ];
        } else {
            // Standard MetaG project - backward compatibility
            $startDay = null;
            $endDay = null;
            
            if (isset($this->cases) && $this->cases->count() > 0) {
                $case = $this->cases->first();
                $startDay = $case->startDay();
                $endDay = $case->lastDay();
            }
            
            return [
                'projectId' => $this->id,
                'projectName' => $this->name,
                'options' => [
                    'startDate' => $startDay ? date('Y-m-d\TH:i:s\Z', strtotime($startDay)) : null,
                    'endDate' => $endDay ? date('Y-m-d\TH:i:s\Z', strtotime($endDay)) : null,
                    'startTime' => null,
                    'endTime' => null,
                    'breakBetweenQuestionSheets' => 0,
                    'relatedQuestionSheets' => [
                        [
                            'sheetId' => 1,
                            'type' => 'initial'
                        ]
                    ],
                    'useNotifications' => true
                ]
            ];
        }
    }
}