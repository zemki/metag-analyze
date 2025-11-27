<?php

namespace Tests\Feature;

use App\Mart\MartProject;
use App\Mart\MartSchedule;
use Tests\TestCase;

class MartStartOnFirstLoginTest extends TestCase
{
    protected $martProject;

    protected function setUp(): void
    {
        parent::setUp();

        // Update existing project with MART configuration
        $this->project->update([
            'name' => 'Test MART Project',
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Configuration'],
            ]),
        ]);

        // Get or create MART project (MART DB doesn't rollback between tests)
        $this->martProject = MartProject::firstOrCreate(['main_project_id' => $this->project->id]);
    }

    /** @test */
    public function it_rejects_start_on_first_login_for_repeating_questionnaire()
    {
        $this->actingAs($this->user);

        $questionnaireId = time() % 100000 + 5000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Repeating with Start on Login',
            'type' => 'repeating',
            'start_on_first_login' => true,
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'end_date_time' => ['date' => '2025-12-31', 'time' => '21:00'],
            'daily_start_time' => '09:00',
            'daily_end_time' => '21:00',
            'daily_interval_duration' => 120,
            'max_daily_submits' => 3,
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Test question',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Start on first login is not supported for repeating questionnaires.',
            ]);
    }

    /** @test */
    public function it_allows_start_on_first_login_for_single_questionnaire()
    {
        $this->actingAs($this->user);

        $questionnaireId = time() % 100000 + 6000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Single with Start on Login',
            'type' => 'single',
            'start_on_first_login' => true,
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Test question',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify the flag was saved
        $responseData = $response->json();
        $schedule = MartSchedule::find($responseData['schedule']['id']);

        $this->assertTrue($schedule->timing_config['start_on_first_login']);
    }

    /** @test */
    public function it_rejects_updating_repeating_questionnaire_with_start_on_first_login()
    {
        $this->actingAs($this->user);

        // Create a repeating schedule without the flag
        $schedule = MartSchedule::create([
            'mart_project_id' => $this->martProject->id,
            'questionnaire_id' => time() % 100000 + 7000,
            'name' => 'Repeating Questionnaire',
            'type' => 'repeating',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'end_date_time' => ['date' => '2025-12-31', 'time' => '21:00'],
                'start_on_first_login' => false,
            ],
            'notification_config' => [
                'show_notifications' => false,
            ],
        ]);

        // Try to update with start_on_first_login = true
        $response = $this->putJson("/questionnaires/{$schedule->id}/questions", [
            'start_on_first_login' => true,
            'questions' => [
                [
                    'text' => 'Test question',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Start on first login is not supported for repeating questionnaires.',
            ]);
    }

    /** @test */
    public function it_rejects_changing_single_to_repeating_when_start_on_first_login_is_set()
    {
        $this->actingAs($this->user);

        // Create a single schedule with start_on_first_login = true
        $schedule = MartSchedule::create([
            'mart_project_id' => $this->martProject->id,
            'questionnaire_id' => time() % 100000 + 8000,
            'name' => 'Single Questionnaire',
            'type' => 'single',
            'timing_config' => [
                'start_on_first_login' => true,
            ],
            'notification_config' => [
                'show_notifications' => false,
            ],
        ]);

        // Try to change type to repeating without clearing the flag
        $response = $this->putJson("/questionnaires/{$schedule->id}/questions", [
            'type' => 'repeating',
            'questions' => [
                [
                    'text' => 'Test question',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Start on first login is not supported for repeating questionnaires.',
            ]);
    }
}
