<?php

namespace Tests\Feature;

use App\Entry;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * MeTag projects must not reach the MART database, and MART projects must not
 * lose MART failures: a swallowed error used to look like "no MART data".
 */
class MetagMartGateTest extends TestCase
{
    private function assertNoMartQueries(callable $action): void
    {
        $mart = DB::connection('mart');
        $mart->flushQueryLog();
        $mart->enableQueryLog();

        $action();

        $this->assertSame([], array_column($mart->getQueryLog(), 'query'));
    }

    private function makeProjectMart(): void
    {
        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
    }

    /** Points `mart` at an empty database, so every MART query fails. */
    private function breakMartConnection(): void
    {
        config(['database.connections.mart' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('mart');
    }

    /** @test */
    public function metag_works_on_a_server_with_no_mart_database()
    {
        config(['database.connections.mart.database' => 'metag_mart_does_not_exist']);
        DB::purge('mart');

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ])->assertOk();

        $this->actingAs($this->user);
        $this->get('/projects')->assertOk();
        $this->get("/projects/{$this->project->id}")->assertOk();
        $this->getJson("/projects/{$this->project->id}/cases")->assertOk();
    }

    /** @test */
    public function a_metag_project_has_no_mart_project_and_does_not_look_for_one()
    {
        $this->assertNoMartQueries(fn () => $this->assertNull($this->project->martProject()));
    }

    /** @test */
    public function adding_a_user_to_a_metag_case_does_not_query_mart()
    {
        $this->assertNoMartQueries(fn () => $this->case->addUser(User::factory()->create()));
    }

    /** @test */
    public function duplicating_a_metag_project_does_not_query_mart()
    {
        $this->assertNoMartQueries(function () {
            $this->actingAs($this->user)
                ->get("/projects/{$this->project->id}/duplicate")
                ->assertOk();
        });
    }

    /** @test */
    public function deleting_a_metag_project_does_not_query_mart()
    {
        $this->assertNoMartQueries(function () {
            $this->actingAs($this->user)
                ->delete(route('projects.destroy', $this->project))
                ->assertOk();
        });
    }

    /** @test */
    public function an_entry_of_a_metag_case_has_no_mart_entry_and_does_not_look_for_one()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);

        $this->assertNoMartQueries(fn () => $this->assertNull($entry->martEntry()));
    }

    /** @test */
    public function a_failed_mart_project_lookup_is_not_hidden()
    {
        $this->makeProjectMart();
        $this->breakMartConnection();

        $this->expectException(QueryException::class);

        $this->project->martProject();
    }

    /** @test */
    public function a_failed_mart_entry_lookup_is_not_hidden()
    {
        $this->makeProjectMart();
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $this->breakMartConnection();

        $this->expectException(QueryException::class);

        $entry->martEntry();
    }

    /** @test */
    public function a_failed_mart_delete_stops_the_project_delete_before_main_changes()
    {
        $this->makeProjectMart();
        $invited = User::factory()->create();
        $this->project->invited()->attach($invited);
        $this->breakMartConnection();

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->user)->delete(route('projects.destroy', $this->project));
            $this->fail('The MART failure was hidden.');
        } catch (QueryException) {
            $this->assertSame(1, $this->project->fresh()->invited()->count());
        }
    }

    /** @test */
    public function a_mart_project_is_removed_again_when_its_mart_row_cannot_be_created()
    {
        $this->breakMartConnection();
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->user)->postJson('/projects', [
                'name' => 'Half created study',
                'description' => 'MART is down',
                'isMart' => true,
            ]);
            $this->fail('The MART failure was hidden.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Failed to create MART project', $e->getMessage());
        }

        $this->assertDatabaseMissing('projects', ['name' => 'Half created study']);
    }
}
