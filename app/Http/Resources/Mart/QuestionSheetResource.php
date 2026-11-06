<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionSheetResource extends JsonResource
{
    protected $questions;

    protected $martConfig;

    protected $questionnaireId;

    protected $scheduleName;

    /** MART only: scaleId per item index, assigned in MartStructureResource. */
    protected $itemScaleIds;

    public function __construct($resource, $questions = null, $martConfig = null, $questionnaireId = 1, $scheduleName = null, array $itemScaleIds = [])
    {
        parent::__construct($resource);
        $this->questions = $questions;
        $this->martConfig = $martConfig;
        $this->questionnaireId = $questionnaireId;
        $this->scheduleName = $scheduleName;
        $this->itemScaleIds = $itemScaleIds;
    }

    public function toArray($request)
    {
        $items = [];

        if ($this->questions && $this->martConfig) {
            // MART project - use provided questions
            foreach ($this->questions as $index => $question) {
                $questionType = $question['type'] ?? 'text';
                $isDisplayOnly = $questionType === 'display';

                // The item-level options live in the question's `config` JSON,
                // where the questionnaire editor puts them. Mandatoriness is the
                // exception - it has its own column.
                $config = $question['config'] ?? [];

                $item = [
                    // Per martTypes.ts: itemId/scaleId are null for display-only items (text without question)
                    'itemId' => $isDisplayOnly ? null : $index + 1,
                    'scaleId' => $this->itemScaleIds[$index] ?? null,
                    'text' => $question['text'] ?? $question['name'] ?? '',
                    'options' => [
                        'randomizationGroupId' => $config['randomizationGroupId'] ?? null,
                        'randomizeAnswers' => $config['randomizeAnswers'] ?? false,
                        'randomPositionWithinQuestionnaire' => $config['randomPositionWithinQuestionnaire'] ?? false,
                        // martTypes.ts declares itemGroup as a string; older
                        // data may hold an int, so normalise here.
                        'itemGroup' => isset($config['itemGroup']) ? (string) $config['itemGroup'] : null,
                        // Inverse of is_mandatory. A display item is never
                        // answerable, and the editor can still mark one
                        // mandatory, so that branch stays.
                        'noValueAllowed' => $isDisplayOnly ? true : ! ($question['is_mandatory'] ?? true),
                    ],
                ];

                // Add image/video URLs if present (per martTypes.ts)
                if (!empty($question['image_url'])) {
                    $item['imageUrl'] = $question['image_url'];
                }
                if (!empty($question['video_url'])) {
                    $item['videoUrl'] = $question['video_url'];
                }
                // Shown in place of a video before it starts. A top-level item
                // field per martTypes.ts, stored in config like the options.
                if (!empty($config['posterUrl'])) {
                    $item['posterUrl'] = $config['posterUrl'];
                }

                $items[] = $item;
            }

            // Prefer the schedule's own name (per-questionnaire), fall back to the
            // project-level questionnaireName, then to a generic project-derived label.
            $questionnaireName = $this->scheduleName
                ?? $this->martConfig['questionnaireName']
                ?? ($this->name . ' Questions');
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
                            'randomizationGroupId' => $input['randomizationGroupId'] ?? null,
                            'randomizeAnswers' => $input['randomizeAnswers'] ?? false,
                            'itemGroup' => $input['item_group'] ?? null,
                            'noValueAllowed' => $input['noValueAllowed'] ?? false,
                        ],
                    ];
                }
            }

            $questionnaireName = $this->name . ' Questions';
        }

        // Per martTypes.ts, Questionnaire only has questionnaireId, name, items (no projectId)
        return [
            'questionnaireId' => $this->questionnaireId,
            'name' => $questionnaireName,
            'items' => $items,
        ];
    }
}
