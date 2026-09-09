<?php

namespace App\Console\Commands\Roles;

class RoleAddUserCommand extends AbstractAddRoleCommand
{
    protected $signature = 'roleadd:user {email : Email address of the user}';

    protected $description = 'Grant the participant role (mobile app only) to a user';

    protected string $role = 'user';
}
