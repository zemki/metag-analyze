<?php

namespace Tests\Feature;

use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartCaseSchedule;
use App\Mart\MartQuestionHistory;
use App\Project;
use App\User;
use App\Cases;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MartQuestionnaireDeleteTest extends TestCase
{
    protected $martProject;
    protected $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        // Update existing project with MART configuration (parent creates $this->user and $this->project)
        $this->project->update([
            'name' => 'Test MART Project',
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Configuration'],
            ]),
        ]);

        // Get or create MART project in MART database (MART DB doesn't rollback between tests)
        $this->martProject = MartProject::firstOrCreate(['main_project_id' => $this->project->id]);

        // Use unique questionnaire_id based on project ID + timestamp to avoid conflicts
        $uniqueQuestionnaireId = $this->project->id * 1000 + (time() % 1000);

        // Create a schedule with questions (unique ID ensures no conflicts)
        $this->schedule = MartSchedule::create([
            'mart_project_id' => $this->martProject->id,
            'questionnaire_id' => $uniqueQuestionnaireId,
            'name' => 'Test Questionnaire',
            'type' => 'repeating',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'end_date_time' => ['date' => '2025-12-31', 'time' => '21:00'],
                'max_daily_submits' => 6,
            ],
            'notification_config' => [
                'show_notifications' => true,
            ],
        ]);

        // Create questions
        MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 1,
            'text' => 'Test Question 1',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 2,
            'text' => 'Test Question 2',
            'type' => 'scale',
            'config' => ['minValue' => 1, 'maxValue' => 10],
            'is_mandatory' => false,
            'version' => 1,
        ]);
    }

    /** @test */
    public function it_can_delete_a_questionnaire()
    {
        $this->actingAs($this->user);

        // Verify schedule and questions exist
        $this->assertDatabaseHas('mart_schedules', [
            'id' => $this->schedule->id,
            'name' => 'Test Questionnaire',
        ], 'mart');

        $this->assertEquals(2, MartQuestion::where('schedule_id', $this->schedule->id)->count());

        // Delete the questionnaire
        $response = $this->deleteJson("/questionnaires/{$this->schedule->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Questionnaire deleted successfully',
            ]);

        // Verify schedule is deleted
        $this->assertDatabaseMissing('mart_schedules', [
            'id' => $this->schedule->id,
        ], 'mart');

        // Verify questions are deleted
        $this->assertEquals(0, MartQuestion::where('schedule_id', $this->schedule->id)->count());
    }

    /** @test */
    public function it_deletes_case_schedule_overrides_when_deleting_questionnaire()
    {
        // Skip if mart_case_schedules table doesn't exist in test DB
        try {
            DB::connection('mart')->table('mart_case_schedules')->exists();
        } catch (\Exception $e) {
            $this->markTestSkipped('mart_case_schedules table not available in test database');
        }

        $this->actingAs($this->user);

        // Create a case
        $case = Cases::create([
            'name' => 'TestCase001',
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'duration' => 'startDay:01.01.2025|',
            'first_login_at' => now(),
        ]);

        // Create case schedule override
        MartCaseSchedule::create([
            'case_id' => $case->id,
            'schedule_id' => $this->schedule->id,
            'timing_overrides' => [
                'start_date_time' => ['date' => '2025-02-01', 'time' => '10:00'],
            ],
            'calculated_at' => now(),
        ]);

        // Verify case schedule exists
        $this->assertDatabaseHas('mart_case_schedules', [
            'case_id' => $case->id,
            'schedule_id' => $this->schedule->id,
        ], 'mart');

        // Delete the questionnaire
        $response = $this->deleteJson("/questionnaires/{$this->schedule->id}");

        $response->assertStatus(200);

        // Verify case schedule override is deleted
        $this->assertDatabaseMissing('mart_case_schedules', [
            'case_id' => $case->id,
            'schedule_id' => $this->schedule->id,
        ], 'mart');
    }

    /** @test */
    public function it_preserves_question_history_when_deleting_questionnaire()
    {
        $this->actingAs($this->user);

        // Get a question UUID
        $question = MartQuestion::where('schedule_id', $this->schedule->id)->first();
        $questionUuid = $question->uuid;

        // Create question history (simulating a previous edit)
        MartQuestionHistory::create([
            'question_uuid' => $questionUuid,
            'version' => 1,
            'text' => 'Original Question Text',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
            'changed_at' => now()->subDay(),
        ]);

        // Verify history exists
        $this->assertDatabaseHas('mart_question_history', [
            'question_uuid' => $questionUuid,
        ], 'mart');

        // Delete the questionnaire
        $response = $this->deleteJson("/questionnaires/{$this->schedule->id}");

        $response->assertStatus(200);

        // Verify question history is PRESERVED (not deleted)
        $this->assertDatabaseHas('mart_question_history', [
            'question_uuid' => $questionUuid,
        ], 'mart');
    }

    /** @test */
    public function it_requires_authentication_to_delete_questionnaire()
    {
        // Try to delete without authentication
        $response = $this->deleteJson("/questionnaires/{$this->schedule->id}");

        // Should be unauthorized
        $response->assertStatus(401);

        // Verify schedule still exists
        $this->assertDatabaseHas('mart_schedules', [
            'id' => $this->schedule->id,
        ], 'mart');
    }

    /** @test */
    public function it_returns_404_for_non_existent_questionnaire()
    {
        $this->actingAs($this->user);

        // Try to delete a non-existent questionnaire
        $response = $this->deleteJson("/questionnaires/99999");

        $response->assertStatus(404);
    }
}
