<?php

namespace App\Policies;

use App\Cases;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntryPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Cases $cases)
    {
        // if case is over and user created the project, or is a collaborator (invited)
        // or the request comes from a mobile phone and the case is NOT over.
        return ($cases->isConsultable() && ($user->is($cases->project->created_by()) || $cases->project->invited->contains($user))) || (! $cases->isConsultable() && $user->is($cases->user));
    }
}
