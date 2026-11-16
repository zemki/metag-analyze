<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * MART migrations live in database/migrations/mart/ and keep their history in
 * the MART database's own `migrations` table, run by `migrate:mart`.
 *
 * `mart` points at an in-memory SQLite database here, so building and wiping
 * it never reaches a real one. Rows these tests put in the main `migrations`
 * table are rolled back with the rest of the test.
 */
class MartMigrationsTest extends TestCase
{
    private const MART_MIGRATIONS = [
        '2025_10_01_000001_create_mart_base_schema',
        '2025_11_10_151648_add_success_page_flag_to_mart_pages',
        '2025_11_10_154056_add_introductory_text_to_mart_schedules',
        '2025_11_25_100000_create_mart_case_schedules_table',
        '2025_11_26_123926_add_image_video_urls_to_mart_questions',
        '2025_11_26_140018_add_show_in_menu_to_mart_pages',
        '2025_11_26_141347_create_mart_files_table',
        '2025_11_26_150816_add_page_type_to_mart_pages',
        '2026_11_06_000001_add_case_id_to_mart_entries',
    ];

    private const MART_TABLES = [
        'mart_projects',
        'mart_schedules',
        'mart_questions',
        'mart_question_history',
        'mart_pages',
        'mart_entries',
        'mart_answers',
        'mart_stats',
        'mart_device_info',
        'mart_case_schedules',
        'mart_files',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.mart' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::purge('mart');
    }

    /** @return string[] */
    private function martMigrationNames(): array
    {
        return self::MART_MIGRATIONS;
    }

    /** What the main `migrations` table holds where MART was migrated before this change. */
    private function recordMartHistoryInMain(array $names): void
    {
        DB::table('migrations')->whereIn('migration', $this->martMigrationNames())->delete();

        foreach ($names as $name) {
            DB::table('migrations')->insert(['migration' => $name, 'batch' => 7]);
        }
    }

    /** MART tables as they stand on a server today: built, with no history of their own. */
    private function buildMartTablesWithoutHistory(): void
    {
        Artisan::call('migrate', ['--database' => 'mart', '--path' => 'database/migrations/mart']);
        Schema::connection('mart')->drop('migrations');
    }

    /** @return string[] */
    private function martHistory(): array
    {
        return DB::connection('mart')->table('migrations')->orderBy('migration')->pluck('migration')->all();
    }

