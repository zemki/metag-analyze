<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\Filament\Widgets\StatsOverview;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Media;
use App\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * MART submissions live in `mart_entries`, linked to their case by `case_id`.
 * Case lists and totals read them from there, and the main `entries` table
 * takes no new rows for MART cases.
 */
class MartCaseEntriesTest extends TestCase
{
    private const TOKEN = 'mart_case_entries_test_token';

    private MartSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
        $this->schedule = $this->scheduleFor($this->project);
        $this->user->forceFill(['api_token' => hash('sha256', self::TOKEN)])->save();
        $this->actingAs($this->user);
    }

    private function scheduleFor(Project $project): MartSchedule
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $project->id])->id,
            'questionnaire_id' => $project->id * 1000 + 1,
            'name' => 'Daily check-in',
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);

        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 1,
            'text' => 'How was your day?',
            'type' => 'text',
            'is_mandatory' => false,
            'version' => 1,
        ]);

        return $schedule;
    }

    private function addSubmission(Cases $case, array $attributes = []): MartEntry
    {
        return MartEntry::create(array_merge([
            'case_id' => $case->id,
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => $this->schedule->questionnaire_id,
            'participant_id' => $case->name,
            'user_id' => $this->user->email,
            'started_at' => '2026-05-01 10:00:00',
            'completed_at' => '2026-05-01 10:05:00',
            'duration_ms' => 300000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => 1777629600000,
        ], $attributes));
    }

    private function addCase(Project $project): Cases
    {
        return Cases::factory()->create([
            'project_id' => $project->id,
            'user_id' => $this->user->id,
            'duration' => 'value:24|days:1',
        ]);
    }

    private function metagProject(): Project
    {
        return Project::factory()->create(['created_by' => $this->user->id, 'inputs' => '[]']);
    }

    /** @return string[] */
    private function martQueriesDuring(callable $action): array
    {
        $mart = DB::connection('mart');
        $mart->flushQueryLog();
        $mart->enableQueryLog();

        $action();

        return array_column($mart->getQueryLog(), 'query');
    }

    private function totalEntriesStat(): mixed
    {
        Cache::forget('dashboard_stats');

        $stats = (new \ReflectionMethod(StatsOverview::class, 'getStats'))->invoke(new StatsOverview);

        return collect($stats)->first(fn ($stat) => $stat->getLabel() === 'Total Entries')->getValue();
    }

    /** @test */
    public function submitting_stores_the_case_on_the_mart_entry()
    {
        $this->withToken(self::TOKEN)
            ->postJson("/api/mobile/cases/{$this->case->id}/submit", [
                'projectId' => $this->project->id,
                'questionnaireId' => $this->schedule->questionnaire_id,
                'userId' => $this->user->email,
                'participantId' => $this->case->name,
                'questionnaireStarted' => now()->timestamp * 1000,
                'questionnaireDuration' => 1000,
                'answers' => ['1' => 'fine'],
                'timestamp' => now()->timestamp * 1000,
                'timezone' => 'Europe/Berlin',
            ])
            ->assertOk();

        $this->assertSame(1, MartEntry::where('case_id', $this->case->id)->count());
    }

    /** @test */
    public function the_case_list_counts_mart_submissions()
    {
        $this->addSubmission($this->case);
        $this->addSubmission($this->case);

        $this->getJson("/projects/{$this->project->id}/cases")
            ->assertOk()
            ->assertJsonPath('data.0.entries_count', 2);
    }

    /** @test */
    public function the_case_list_shows_mart_submissions_as_its_entries()
    {
        $submission = $this->addSubmission($this->case);

        $this->getJson("/projects/{$this->project->id}/cases")
            ->assertJsonCount(1, 'data.0.entries')
            ->assertJsonPath('data.0.entries.0.id', $submission->id)
            ->assertJsonPath('data.0.entries.0.begin', '2026-05-01 10:00:00')
            ->assertJsonPath('data.0.entries.0.end', '2026-05-01 10:05:00');
    }

    /** @test */
    public function a_main_entry_without_a_submission_is_not_listed_for_a_mart_case()
    {
        Entry::factory()->create(['case_id' => $this->case->id]);

        $this->getJson("/projects/{$this->project->id}/cases")
            ->assertJsonPath('data.0.entries_count', 0);
    }

    /** @test */
    public function a_submission_with_a_main_entry_counts_once()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $this->addSubmission($this->case, ['main_entry_id' => $entry->id]);

        $this->getJson("/projects/{$this->project->id}/cases")
            ->assertJsonPath('data.0.entries_count', 1);
    }

    /** @test */
    public function the_project_list_totals_mart_submissions()
    {
        $this->addSubmission($this->case);
        $this->addSubmission($this->case);

        $this->get('/projects')->assertViewHas('projects', function ($projects) {
            return $projects->firstWhere('id', $this->project->id)['entries'] === 2;
        });
    }

    /** @test */
    public function the_case_list_reads_mart_entries_once_however_many_cases_it_has()
    {
        $this->addSubmission($this->addCase($this->project));
        $this->addSubmission($this->addCase($this->project));

        $queries = $this->martQueriesDuring(fn () => $this->getJson("/projects/{$this->project->id}/cases"));

        $this->assertCount(1, array_filter($queries, fn ($query) => str_contains($query, 'mart_entries')));
    }

    /** @test */
    public function the_project_list_reads_mart_entries_once_however_many_projects_it_has()
    {
        $second = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => json_encode([['type' => 'mart']]),
        ]);
        $this->addCase($second);

        $queries = $this->martQueriesDuring(fn () => $this->get('/projects'));

        $this->assertCount(1, array_filter($queries, fn ($query) => str_contains($query, 'mart_entries')));
    }

    /** @test */
    public function a_metag_case_list_does_not_query_mart()
    {
        $project = $this->metagProject();
        $this->addCase($project);

        $this->assertSame([], $this->martQueriesDuring(function () use ($project) {
            $this->getJson("/projects/{$project->id}/cases")->assertOk();
        }));
    }

    /** @test */
    public function a_metag_project_list_does_not_query_mart()
    {
        $this->project->update(['inputs' => '[]']);

        $this->assertSame([], $this->martQueriesDuring(fn () => $this->get('/projects')->assertOk()));
    }

    /** @test */
    public function counting_a_metag_case_adds_no_query()
    {
        $case = $this->addCase($this->metagProject());
        DB::enableQueryLog();

        $case->entries_count;

        $this->assertCount(1, DB::getQueryLog());
    }

    /** @test */
    public function entries_cannot_be_added_to_a_mart_case()
    {
        $this->postJson("/cases/{$this->case->id}/entries", [
            'begin' => '2026-05-01 10:00:00',
            'end' => '2026-05-01 10:05:00',
            'case_id' => $this->case->id,
            'inputs' => [],
        ])->assertForbidden();

        $this->assertDatabaseMissing('entries', ['case_id' => $this->case->id]);
    }

    /** @test */
    public function an_old_entry_of_a_mart_case_cannot_be_edited()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id, 'begin' => 'untouched']);

        $this->patchJson("/cases/{$this->case->id}/entries/{$entry->id}", [
            'begin' => '2026-05-01 10:00:00',
            'end' => '2026-05-01 10:05:00',
            'case_id' => $this->case->id,
            'inputs' => [],
        ])->assertForbidden();

        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'begin' => 'untouched']);
    }

    /** @test */
    public function an_old_entry_of_a_mart_case_cannot_be_deleted()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);

        $this->deleteJson("/cases/{$this->case->id}/entries/{$entry->id}")->assertForbidden();

        $this->assertDatabaseHas('entries', ['id' => $entry->id]);
    }

    /** @test */
    public function entries_can_still_be_added_to_a_metag_case()
    {
        $case = $this->addCase($this->metagProject());

        $this->postJson("/cases/{$case->id}/entries", [
            'begin' => '2026-05-01 10:00:00',
            'end' => '2026-05-01 10:05:00',
            'case_id' => $case->id,
            'media_id' => Media::factory()->create()->id,
            'inputs' => [],
        ])->assertOk();

        $this->assertDatabaseHas('entries', ['case_id' => $case->id]);
    }

    /** @test */
    public function the_admin_total_counts_a_submission_once_with_or_without_a_main_entry()
    {
        $before = $this->totalEntriesStat();

        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $this->addSubmission($this->case, ['main_entry_id' => $entry->id]);
        $this->addSubmission($this->case);

        $this->assertSame($before + 2, $this->totalEntriesStat());
    }
}
