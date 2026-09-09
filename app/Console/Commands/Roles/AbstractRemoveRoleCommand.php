<?php

namespace App\Console\Commands\Roles;

/**
 * Revokes a single role, guarding the case where it is the user's last one.
 */
abstract class AbstractRemoveRoleCommand extends AbstractRoleCommand
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

        $current = $this->currentRoleNames($user);

        if (! in_array($this->role, $current, true)) {
            $this->info("{$user->email} does not have the '{$this->role}' role. Nothing changed.");
            $this->showRoles($user);

            return self::SUCCESS;
        }

        // Removing the last role does not merely reduce privileges: the
        // Authorised middleware passes only for admin or researcher, so an
        // account with no roles cannot reach the web dashboard at all.
        if (count($current) === 1 && ! $this->option('force')) {
            $this->warn("'{$this->role}' is the only role {$user->email} has.");
            $this->line('  Removing it leaves the account with no roles, which means it can no');
            $this->line('  longer sign in to the web dashboard (Authorised requires admin or');
            $this->line('  researcher). The account itself is not deleted.');

            if (! $this->confirm('Remove it anyway?', false)) {
                $this->line('Cancelled. Nothing changed.');

                return self::SUCCESS;
            }
        }

        // detach() with an explicit id, so duplicate role rows sharing this
        // name are not all stripped at once.
        $user->roles()->detach($role->id);

        $user->addAction(
            "Role '{$this->role}' revoked from " . $user->email,
            'artisan roleremove:' . $this->role
        );

        $this->info("Removed '{$this->role}' from {$user->email}.");
        $this->showRoles($user->fresh());

        if (empty($this->currentRoleNames($user->fresh()))) {
            $this->warn('This account now has no roles and cannot use the web dashboard.');
        }

        return self::SUCCESS;
    }
}
