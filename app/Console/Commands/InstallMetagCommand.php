<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallMetagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metag:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Metag Analyze: migrate, ensure the base roles exist, and create the first administrator';

    /**
     * The roles the application cannot function without.
     *
     * Keyed by name because names are the stable identifier - role ids are not.
     * RolesTableSeeder inserts with a bare insert(), so running it twice
     * duplicates the whole set and shifts every id.
     *
     * @var array<string, string>
     */
    protected array $baseRoles = [
        'admin' => 'user with all privileges',
        'researcher' => 'create projects and monitor them',
        'user' => 'user of the app and data provider',
    ];

    /**
     * Execute the console command.
     *
     * Safe to run more than once: migrations are idempotent, roles are only
     * created when missing, and an existing administrator has their password
     * reset rather than being duplicated.
     */
    public function handle(): int
    {
        $this->warn('--- Migrating the database ---');
        // --force is required: under APP_ENV=production Laravel's
        // ConfirmableTrait prompts for confirmation and, with no TTY, cancels
        // the command outright - migrations would silently not run.
        Artisan::call('migrate', ['--force' => true], $this->getOutput());

        $this->warn('--- Clearing caches ---');
        Artisan::call('optimize:clear', [], $this->getOutput());

        $this->warn('--- Checking base roles ---');
        if (! $this->ensureRoles()) {
            return self::FAILURE;
        }

        $this->warn('--- Creating the first administrator ---');

        return $this->createAdministrator();
    }

    /**
     * Create any missing base role, leaving existing ones untouched.
     */
    protected function ensureRoles(): bool
    {
        $duplicates = Role::query()
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        if ($duplicates->isNotEmpty()) {
            $this->error('The roles table contains duplicate names: ' . $duplicates->implode(', '));
            $this->line('That happens when RolesTableSeeder is run more than once. Resolve the');
            $this->line('duplicates first - assigning a role by name is ambiguous until you do.');
            $this->line('See DOCS/DEPLOYMENT.md, "Roles and the first administrator".');

            return false;
        }

        foreach ($this->baseRoles as $name => $description) {
            $role = Role::firstOrCreate(['name' => $name], ['description' => $description]);

            $this->line($role->wasRecentlyCreated
                ? "  created role '{$name}'"
                : "  role '{$name}' already present");
        }

        return true;
    }

    /**
     * Prompt for credentials and create or update the administrator account.
     */
    protected function createAdministrator(): int
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            $this->error('No admin role found even after seeding. Aborting.');

            return self::FAILURE;
        }

        $email = null;
        $password = null;

        while ($email === null) {
            $candidate = trim((string) $this->ask('Administrator email'));

            if (! filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                $this->warn('That is not a valid email address.');

                continue;
            }

            $email = $candidate;
        }

        while ($password === null) {
            // secret() so the password is never echoed to the terminal, and it
            // is deliberately never printed back in the summary below.
            $candidate = (string) $this->secret('Password (minimum 6 characters)');

            if (strlen($candidate) < 6) {
                $this->warn('Password must be at least 6 characters.');

                continue;
            }

            if ($candidate !== (string) $this->secret('Confirm password')) {
                $this->warn('The passwords did not match.');

                continue;
            }

            $password = $candidate;
        }

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->password = bcrypt($password);

        // Assigned explicitly rather than mass-assigned: email_verified_at is
        // absent from User::$fillable, so passing it to create()/fill() is
        // silently discarded and the account is then blocked by the 'verified'
        // middleware on every web route.
        $user->email_verified_at = now();

        if (! $existed) {
            $user->password_token = bcrypt(Helper::random_str(60));
        }

        $user->save();

        // syncWithoutDetaching, not sync: re-running must not strip any other
        // role the account has picked up.
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        // NotificationChecker reads $case->user->profile->last_notification_at,
        // so an administrator without a profile row would break notification
        // scheduling for any case they own. addProfile() is the model's own
        // helper for this.
        if (! $user->profile()->exists()) {
            $user->addProfile($user);
        }

        $this->newLine();
        $this->info($existed
            ? "Existing account {$email} updated: password reset, admin role ensured."
            : "Administrator {$email} created.");
        $this->line('Sign in at ' . rtrim(config('app.url'), '/') . '/login');
        $this->line('The admin panel is at ' . rtrim(config('app.url'), '/') . '/admin');

        return self::SUCCESS;
    }
}