    private function mainHistory(): array
    {
        return DB::table('migrations')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    private function assertMartTablesExist(): void
    {
        foreach (self::MART_TABLES as $table) {
            $this->assertTrue(Schema::connection('mart')->hasTable($table), "{$table} is missing.");
        }
    }

    /** @test */
    public function the_mart_folder_holds_the_nine_mart_migrations()
    {
        $inFolder = collect(File::glob(database_path('migrations/mart/*.php')))
            ->map(fn (string $path) => basename($path, '.php'))
            ->sort()
            ->values()
            ->all();

        $this->assertSame(self::MART_MIGRATIONS, $inFolder);
    }

    /** @test */
    public function no_migration_in_the_main_folder_uses_the_mart_connection()
    {
        foreach (File::glob(database_path('migrations/*.php')) as $file) {
            $this->assertStringNotContainsString("connection('mart')", File::get($file), basename($file));
        }
    }

    /**
     * As on a server where MART never ran: before the move, plain `migrate`
     * would pick these up and stop at the missing database.
     *
     * @test
     */
    public function plain_migrate_works_without_a_mart_database()
    {
        $this->recordMartHistoryInMain([]);
        config(['database.connections.mart' => array_merge(config('database.connections.mysql'), ['database' => 'metag_mart_does_not_exist'])]);
        DB::purge('mart');

        $this->assertSame(0, Artisan::call('migrate', ['--force' => true]));
    }

    /**
     * The legacy main-DB `mart_*` migrations stay in the main folder: they
     * build tables in the main database.
     *
     * @test
     */
    public function plain_migrate_status_lists_none_of_the_mart_migrations()
    {
        Artisan::call('migrate:status');

        $output = Artisan::output();
        foreach (self::MART_MIGRATIONS as $name) {
            $this->assertStringNotContainsString($name, $output);
        }
    }

    /** @test */
    public function an_empty_mart_database_is_built_with_its_own_history()
    {
        $this->assertSame(0, Artisan::call('migrate:mart'));

        $this->assertMartTablesExist();
        $this->assertSame($this->martMigrationNames(), $this->martHistory());
    }

    /** @test */
    public function building_the_mart_database_writes_nothing_to_main()
    {
        $before = $this->mainHistory();
        $main = DB::connection('mysql');
        $main->flushQueryLog();
        $main->enableQueryLog();

        Artisan::call('migrate:mart');

        $writes = array_filter(
            array_column($main->getQueryLog(), 'query'),
            fn (string $query) => ! str_starts_with(strtolower(ltrim($query)), 'select')
        );
        $this->assertSame([], array_values($writes));
        $this->assertSame($before, $this->mainHistory());
    }

    /** @test */
    public function the_first_run_on_existing_tables_copies_the_history()
    {
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain($this->martMigrationNames());

        Artisan::call('migrate:mart');

        $this->assertSame($this->martMigrationNames(), $this->martHistory());
    }

    /** @test */
    public function the_first_run_on_existing_tables_reruns_nothing()
    {
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain($this->martMigrationNames());

        Artisan::call('migrate:mart');

        $this->assertStringContainsString('Nothing to migrate', Artisan::output());
    }

    /** @test */
    public function the_first_run_keeps_the_batches_main_recorded()
    {
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain($this->martMigrationNames());

        Artisan::call('migrate:mart');

        $this->assertSame([7], DB::connection('mart')->table('migrations')->distinct()->pluck('batch')->all());
    }

    /** @test */
    public function the_first_run_leaves_main_unchanged()
    {
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain($this->martMigrationNames());
        $before = $this->mainHistory();

        Artisan::call('migrate:mart');

        $this->assertSame($before, $this->mainHistory());
    }

    /** @test */
    public function the_first_run_runs_what_main_never_recorded()
    {
        $names = $this->martMigrationNames();
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain(array_slice($names, 0, -1));

        Artisan::call('migrate:mart');

        $this->assertStringContainsString(end($names), Artisan::output());
        $this->assertSame($names, $this->martHistory());
    }

    /** @test */
    public function a_wiped_mart_database_is_rebuilt_even_when_main_says_it_ran()
    {
        $this->recordMartHistoryInMain($this->martMigrationNames());

        Artisan::call('migrate:mart');

        $this->assertMartTablesExist();
    }

    /** @test */
    public function status_before_the_first_run_changes_nothing()
    {
        $this->buildMartTablesWithoutHistory();
        $this->recordMartHistoryInMain($this->martMigrationNames());
        $before = $this->mainHistory();

        $this->assertSame(0, Artisan::call('migrate:mart', ['--status' => true]));

        $this->assertFalse(Schema::connection('mart')->hasTable('migrations'));
        $this->assertSame($before, $this->mainHistory());
    }

    /** @test */
    public function status_on_an_empty_database_changes_nothing()
    {
        $this->assertSame(0, Artisan::call('migrate:mart', ['--status' => true]));

        $this->assertSame([], Schema::connection('mart')->getTableListing());
    }

    /** @test */
    public function status_lists_the_mart_migrations_once_they_have_run()
    {
        Artisan::call('migrate:mart');

        Artisan::call('migrate:mart', ['--status' => true]);

        $output = Artisan::output();
        foreach ($this->martMigrationNames() as $name) {
            $this->assertStringContainsString($name, $output);
        }
    }

    /** @test */
    public function install_runs_the_mart_migrations_too()
    {
        $this->artisan('metag:install')
            ->expectsQuestion('Administrator email', 'install-admin@example.com')
            ->expectsQuestion('Password (minimum 6 characters)', 'secret123')
            ->expectsQuestion('Confirm password', 'secret123')
            ->assertSuccessful()
            ->run();

        $this->assertSame($this->martMigrationNames(), $this->martHistory());
    }
}
