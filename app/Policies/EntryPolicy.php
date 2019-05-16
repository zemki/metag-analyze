<?php

namespace App\Policies;

use App\User;
use App\Entry;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntryPolicy
{
	use HandlesAuthorization;

	public function update(User $user,\App\Cases $c)
	{

		return $user->is($c->user);
	}
}
