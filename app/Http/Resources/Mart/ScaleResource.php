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
                case 'number':
                    $scaleOptions = [
                        'type' => 'number',
                        'minValue' => $config['min'] ?? 1,
                        'maxValue' => $config['max'] ?? 10,
                    ];

                    // Add maxDigits if present
                    if (isset($config['maxDigits'])) {
                        $scaleOptions['maxDigits'] = $config['maxDigits'];
                    }
                    break;

                case 'range':
                    $scaleOptions = [
                        'type' => 'range',
                        'rangeOptions' => [
                            'minValue' => $config['min'] ?? 1,
                            'maxValue' => $config['max'] ?? 10,
                            'steps' => $config['step'] ?? 1,
                            'defaultValue' => $config['defaultValue'] ?? ($config['min'] ?? 1),
                        ],
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
                    // Use radioWithText if includeOtherOption is enabled
                    $type = (isset($config['includeOtherOption']) && $config['includeOtherOption']) ? 'radioWithText' : 'radio';
                    $scaleOptions = [
                        'type' => $type,
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
                    // Use checkboxWithText if includeOtherOption is enabled
                    $type = (isset($config['includeOtherOption']) && $config['includeOtherOption']) ? 'checkboxWithText' : 'checkbox';
                    $scaleOptions = [
                        'type' => $type,
                        'checkboxOptions' => $options,
                    ];
                    break;

                case 'text':
                    $scaleOptions = ['type' => 'text'];
                    // Add placeholder if present
                    if (isset($config['placeholder'])) {
                        $scaleOptions['placeholder'] = $config['placeholder'];
                    }
                    break;

                case 'textarea':
                    $scaleOptions = ['type' => 'textarea'];
                    // Add placeholder if present
                    if (isset($config['placeholder'])) {
                        $scaleOptions['placeholder'] = $config['placeholder'];
                    }
                    break;
            }
        } elseif ($this->isMartProject && isset($this->martMetadata)) {
            // Handle MART project with native MART types (old structure)
            $martType = $this->martMetadata['originalType'];

            switch ($martType) {
                case 'text':
                    $scaleOptions = ['type' => 'text'];
                    // Add placeholder if present
                    if (isset($this->martMetadata['placeholder'])) {
                        $scaleOptions['placeholder'] = $this->martMetadata['placeholder'];
                    }
                    break;

                case 'textarea':
                    $scaleOptions = ['type' => 'textarea'];
                    // Add placeholder if present
                    if (isset($this->martMetadata['placeholder'])) {
                        $scaleOptions['placeholder'] = $this->martMetadata['placeholder'];
                    }
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

        // Add timer and jump to $scaleOptions if present (MART projects only)
        // These should be inside the options object per martTypes.ts
        if ($this->isMartProject && isset($this->config)) {
            $config = is_array($this->config) ? $this->config : json_decode($this->config, true);

            if (isset($config['timer']) && is_array($config['timer'])) {
                $scaleOptions['timer'] = [
                    'time' => $config['timer']['time'],
                    'showCountdown' => $config['timer']['showCountdown'],
                ];
            }

            // Add jump if present (only for radio/checkbox)
            if (isset($config['jump']) && is_array($config['jump'])) {
                $scaleOptions['jump'] = [
                    'jumpCondition' => $config['jump']['jumpCondition'],
                    'jumpOver' => $config['jump']['jumpOver'],
                ];
            }
        }

        // Per martTypes.ts, Scale only has scaleId and options (no projectId)
        return [
            'scaleId' => $this->index + 1,
            'options' => $scaleOptions,
        ];
    }
}
