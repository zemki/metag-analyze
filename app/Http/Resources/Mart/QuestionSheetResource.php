<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionSheetResource extends JsonResource
{
    protected $questions;

    protected $martConfig;

    public function __construct($resource, $questions = null, $martConfig = null)
    {
        parent::__construct($resource);
        $this->questions = $questions;
        $this->martConfig = $martConfig;
    }

    public function toArray($request)
    {
        $items = [];

        if ($this->questions && $this->martConfig) {
            // MART project - use provided questions
            foreach ($this->questions as $index => $question) {
                $items[] = [
                    'itemId' => $index + 1,
                    'scaleId' => $index + 1,
                    'text' => $question['name'] ?? '',
                    'options' => [
                        'randomizationGroupId' => 1,
                    ],
                ];
            }

            $questionnaireName = $this->martConfig['questionnaireName'] ?? ($this->name . ' Questions');
        } else {
            // Standard MetaG project - use inputs
            $inputs = $this->questions ?: json_decode($this->inputs, true);

            if (is_array($inputs)) {
                foreach ($inputs as $index => $input) {
                    $items[] = [
                        'itemId' => $index + 1,
                        'scaleId' => $index + 1,
                        'text' => $input['name'] ?? '',
                        'options' => [
                            'randomizationGroupId' => 1,
                        ],
                    ];
                }
            }

            $questionnaireName = $this->name . ' Questions';
        }

        return [
            'projectId' => $this->id,
            'questionnaireId' => 1,
            'name' => $questionnaireName,
            'items' => $items,
        ];
    }
}
