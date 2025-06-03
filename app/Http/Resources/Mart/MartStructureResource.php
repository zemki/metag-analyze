<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class MartStructureResource extends JsonResource
{
    public function toArray($request)
    {
        $project = $this->resource;
        
        // Create question sheets
        $questionSheet = new QuestionSheetResource($project);
        
        // Create scales from inputs
        $scales = [];
        $inputs = json_decode($project->inputs, true);
        if (is_array($inputs)) {
            foreach ($inputs as $index => $input) {
                // Add project ID to input
                $input['projectId'] = $project->id;
                $scales[] = new ScaleResource((object)$input, $index);
            }
        }
        
        return [
            'projectOptions' => new ProjectOptionsResource($project),
            'questionSheets' => [$questionSheet],
            'scales' => $scales
        ];
    }
}