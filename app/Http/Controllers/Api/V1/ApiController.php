<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    const EMAIL = 'email';

    const PASSWORD = 'password';

    const INPUTS = 'inputs';

    const TOKEN = 'token';

    const CUSTOMINPUTS = 'custominputs';

    const NOTSTARTED = 'notstarted';

    const MEDIA = 'media';

    const ENTITY = 'entity';

    /**
     * @return JsonResponse
     */
    public function getProject(Project $project)
    {
        return response()->json(compact($project), 200);
    }

    /**
     * @return JsonResponse
     */
    public function getInputs(Project $project)
    {
        // In V1, we primarily use 'media' but include 'entity' for forward compatibility
        $data[self::MEDIA] = $project->media;

        // Include entity_name for clients that might be using it
        $data['entityName'] = $project->entity_name ?? 'entity';

        // Include entity with the same data as media for forward compatibility
        $data[self::ENTITY] = $project->media;

        return response()->json($data, 200);
    }

    /**
     * Format the response for login in V1 format
     *
     * @return mixed
     */
    protected function formatLoginResponse($response)
    {
        // V1 primarily uses 'media' but includes entity for forward compatibility
        $entityName = $response->project->entity_name ?? 'entity';

        // Prepare media data (primary field in V1)
        $data[self::INPUTS][self::MEDIA] = $response->project->media;
        $nullItem = (object) ['id' => 0, 'name' => ''];
        $data[self::INPUTS][self::MEDIA]->prepend($nullItem);

        // Include entity with the same data for forward compatibility
        $data[self::INPUTS][self::ENTITY] = $data[self::INPUTS][self::MEDIA];

        // Include entity_name for clients that might be using it
        $data[self::INPUTS]['entityName'] = $entityName;

        $data[self::INPUTS][self::CUSTOMINPUTS] = $response->project->inputs;

        return $data;
    }
}
