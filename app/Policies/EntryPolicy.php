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
        return ($case->isConsultable() && ($user->is($cases->project->created_by) || $user->is($c)) || $user->is($cases->user);
    }
}
