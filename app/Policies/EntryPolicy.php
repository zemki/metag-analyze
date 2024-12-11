<?php

namespace App\Policies;

use App\Cases;
use App\Entry;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntryPolicy
{
    use HandlesAuthorization;

    public function store(User $user, Cases $case)
    {
        return $user->is($case->user) || $user->is($case->project->created_by()) || $case->project->invited->contains($user);
    }

    public function update(User $user, Entry $entry)
    {
        $cases = Cases::find($entry->case_id);

        // if case is over and user created the project, or is a collaborator (invited)
        // or the request comes from a mobile phone and the case is NOT over.
        return ($cases->isConsultable() && ($user->is($cases->project->created_by()) || $cases->project->invited->contains($user))) || (! $cases->isConsultable() && $user->is($cases->user));
    }
}
