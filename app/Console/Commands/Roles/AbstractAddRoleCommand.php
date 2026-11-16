<?php

namespace App\Console\Commands\Roles;

/**
 * Grants a single role. Adds only - existing roles are left alone.
 */
abstract class AbstractAddRoleCommand extends AbstractRoleCommand
{
    public function handle(): int
    {
        $user = $this->resolveUser((string) $this->argument('email'));

        if (! $user) {
            return self::FAILURE;
        }

        $role = $this->resolveRole();

        if (! $role) {
            return self::FAILURE;
        }

        // Already holding it is a no-op, not a failure: re-running the command
        // should be safe in a provisioning script.
        if (in_array($this->role, $this->currentRoleNames($user), true)) {
            $this->info("{$user->email} already has the '{$this->role}' role. Nothing changed.");
            $this->showRoles($user);

            return self::SUCCESS;
        }

        $user->roles()->attach($role->id);

        $user->addAction(
            "Role '{$this->role}' granted to " . $user->email,
            'artisan roleadd:' . $this->role
        );

        $this->info("Granted '{$this->role}' to {$user->email}.");
        $this->showRoles($user->fresh());

        $this->afterGrant($user);

        return self::SUCCESS;
    }

    /**
     * Hook for role-specific follow-up notes.
     */
    protected function afterGrant($user): void
    {
        //
    }
}
