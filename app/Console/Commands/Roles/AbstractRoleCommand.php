<?php

namespace App\Console\Commands\Roles;

use App\Role;
use App\User;
use Illuminate\Console\Command;

/**
 * Shared plumbing for the roleadd:* and roleremove:* commands.
 *
 * Roles in this application are rows in `user_roles`, not configuration. They
 * decide real access:
 *
 *   admin       web dashboard (Authorised) and the Filament panel at /admin
 *   researcher  web dashboard only
 *   user        participant - mobile app only, no dashboard
 *
 * Authorised passes for admin OR researcher, so a user holding neither cannot
 * reach the dashboard at all.
 */
abstract class AbstractRoleCommand extends Command
{
    /**
     * The role this command operates on: admin, researcher or user.
     */
    protected string $role;

    /**
     * Look up the target account, reporting clearly when it does not exist.
     */
    protected function resolveUser(string $email): ?User
    {
        $email = trim($email);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Not a valid email address: {$email}");

            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email '{$email}'.");

            return null;
        }

        return $user;
    }

    /**
     * Resolve the canonical row for this role.
     *
     * Always the lowest id with that name. The roles table can contain more
     * than one row per name - RolesTableSeeder inserts with a bare insert(), so
     * running it twice duplicates the whole set - and the lowest id is the one
     * existing user_roles rows point at. Picking it deterministically means
     * repeated runs agree with each other and with the existing data.
     */
    protected function resolveRole(): ?Role
    {
        $matches = Role::where('name', $this->role)->orderBy('id')->get();

        if ($matches->isEmpty()) {
            $this->error("The '{$this->role}' role does not exist in the roles table.");
            $this->line('Seed it first:  php artisan db:seed --force --class=\'\RolesTableSeeder\'');

            return null;
        }

        if ($matches->count() > 1) {
            $this->warn(sprintf(
                "The roles table contains %d rows named '%s' (ids: %s).",
                $matches->count(),
                $this->role,
                $matches->pluck('id')->implode(', ')
            ));
            $this->line('  Using id ' . $matches->first()->id . ' (the lowest, which existing assignments reference).');
            $this->line('  Duplicates come from running RolesTableSeeder more than once; worth cleaning up.');
        }

        return $matches->first();
    }

    /**
     * Current role names, for echoing back after a change.
     */
    protected function currentRoleNames(User $user): array
    {
        return $user->roles()->orderBy('roles.id')->pluck('roles.name')->all();
    }

    protected function showRoles(User $user): void
    {
        $names = $this->currentRoleNames($user);

        $this->line('  Roles now: ' . (empty($names) ? '(none)' : implode(', ', $names)));
    }
}
