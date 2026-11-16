<?php

namespace App\Console\Commands\Roles;

class RoleRemoveResearcherCommand extends AbstractRemoveRoleCommand
{
    protected $signature = 'roleremove:researcher
                            {email : Email address of the user}
                            {--force : Skip the confirmation when this is the user\'s last role}';

    protected $description = 'Revoke the researcher role (web dashboard access) from a user';

    protected string $role = 'researcher';
}
