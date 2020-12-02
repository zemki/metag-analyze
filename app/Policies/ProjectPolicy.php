<?php

namespace App\Policies;

use App\Project;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Project $project)
    {
        return $user->is($project->created_by()) || in_array($project->id, $user->invites()->pluck('project_id')->toArray()) || $user->isAdmin();
    }
}
