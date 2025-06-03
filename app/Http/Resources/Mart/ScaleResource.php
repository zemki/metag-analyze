<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class ScaleResource extends JsonResource
{
    protected $index;
    
    public function __construct($resource, $index = 0)
    {
        parent::__construct($resource);
        $this->index = $index;
    }
    
    public function toArray($request)
    {
        $scaleOptions = [
            'type' => 'textfield'
        ];
        
        // Convert input type to scale type
        if ($this->type === 'scale') {
            $scaleOptions = [
                'type' => 'number',
                'maxValue' => 5,
                'minValue' => 1
            ];
        } elseif ($this->type === 'one choice') {
            $options = [];
            foreach ($this->answers as $key => $answer) {
                if (!empty($answer)) {
                    $options[] = [
                        'value' => $key,
                        'text' => $answer
                    ];
                }
            }
            
            $scaleOptions = [
                'type' => 'radio',
                'radioOptions' => $options
            ];
        } elseif ($this->type === 'multiple choice') {
            $options = [];
            foreach ($this->answers as $key => $answer) {
                if (!empty($answer)) {
                    $options[] = [
                        'value' => $key,
                        'text' => $answer
                    ];
                }
            }
            
            $scaleOptions = [
                'type' => 'checkbox',
                'checkboxOptions' => $options
            ];
        } elseif ($this->type === 'text') {
            $scaleOptions = [
                'type' => 'textfield'
            ];
        }
        
        return [
            'projectId' => $this->projectId ?? 0,
            'scaleId' => $this->index + 1,
            'name' => $this->name ?? '',
            'options' => $scaleOptions
        ];
    }
}