<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class MartStructureResource extends JsonResource
{
    protected $schedules;

    public function __construct($resource, $schedules = null)
    {
        parent::__construct($resource);
        $this->schedules = $schedules;
    }

    public function toArray($request)
    {
        $project = $this->resource;
        $inputs = json_decode($project->inputs, true);

        // Check if this is a MART project
        $isMartProject = false;
        $martConfig = null;
        $questions = [];

        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                if (isset($input['type']) && $input['type'] === 'mart') {
                    $isMartProject = true;
                    $martConfig = $input;
                } else {
                    // This is a question/input
                    $questions[] = $input;
                }
            }
        }

        if ($isMartProject && $martConfig) {
            // Handle MART project
            $questionSheet = new QuestionSheetResource($project, $questions, $martConfig);

            // Create scales from MART questions
            $scales = [];
            foreach ($questions as $index => $question) {
                $question['projectId'] = $project->id;
                $scales[] = new ScaleResource((object) $question, $index, true); // true = isMartProject
            }

            // Get pages for MART project
            $pages = $project->pages()->orderBy('sort_order')->get();
            $pageResources = $pages->map(function ($page) {
                return new MartPageResource($page);
            });

            return [
                'projectOptions' => new ProjectOptionsResource($project, $martConfig, $this->schedules),
                'questionnaires' => [$questionSheet],
                'scales' => $scales,
                'pages' => $pageResources,
            ];
        } else {
            // Handle standard MetaG project (backward compatibility)
            $questionSheet = new QuestionSheetResource($project, $inputs);

            // Create scales from inputs
            $scales = [];
            if (is_array($inputs)) {
                foreach ($inputs as $index => $input) {
                    $input['projectId'] = $project->id;
                    $scales[] = new ScaleResource((object) $input, $index, false); // false = not MART project
                }
            }

            return [
                'projectOptions' => new ProjectOptionsResource($project, null, $this->schedules),
                'questionnaires' => [$questionSheet],
                'scales' => $scales,
                'pages' => [], // Standard projects don't have pages
            ];
        }
    }
}
