<?php

namespace App\Http\Controllers\Api\V2;

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

    const MEDIA = 'media';      // Legacy field, maintained for compatibility

    const ENTITY = 'entity';    // New field name

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
        // For V2, we focus only on entity field
        $entityName = $project->entity_name ?? 'entity';
        $useEntity = $project->use_entity ?? true;

        // Send entity as the primary field
        $data[self::ENTITY] = $project->media;

        // Pass the entity configuration in the response
        $data['entityName'] = $entityName;
        $data['useEntity'] = $useEntity;

        return response()->json($data, 200);
    }

    /**
     * Format the response for login in V2 format
     *
     * @return mixed
     */
    protected function formatLoginResponse($response)
    {
        // V2 uses 'entity' as the primary field name
        $entityName = $response->project->entity_name ?? 'entity';
        $useEntity = $response->project->use_entity ?? true;

        // Prepare entity data
        $data[self::INPUTS][self::ENTITY] = $response->project->media;
        $nullItem = (object) ['id' => 0, 'name' => ''];
        $data[self::INPUTS][self::ENTITY]->prepend($nullItem);

        // Set entity configuration
        $data[self::INPUTS]['entityName'] = $entityName;
        $data[self::INPUTS]['useEntity'] = $useEntity;

        $data[self::INPUTS][self::CUSTOMINPUTS] = $response->project->inputs;

        return $data;
    }
}
