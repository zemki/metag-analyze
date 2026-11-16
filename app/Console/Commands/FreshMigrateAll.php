<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FreshMigrateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-all {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '🚨 DESTRUCTIVE: Drop ALL tables in BOTH databases (LOCAL ONLY)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // ========================================
        // PRODUCTION SAFETY CHECK
        // ========================================
        if (app()->environment('production')) {
            $this->newLine();
            $this->error('╔═══════════════════════════════════════════════════════════════╗');
            $this->error('║                                                               ║');
            $this->error('║   🚨 BLOCKED: THIS COMMAND CANNOT RUN IN PRODUCTION! 🚨      ║');
            $this->error('║                                                               ║');
            $this->error('║   This command is EXTREMELY DESTRUCTIVE and will delete      ║');
            $this->error('║   ALL data from both MAIN and MART databases.                ║');
            $this->error('║                                                               ║');
            $this->error('║   This command is ONLY for local/staging environments.       ║');
            $this->error('║                                                               ║');
            $this->error('╚═══════════════════════════════════════════════════════════════╝');
            $this->newLine();
            $this->error('Current environment: ' . app()->environment());
            $this->newLine();

            return 1; // Exit with error code
        }

        // ========================================
        // DESTRUCTIVE OPERATION WARNING
        // ========================================
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                               ║');
        $this->error('║         ⚠️  DESTRUCTIVE OPERATION WARNING  ⚠️                 ║');
        $this->line('║                                                               ║');
        $this->line('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->warn('This command will:');
        $this->warn('  ❌ DROP ALL TABLES in the MAIN database');
        $this->warn('  ❌ DROP ALL TABLES in the MART database');
        $this->warn('  ❌ DELETE ALL DATA permanently');
        $this->warn('  ⚠️  This action CANNOT be undone!');
        $this->newLine();

        $this->info('Environment: ' . app()->environment());
        $this->info('MAIN Database: ' . config('database.connections.mysql.database'));
        $this->info('MART Database: ' . config('database.connections.mart.database'));
        $this->newLine();

        // First confirmation
        if (!$this->confirm('⚠️  Do you understand this will DELETE ALL DATA?', false)) {
            $this->info('✅ Operation cancelled. Your data is safe.');
            return 0;
        }

        // Second confirmation (type to confirm)
        $this->newLine();
        $this->warn('⚠️  FINAL CONFIRMATION REQUIRED');
        $confirmation = $this->ask('Type "DELETE ALL DATA" (in capitals) to proceed');

        if ($confirmation !== 'DELETE ALL DATA') {
            $this->info('✅ Operation cancelled. Confirmation did not match.');
            return 0;
        }

        $this->newLine();
        $this->info('🗑️  Resetting BOTH databases...');

        // MART has its own migrations and history: drop its tables by hand,
        // reset main with migrate:fresh, then build MART with migrate:mart.

        $this->info('    1. Manually dropping all MART database tables...');
        $this->dropAllMartTables();

        $this->info('    2. Running migrate:fresh on MAIN...');
        $this->call('migrate:fresh', ['--force' => true]);

        $this->info('    3. Running migrate:mart on MART...');
        $this->call('migrate:mart', ['--force' => true]);

        $this->newLine();
        $this->info('✅ Both databases have been reset!');

        // Run seeders if requested
        if ($this->option('seed')) {
            $this->newLine();
            $this->info('🌱 Running database seeders...');
            $this->call('db:seed');

            $this->newLine();
            $this->info('🎉 Migration and seeding completed!');
        } else {
            $this->newLine();
            $this->comment('💡 Tip: Run with --seed to automatically seed the database');
            $this->comment('   Example: php artisan migrate:fresh-all --seed');
        }

        // Clear caches automatically
        $this->newLine();
        $this->info('🧹 Clearing caches...');
        $this->call('cache:clear');
        $this->call('config:clear');

        // Clear Redis
        try {
            exec('redis-cli FLUSHDB', $output, $returnCode);
            if ($returnCode === 0) {
                $this->info('✅ Redis cache cleared');
            } else {
                $this->warn('⚠️  Could not clear Redis - run manually: redis-cli FLUSHDB');
            }
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not clear Redis - run manually: redis-cli FLUSHDB');
        }

        $this->newLine();
        $this->info('🎉 All done! You can now log in.');

        return 0;
    }

    /**
     * Manually drop all tables in the MART database, its `migrations` table
     * included. migrate:fresh only resets the default connection, and
     * migrate:fresh --database=mart would run every migration on MART.
     */
    protected function dropAllMartTables()
    {
        try {
            $martConnection = \DB::connection('mart');

            // Disable foreign key checks
            $martConnection->statement('SET FOREIGN_KEY_CHECKS=0');

            // Get all table names in MART database
            $tables = $martConnection->select('SHOW TABLES');
            $dbName = $martConnection->getDatabaseName();
            $tableKey = "Tables_in_{$dbName}";

            $tableCount = 0;
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $martConnection->statement("DROP TABLE IF EXISTS `{$tableName}`");
                $tableCount++;
            }

            // Re-enable foreign key checks
            $martConnection->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info("       ✓ Dropped {$tableCount} tables from MART database");
        } catch (\Exception $e) {
            $this->error("       ✗ Error dropping MART tables: " . $e->getMessage());
            throw $e;
        }
    }
}
