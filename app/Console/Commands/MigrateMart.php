<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Runs the MART migrations on the MART database, with their history in that
 * database's own `migrations` table. Plain `migrate` covers the main database
 * only, so a deploy runs `migrate --force`, then `migrate:mart --force`.
 */
class MigrateMart extends Command
{
    use ConfirmableTrait;

    public const PATH = 'database/migrations/mart';

    protected $signature = 'migrate:mart
        {--force : Run in production without asking}
        {--status : Show which MART migrations have run, changing nothing}';

    protected $description = 'Run the MART migrations, with their history in the MART database';

    public function handle(): int
    {
        $migrator = $this->laravel['migrator'];

        if ($this->option('status')) {
            return $this->showStatus();
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->copyHistoryOnFirstRun($migrator);

        return $this->call('migrate', ['--database' => 'mart', '--path' => self::PATH, '--force' => true]);
    }

    /**
     * MART's history used to sit in the main `migrations` table. On the first
     * run against existing MART tables it is copied here, so nothing runs
     * twice. An empty MART database gets no copy: a wiped one is rebuilt
     * instead of inheriting "Ran" rows. The main database is only read, and
     * its old rows stay there unused.
     */
    private function copyHistoryOnFirstRun(Migrator $migrator): void
    {
        $mart = Schema::connection('mart');

        if ($mart->hasTable('migrations') || ! $mart->hasTable('mart_projects')) {
            return;
        }

        $names = array_keys($migrator->getMigrationFiles([base_path(self::PATH)]));
        $ran = Schema::connection('mysql')->hasTable('migrations')
            ? DB::connection('mysql')->table('migrations')->whereIn('migration', $names)->orderBy('id')->get()
            : collect();

        $repository = $migrator->getRepository();
        $repository->setSource('mart');
        $repository->createRepository();

        foreach ($ran as $row) {
            $repository->log($row->migration, $row->batch);
        }

        $this->components->info("Copied the history of {$ran->count()} MART migrations from the main database.");
    }

    private function showStatus(): int
    {
        $mart = Schema::connection('mart');

        if ($mart->hasTable('migrations')) {
            return $this->call('migrate:status', ['--database' => 'mart', '--path' => self::PATH]);
        }

        $this->components->info($mart->hasTable('mart_projects')
            ? 'No MART migration history yet. The first migrate:mart copies it from the main database.'
            : 'The MART database is empty. The first migrate:mart builds it.');

        return self::SUCCESS;
    }
}
