<?php

namespace Tests\Feature;

use App\Exports\Mart\MartProjectExport;
use App\Mart\MartAnswer;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MartExportTest extends TestCase
{
    protected MartProject $martProject;

    protected MartSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        // Make project a MART project
        $this->project->update([
            'inputs' => json_encode([
                ['type' => 'mart', 'projectOptions' => [
                    'startDateAndTime' => ['date' => '2025-01-01', 'time' => '00:00'],
                    'endDateAndTime' => ['date' => '2025-12-31', 'time' => '23:59'],
                ]],
            ]),
        ]);

        // Create MART project record
        $this->martProject = MartProject::firstOrCreate([
            'main_project_id' => $this->project->id,
        ]);

        // Create a schedule with questions
        $this->schedule = MartSchedule::create([
            'mart_project_id' => $this->martProject->id,
            'questionnaire_id' => 1,
            'name' => 'Daily Survey',
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up MART database records (not wrapped in transaction)
        MartAnswer::whereIn('entry_id',
            MartEntry::where('schedule_id', $this->schedule->id)->pluck('id')
        )->delete();
        MartEntry::where('schedule_id', $this->schedule->id)->delete();
        MartQuestion::where('schedule_id', $this->schedule->id)->delete();
        MartStat::where('mart_project_id', $this->martProject->id)->delete();
        MartDeviceInfo::where('participant_id', 'TEST_PARTICIPANT')->delete();
        $this->schedule->delete();

        parent::tearDown();
    }

    /** @test */
    public function it_exports_mart_project_as_xlsx()
    {
        Excel::fake();

        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}/export")
            ->assertOk();

        Excel::assertDownloaded($this->project->name . ' - MART data.xlsx');
    }

    /** @test */
    public function it_exports_with_questionnaire_data()
    {
        // Create questions
        $q1 = MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 1,
            'text' => 'How are you feeling?',
            'type' => 'range',
            'config' => ['min' => 1, 'max' => 10],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        $q2 = MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 2,
            'text' => 'What did you do today?',
            'type' => 'multiple choice',
            'config' => ['options' => ['Work', 'Exercise', 'Rest']],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        // Create an entry with answers
        $entry = MartEntry::create([
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => 1,
            'main_entry_id' => 0,
            'participant_id' => 'TEST_PARTICIPANT',
            'user_id' => 'test@example.com',
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
            'duration_ms' => 300000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => now()->timestamp,
        ]);

        MartAnswer::create([
            'entry_id' => $entry->id,
            'question_uuid' => $q1->uuid,
            'question_version' => 1,
            'answer_value' => '7',
        ]);

        MartAnswer::create([
            'entry_id' => $entry->id,
            'question_uuid' => $q2->uuid,
            'question_version' => 1,
            'answer_value' => json_encode(['Work', 'Exercise']),
        ]);

        $export = new MartProjectExport($this->project);
        $sheets = $export->sheets();

        $this->assertCount(4, $sheets, 'Export should have 4 sheets');

        // Test entries sheet
        $entriesSheet = $sheets[0];
        $headings = $entriesSheet->headings();

        $this->assertContains('How are you feeling?', $headings);
        $this->assertContains('What did you do today?', $headings);

        $collection = $entriesSheet->collection();
        $this->assertCount(1, $collection);

        $row = $entriesSheet->map($collection->first());
        $this->assertEquals('TEST_PARTICIPANT', $row[0]);
        $this->assertEquals('7', $row[7]); // First question answer
        $this->assertEquals('Work, Exercise', $row[8]); // Multiple choice joined
    }

    /** @test */
    public function it_exports_device_info()
    {
        // Need an entry so device info is linked via participant_id
        $entry = MartEntry::create([
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => 1,
            'main_entry_id' => 0,
            'participant_id' => 'TEST_PARTICIPANT',
            'user_id' => 'test@example.com',
            'started_at' => now(),
            'completed_at' => now(),
            'duration_ms' => 1000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => now()->timestamp,
        ]);

        MartDeviceInfo::create([
            'participant_id' => 'TEST_PARTICIPANT',
            'user_id' => 'test@example.com',
            'os' => 'android',
            'os_version' => '14',
            'model' => 'Pixel 8',
            'manufacturer' => 'Google',
            'last_updated' => now(),
        ]);

        $export = new MartProjectExport($this->project);
        $deviceSheet = $export->sheets()[1];

        $collection = $deviceSheet->collection();
        $this->assertCount(1, $collection);

        $row = $deviceSheet->map($collection->first());
        $this->assertEquals('TEST_PARTICIPANT', $row[0]);
        $this->assertEquals('android', $row[2]);
        $this->assertEquals('Pixel 8', $row[4]);
    }

    /** @test */
    public function it_exports_stats()
    {
        MartStat::create([
            'mart_project_id' => $this->martProject->id,
            'participant_id' => 'TEST_PARTICIPANT',
            'user_id' => 'test@example.com',
            'timestamp' => 1776767220,
            'timezone' => 'Europe/Berlin',
        ]);

        $export = new MartProjectExport($this->project);
        $statsSheet = $export->sheets()[2];

        $collection = $statsSheet->collection();
        $this->assertCount(1, $collection);

        $row = $statsSheet->map($collection->first());
        $this->assertEquals('TEST_PARTICIPANT', $row[0]);
        $this->assertEquals(1776767220, $row[2]);
    }

    /** @test */
    public function it_skips_display_questions_in_headings()
    {
        MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 1,
            'text' => 'Welcome to the survey',
            'type' => 'display',
            'config' => [],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 2,
            'text' => 'Rate your mood',
            'type' => 'range',
            'config' => ['min' => 1, 'max' => 5],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        $export = new MartProjectExport($this->project);
        $entriesSheet = $export->sheets()[0];
        $headings = $entriesSheet->headings();

        $this->assertNotContains('Welcome to the survey', $headings);
        $this->assertContains('Rate your mood', $headings);
    }

    /** @test */
    public function it_denies_export_to_non_owner()
    {
        $otherUser = \App\User::factory()->researcher()->create();

        $this->actingAs($otherUser)
            ->get("/projects/{$this->project->id}/export")
            ->assertStatus(403);
    }

    /** @test */
    public function regular_project_export_still_works()
    {
        // Reset to non-MART project
        $this->project->update(['inputs' => '[]']);

        Excel::fake();

        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}/export")
            ->assertOk();

        Excel::assertDownloaded('cases from ' . $this->project->name . ' project.xlsx');
    }
}
