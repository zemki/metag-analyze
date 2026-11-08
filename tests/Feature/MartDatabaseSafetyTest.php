<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The MART base migration used to drop every MART table in up(), so
 * `php artisan migrate --database=mart` erased a populated MART database.
 *
 * `mart` points at an in-memory SQLite database here. MySQL can't roll DDL
 * back, so on the real connection a failing run would wipe the MART test
 * database, and every later test with it.
 */
class MartDatabaseSafetyTest extends TestCase
{
    private const BASE_TABLES = [
        'mart_projects',
        'mart_schedules',
        'mart_questions',
        'mart_question_history',
        'mart_pages',
        'mart_entries',
        'mart_answers',
        'mart_stats',
        'mart_device_info',
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

    private function runBaseMigration(): void
    {
        $migration = require database_path('migrations/mart/2025_10_01_000001_create_mart_base_schema.php');

        $migration->up();
    }

    private function bootInProduction(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        (new AppServiceProvider($this->app))->boot();
    }

    /** An in-memory connection, so an unguarded wipe can't reach a real database. */
    private function wipeThrowawayDatabase()
    {
        config(['database.connections.wipe_probe' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);

        return $this->artisan('db:wipe', ['--database' => 'wipe_probe', '--force' => true]);
    }

    /** @test */
    public function it_creates_every_table_on_an_empty_database()
    {
        $this->runBaseMigration();

        foreach (self::BASE_TABLES as $table) {
            $this->assertTrue(Schema::connection('mart')->hasTable($table), "{$table} was not created.");
        }
    }

    /** @test */
    public function it_keeps_existing_rows_when_it_runs_again()
    {
        $this->runBaseMigration();
        DB::connection('mart')->table('mart_projects')->insert(['main_project_id' => 1]);

        $this->runBaseMigration();

        $this->assertSame(1, DB::connection('mart')->table('mart_projects')->count());
    }

    /** @test */
    public function it_recreates_none_of_the_existing_tables()
    {
        $this->runBaseMigration();
        foreach (self::BASE_TABLES as $table) {
            Schema::connection('mart')->table($table, fn (Blueprint $blueprint) => $blueprint->string('sentinel')->nullable());
        }

        $this->runBaseMigration();

        foreach (self::BASE_TABLES as $table) {
            $this->assertTrue(Schema::connection('mart')->hasColumn($table, 'sentinel'), "{$table} was recreated.");
        }
    }

    /** @test */
    public function it_creates_only_the_missing_tables_when_the_schema_is_partial()
    {
        Schema::connection('mart')->create('mart_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('main_project_id');
            $table->timestamps();
        });
        DB::connection('mart')->table('mart_projects')->insert(['main_project_id' => 1]);

        $this->runBaseMigration();

        $this->assertSame(1, DB::connection('mart')->table('mart_projects')->count());
        $this->assertTrue(Schema::connection('mart')->hasTable('mart_device_info'));
    }

    /** @test */
    public function destructive_commands_are_refused_in_production()
    {
        $this->bootInProduction();

        $this->wipeThrowawayDatabase()->assertFailed();
    }

    /** @test */
    public function destructive_commands_stay_available_outside_production()
    {
        $this->wipeThrowawayDatabase()->assertSuccessful();
    }
}
