<?php

namespace App\Http\Resources\Mart;

use App\Mart\MartFile;
use App\Mart\MartQuestion;
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
        // Per martTypes.ts, Scale only has scaleId and options (no projectId)
        return [
            'scaleId' => $this->index + 1,
            'options' => $this->scaleOptions(),
        ];
    }

    /**
     * The scale's options block. MART scale ids are assigned across the whole
     * payload in MartStructureResource, which needs this without an id.
     *
     * MART choice values start at 1, because the app drops a radio value of 0.
     * config keeps 0-based keys, so each value, default and jump adds 1 here.
     */
    public function scaleOptions(): array
    {
        $scaleOptions = [
            'type' => 'text',
        ];

        // Check if this is a MartQuestion model from MART DB
        if ($this->isMartProject && isset($this->config)) {
            // Handle MartQuestion from MART database
            $config = is_array($this->config) ? $this->config : json_decode($this->config, true);
            $questionType = $this->martScaleType($config);

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
                                'value' => $key + 1,
                                'text' => $text,
                            ];
                        }
                    }
                    $scaleOptions = $this->withOtherOption([
                        'type' => 'radio',
                        'radioOptions' => $options,
                    ], 'radioOptions', $config);
                    break;

                case 'multiple choice':
                    $options = [];
                    if (isset($config['options']) && is_array($config['options'])) {
                        foreach ($config['options'] as $key => $text) {
                            $options[] = [
                                'value' => $key + 1,
                                'text' => $text,
                            ];
                        }
                    }
                    $scaleOptions = $this->withOtherOption([
                        'type' => 'checkbox',
                        'checkboxOptions' => $options,
                    ], 'checkboxOptions', $config);
                    break;

                // A slider with a label on each step. The app shows
                // rangeValueOptions[answer - 1] and slides from the first value
                // to the last, so values run 1..N in order. No "other" option.
                // A blank label is a step too; it's stored as null, because
                // ConvertEmptyStringsToNull runs on the editor's request.
                case 'range values':
                    $options = [];
                    if (isset($config['options']) && is_array($config['options'])) {
                        foreach ($config['options'] as $key => $text) {
                            $options[] = [
                                'value' => $key + 1,
                                'text' => (string) $text,
                            ];
                        }
                    }
                    $scaleOptions = [
                        'type' => 'rangeValues',
                        'rangeValueOptions' => $options,
                    ];
                    break;

                case 'text':
                case 'textarea':
                    $scaleOptions = ['type' => $questionType];
                    if (isset($config['placeholder'])) {
                        $scaleOptions['textOptions'] = [
                            'type' => $questionType,
                            'placeholder' => $config['placeholder'],
                        ];
                    }
                    break;

                // Upload question types. DB stores `photo` / `audio` / `video`;
                // the mobile API contract (martTypes.ts) expects
                // `photoUpload` / `audioUpload` / `videoUpload`, each with its
                // own optional options block. We pass any saved
                // `*UploadOptions` config through; if none is set the mobile
                // app uses its own defaults per the contract.
                case 'photo':
                    $scaleOptions = ['type' => 'photoUpload'];
                    if (! empty($config['photoUploadOptions']) && is_array($config['photoUploadOptions'])) {
                        $scaleOptions['photoUploadOptions'] = $config['photoUploadOptions'];
                    }
                    break;

                case 'audio':
                    $scaleOptions = ['type' => 'audioUpload'];
                    if (! empty($config['audioUploadOptions']) && is_array($config['audioUploadOptions'])) {
                        $scaleOptions['audioUploadOptions'] = $config['audioUploadOptions'];
                    }
                    break;

                case 'video':
                    $scaleOptions = ['type' => 'videoUpload'];
                    if (! empty($config['videoUploadOptions']) && is_array($config['videoUploadOptions'])) {
                        $scaleOptions['videoUploadOptions'] = $config['videoUploadOptions'];
                    }
                    break;

                case 'file':
                    // Any file. The app reads only maxFileSize (MB) and
                    // allowedFormats, which it matches against "." + the
                    // file's lowercased extension and uses as the picker's
                    // accept list. A missing key means no limit there.
                    // martTypes.ts's fileType isn't sent: the app never reads it.
                    $scaleOptions = ['type' => 'fileUpload'];
                    $fileUploadOptions = $this->fileUploadOptions($config['fileUploadOptions'] ?? []);
                    if ($fileUploadOptions) {
                        $scaleOptions['fileUploadOptions'] = $fileUploadOptions;
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

            // A display item gets no default or jump, whatever its config
            // holds: the app copies the default into the answer and jumps on
            // it even without an itemId, so questions would be skipped unseen.
            // Left out here, before MartStructureResource compares scales, so
            // it can still share the scale of a plain choice.
            $isDisplay = $this->type === 'display';

            // The app reads the default from here for every type, range included.
            if (isset($config['defaultValue']) && ! $isDisplay) {
                $isChoice = in_array($this->type, ['one choice', 'multiple choice', 'range values'], true);
                $scaleOptions['defaultValue'] = $isChoice
                    ? (int) $config['defaultValue'] + 1
                    : $config['defaultValue'];
            }

            if (isset($config['timer']) && is_array($config['timer'])) {
                $scaleOptions['timer'] = [
                    'time' => $config['timer']['time'],
                    'showCountdown' => $config['timer']['showCountdown'],
                ];
            }

            // Add jump if present (only for radio/checkbox)
            if (isset($config['jump']) && is_array($config['jump']) && ! $isDisplay) {
                $scaleOptions['jump'] = [
                    // Saved as a string. The app compares it with === to a number.
                    'jumpCondition' => (int) $config['jump']['jumpCondition'] + 1,
                    'jumpOver' => $config['jump']['jumpOver'],
                ];
            }
        }

        return $scaleOptions;
    }

    /**
     * The type whose scale a MART question sends. A display item that shows
     * answer options sends the scale of that choice, the same as an
     * answerable one, so identical options share one scale. The app tells
     * them apart by the item: one without an itemId is greyed out.
     */
    private function martScaleType($config): string
    {
        $type = $this->type ?? 'text';
        if ($type === 'display') {
            return MartQuestion::displaySelectionType($config) ?? $type;
        }

        return $type;
    }

    /**
     * A file question's options as the app matches them: the size as a
     * number, each format lowercase with one leading dot, listed once. Keys
     * without a value are left out.
     */
    private function fileUploadOptions($options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $fileUploadOptions = [];
        if (is_numeric($options['maxFileSize'] ?? null)) {
            $fileUploadOptions['maxFileSize'] = $options['maxFileSize'] + 0;
        }

        $formats = [];
        foreach ((array) ($options['allowedFormats'] ?? []) as $format) {
            $normalised = MartFile::normaliseFileFormat($format);
            if ($normalised !== null) {
                $formats[] = $normalised;
            }
        }
        if ($formats) {
            $fileUploadOptions['allowedFormats'] = array_values(array_unique($formats));
        }

        return $fileUploadOptions;
    }

    /**
     * An "other" answer is a normal option with the value "text", plus
     * textOptions saying which field to show. The value stays "text" for a
     * multi-line field too: the app only enables a checkbox's field for "text".
     */
    private function withOtherOption(array $scaleOptions, string $optionsKey, array $config): array
    {
        if (empty($config['includeOtherOption'])) {
            return $scaleOptions;
        }

        $scaleOptions[$optionsKey][] = [
            'value' => 'text',
            'text' => ! empty($config['otherOptionLabel']) ? $config['otherOptionLabel'] : 'Other',
        ];
        $scaleOptions['textOptions'] = ['type' => $config['otherTextType'] ?? 'text'];

        return $scaleOptions;
    }
}
