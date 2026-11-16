<?php

namespace App\Console\Commands\Roles;

class RoleRemoveAdminCommand extends AbstractRemoveRoleCommand
{
    protected $signature = 'roleremove:admin
                            {email : Email address of the user}
                            {--force : Skip the confirmation when this is the user\'s last role}';

    protected $description = 'Revoke the admin role (web dashboard + the /admin panel) from a user';

    protected string $role = 'admin';
}
