<?php

namespace App\Http\Middleware;

use App\Project;
use Carbon\Carbon;
use Closure;

class ProjectDateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $projectId = $request->route('project');

        if ($projectId) {
            $project = Project::find($projectId);

            if ($project && $project->created_at) {
                $cutoffDate = Carbon::parse(config('app.api_v2_cutoff_date', '2025-03-21'));

                if ($project->created_at->lt($cutoffDate)) {
                    // Redirect to v1 API for projects created before cutoff date
                    return redirect(str_replace('/v2/', '/v1/', $request->path()));
                }
            }
        }

        return $next($request);
    }
}
