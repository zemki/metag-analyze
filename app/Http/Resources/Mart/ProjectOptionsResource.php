<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectOptionsResource extends JsonResource
{
    public function toArray($request)
    {
        // Get date info from case if available
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