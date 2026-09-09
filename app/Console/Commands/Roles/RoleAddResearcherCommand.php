<?php

namespace App\Console\Commands\Roles;

class RoleAddResearcherCommand extends AbstractAddRoleCommand
{
    protected $signature = 'roleadd:researcher {email : Email address of the user}';

    protected $description = 'Grant the researcher role (web dashboard access) to a user';

    protected string $role = 'researcher';
}
