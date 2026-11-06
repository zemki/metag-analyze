<?php

namespace Tests\Feature;

use App\Entry;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartSchedule;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * `mart_entries.case_id` is filled through `main_entry_id`. Rows whose main
 * entry is gone stay empty, which matches today's totals: a deleted `entries`
 * row already stopped counting.
 */
class MartEntryCaseBackfillTest extends TestCase
{
    private MartSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
        $this->schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + 1,
            'name' => 'Daily check-in',
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);
    }

    private function addOldSubmission(?int $mainEntryId): MartEntry
    {
        return MartEntry::create([
            'main_entry_id' => $mainEntryId,
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => $this->schedule->questionnaire_id,
            'participant_id' => $this->case->name,
            'user_id' => $this->user->email,
            'started_at' => now(),
            'completed_at' => now(),
            'duration_ms' => 1000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => now()->timestamp * 1000,
        ]);
    }

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_11_06_000001_add_case_id_to_mart_entries.php');

        $migration->up();
    }

    /** @test */
    public function it_fills_the_case_through_the_main_entry()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $submission = $this->addOldSubmission($entry->id);

        $this->runMigration();

        $this->assertSame($this->case->id, (int) $submission->fresh()->case_id);
    }

    /** @test */
    public function it_leaves_a_row_without_a_main_entry_empty()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $submission = $this->addOldSubmission($entry->id);
        $entry->delete();

        $this->runMigration();

        $this->assertNull($submission->fresh()->case_id);
    }

    /** @test */
    public function it_keeps_updated_at_as_it_was()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);
        $submission = $this->addOldSubmission($entry->id);
        DB::connection('mart')->table('mart_entries')->where('id', $submission->id)
            ->update(['updated_at' => '2026-01-02 03:04:05']);

        $this->runMigration();

        $this->assertSame('2026-01-02 03:04:05', $submission->fresh()->updated_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function a_submission_can_have_no_main_entry()
    {
        $this->assertNull($this->addOldSubmission(null)->fresh()->main_entry_id);
    }
}
