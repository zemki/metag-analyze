<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class ScaleResource extends JsonResource
{
    protected $index;

    protected $isMartProject;

    public function __construct($resource, $index = 0, $isMartProject = false)
    {
        parent::__construct($resource);
        $this->index = $index;
        $this->isMartProject = $isMartProject;
    }

    public function toArray($request)
    {
        $scaleOptions = [
            'type' => 'text',
        ];

        // Check if this is a MartQuestion model from MART DB
        if ($this->isMartProject && isset($this->config)) {
            // Handle MartQuestion from MART database
            $questionType = $this->type ?? 'text';
            $config = is_array($this->config) ? $this->config : json_decode($this->config, true);

            switch ($questionType) {
                case 'scale':
                    $scaleOptions = [
                        'type' => 'number',
                        'minValue' => $config['minValue'] ?? 1,
                        'maxValue' => $config['maxValue'] ?? 10,
                    ];
                    break;

                case 'one choice':
                    $options = [];
                    if (isset($config['options']) && is_array($config['options'])) {
                        foreach ($config['options'] as $key => $text) {
                            $options[] = [
                                'value' => $key,
                                'text' => $text,
                            ];
                        }
                    }
                    $scaleOptions = [
                        'type' => 'radio',
                        'radioOptions' => $options,
                    ];
                    break;

                case 'multiple choice':
                    $options = [];
                    if (isset($config['options']) && is_array($config['options'])) {
                        foreach ($config['options'] as $key => $text) {
                            $options[] = [
                                'value' => $key,
                                'text' => $text,
                            ];
                        }
                    }
                    $scaleOptions = [
                        'type' => 'checkbox',
                        'checkboxOptions' => $options,
                    ];
                    break;

                case 'text':
                    $scaleOptions = ['type' => 'text'];
                    break;
            }
        } elseif ($this->isMartProject && isset($this->martMetadata)) {
            // Handle MART project with native MART types (old structure)
            $martType = $this->martMetadata['originalType'];

            switch ($martType) {
                case 'text':
                    $scaleOptions = ['type' => 'text'];
                    break;

                case 'textarea':
                    $scaleOptions = ['type' => 'textarea'];
                    break;

                case 'number':
                    $scaleOptions = [
                        'type' => 'number',
                        'minValue' => $this->martMetadata['minValue'] ?? 1,
                        'maxValue' => $this->martMetadata['maxValue'] ?? 10,
                    ];
                    break;

                case 'range':
                    $scaleOptions = [
                        'type' => 'range',
                        'rangeOptions' => [
                            'minValue' => $this->martMetadata['minValue'] ?? 1,
                            'maxValue' => $this->martMetadata['maxValue'] ?? 10,
                            'steps' => $this->martMetadata['steps'] ?? 1,
                            'defaultValue' => $this->martMetadata['defaultValue'] ?? ($this->martMetadata['minValue'] ?? 1),
                        ],
                    ];
                    break;

                case 'radio':
                    $options = [];
                    if (isset($this->answers) && is_array($this->answers)) {
                        foreach ($this->answers as $key => $answer) {
                            if (! empty($answer)) {
                                $options[] = [
                                    'value' => $key,
                                    'text' => $answer,
                                ];
                            }
                        }
                    }
                    $scaleOptions = [
                        'type' => 'radio',
                        'radioOptions' => $options,
                    ];
                    break;

                case 'checkbox':
                    $options = [];
                    if (isset($this->answers) && is_array($this->answers)) {
                        foreach ($this->answers as $key => $answer) {
                            if (! empty($answer)) {
                                $options[] = [
                                    'value' => $key,
                                    'text' => $answer,
                                ];
                            }
                        }
                    }
                    $scaleOptions = [
                        'type' => 'checkbox',
                        'checkboxOptions' => $options,
                    ];
                    break;
            }
        } else {
            // Handle standard MetaG project (backward compatibility)
            if ($this->type === 'scale') {
                $scaleOptions = [
                    'type' => 'number',
                    'maxValue' => 5,
                    'minValue' => 1,
                ];
            } elseif ($this->type === 'one choice') {
                $options = [];
                if (isset($this->answers) && is_array($this->answers)) {
                    foreach ($this->answers as $key => $answer) {
                        if (! empty($answer)) {
                            $options[] = [
                                'value' => $key,
                                'text' => $answer,
                            ];
                        }
                    }
                }

                $scaleOptions = [
                    'type' => 'radio',
                    'radioOptions' => $options,
                ];
            } elseif ($this->type === 'multiple choice') {
                $options = [];
                if (isset($this->answers) && is_array($this->answers)) {
                    foreach ($this->answers as $key => $answer) {
                        if (! empty($answer)) {
                            $options[] = [
                                'value' => $key,
                                'text' => $answer,
                            ];
                        }
                    }
                }

                $scaleOptions = [
                    'type' => 'checkbox',
                    'checkboxOptions' => $options,
                ];
            } elseif ($this->type === 'text') {
                $scaleOptions = [
                    'type' => 'text',
                ];
            }
        }

        return [
            'projectId' => $this->projectId ?? 0,
            'scaleId' => $this->index + 1,
            'options' => $scaleOptions,
        ];
    }
}
