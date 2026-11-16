<?php

namespace App\Console\Commands\Roles;

use App\User;

class RoleAddAdminCommand extends AbstractAddRoleCommand
{
    protected $signature = 'roleadd:admin {email : Email address of the user}';

    protected $description = 'Grant the admin role (web dashboard + the /admin panel) to a user';

    protected string $role = 'admin';

    /**
     * ADMINS is a different thing from the admin role and is easy to conflate,
     * so say so at the moment someone is made an admin.
     */
    protected function afterGrant($user): void
    {
        $admins = array_filter(array_map('trim', (array) config('utilities.adminemails', [])));

        if (in_array($user->email, $admins, true)) {
            return;
        }

        $this->newLine();
        $this->line("Note: {$user->email} is not listed in the ADMINS environment variable.");
        $this->line('  ADMINS is separate from this role and is NOT granted by this command.');
        $this->line('  It controls the Telescope gate and who receives the unverified-users');
        $this->line('  report - not access to /admin, which the role you just granted covers.');
        $this->line('  To add it: edit ADMINS in .env, then run php artisan config:cache');
    }
}
