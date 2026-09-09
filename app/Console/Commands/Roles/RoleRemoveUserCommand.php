<?php

namespace App\Console\Commands\Roles;

class RoleRemoveUserCommand extends AbstractRemoveRoleCommand
{
    protected $signature = 'roleremove:user
                            {email : Email address of the user}
                            {--force : Skip the confirmation when this is the user\'s last role}';

    protected $description = 'Revoke the participant role (mobile app only) from a user';

    protected string $role = 'user';
}
