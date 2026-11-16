<?php

namespace App\Console\Commands\Roles;

use App\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Collapses duplicate rows in the roles table onto one canonical row per name.
 *
 * RolesTableSeeder inserts with a bare insert(), so running it twice creates a
 * second full set of admin/researcher/user. Nothing breaks outright - the role
 * checks all compare names, not ids - but "the admin role" stops having a
 * single answer, and code that hard-codes an id (UserRoleTableSeeder and
 * PlaywrightUserSeeder both use role_id 1) silently depends on insertion order.
 *
 * The lowest id per name wins, because that is the row existing assignments
 * already point at.
 */
class RoleCleanupCommand extends Command
{
    protected $signature = 'role:cleanup
                            {--dry-run : Show what would change without touching anything}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Merge duplicate role rows onto one canonical row per role name';

    public function handle(): int
    {
        $duplicateNames = Role::query()
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        if ($duplicateNames->isEmpty()) {
            $this->info('No duplicate roles. Nothing to clean up.');

            return self::SUCCESS;
        }

        // Work out the full plan first so it can be shown before anything is
        // written, and so --dry-run and the real run cannot disagree.
        $plan = $this->buildPlan($duplicateNames);

        $this->renderPlan($plan);

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('Dry run - nothing was changed.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apply this cleanup?', false)) {
            $this->line('Cancelled. Nothing changed.');

            return self::SUCCESS;
        }

        $moved = 0;
        $merged = 0;
        $deleted = 0;

        // One transaction: a partial cleanup would leave user_roles pointing at
        // role rows that have already been deleted, which the foreign key on
        // user_roles.role_id would reject anyway.
        DB::transaction(function () use ($plan, &$moved, &$merged, &$deleted) {
            foreach ($plan as $entry) {
                foreach ($entry['duplicates'] as $duplicate) {
                    foreach ($duplicate['assignments'] as $assignment) {
                        if ($assignment['already_has_canonical']) {
                            // Re-pointing would create a second identical
                            // (user_id, role_id) pair, so drop this one instead.
                            DB::table('user_roles')->where('id', $assignment['pivot_id'])->delete();
                            $merged++;
                        } else {
                            DB::table('user_roles')
                                ->where('id', $assignment['pivot_id'])
                                ->update(['role_id' => $entry['canonical_id'], 'updated_at' => now()]);
                            $moved++;
                        }
                    }

                    // Safe now: nothing references it.
                    DB::table('roles')->where('id', $duplicate['id'])->delete();
                    $deleted++;
                }
            }
        });

        $this->newLine();
        $this->info('Cleanup complete.');
        $this->line("  assignments re-pointed : {$moved}");
        $this->line("  redundant assignments removed : {$merged}");
        $this->line("  duplicate role rows deleted : {$deleted}");

        $this->newLine();
        $this->line('Roles now:');
        foreach (Role::orderBy('id')->get() as $role) {
            $this->line('  ' . $role->id . ' => ' . $role->name);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildPlan($duplicateNames): array
    {
        $plan = [];

        foreach ($duplicateNames as $name) {
            $rows = Role::where('name', $name)->orderBy('id')->get();
            $canonical = $rows->first();

            $duplicates = [];

            foreach ($rows->slice(1) as $duplicate) {
                $assignments = [];

                foreach (DB::table('user_roles')->where('role_id', $duplicate->id)->get() as $pivot) {
                    $assignments[] = [
                        'pivot_id' => $pivot->id,
                        'user_id' => $pivot->user_id,
                        'already_has_canonical' => DB::table('user_roles')
                            ->where('user_id', $pivot->user_id)
                            ->where('role_id', $canonical->id)
                            ->exists(),
                    ];
                }

                $duplicates[] = [
                    'id' => $duplicate->id,
                    'assignments' => $assignments,
                ];
            }

            $plan[] = [
                'name' => $name,
                'canonical_id' => $canonical->id,
                'duplicates' => $duplicates,
            ];
        }

        return $plan;
    }

    protected function renderPlan(array $plan): void
    {
        $this->warn('Duplicate roles found.');
        $this->newLine();

        foreach ($plan as $entry) {
            $duplicateIds = array_column($entry['duplicates'], 'id');

            $this->line("Role '{$entry['name']}'");
            $this->line('  keep   : id ' . $entry['canonical_id']);
            $this->line('  delete : id ' . implode(', ', $duplicateIds));

            $toMove = 0;
            $toMerge = 0;

            foreach ($entry['duplicates'] as $duplicate) {
                foreach ($duplicate['assignments'] as $assignment) {
                    $assignment['already_has_canonical'] ? $toMerge++ : $toMove++;
                }
            }

            if ($toMove === 0 && $toMerge === 0) {
                $this->line('  no user assignments point at the duplicates');
            } else {
                if ($toMove > 0) {
                    $this->line("  re-point {$toMove} user assignment(s) to id {$entry['canonical_id']}");
                }
                if ($toMerge > 0) {
                    $this->line("  drop {$toMerge} assignment(s) whose user already holds id {$entry['canonical_id']}");
                }
            }

            $this->newLine();
        }
    }
}
