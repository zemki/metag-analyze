<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionSheetResource extends JsonResource
{
    public function toArray($request)
    {
        $items = [];
        $inputs = json_decode($this->inputs, true);
        
        if (is_array($inputs)) {
            foreach ($inputs as $index => $input) {
                $items[] = [
                    'itemId' => $index + 1,
                    'scaleId' => $index + 1,
                    'text' => $input['name'] ?? '',
                    'options' => [
                        'randomizationGroup' => 1
                    ]
                ];
            }
        }
        
        return [
            'projectId' => $this->id,
            'sheetId' => 1,
            'name' => $this->name . ' Questions',
            'items' => $items
        ];
    }
}