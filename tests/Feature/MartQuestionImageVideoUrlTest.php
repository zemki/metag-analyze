<?php

namespace Tests\Feature;

use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartQuestionHistory;
use Tests\TestCase;

class MartQuestionImageVideoUrlTest extends TestCase
{
    protected $martProject;
    protected $schedule;

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

        // Use unique questionnaire_id based on project ID to avoid conflicts
        $uniqueQuestionnaireId = $this->project->id * 1000 + 100;

        // Get or create a schedule with unique questionnaire_id
        $this->schedule = MartSchedule::firstOrCreate(
            [
                'mart_project_id' => $this->martProject->id,
                'questionnaire_id' => $uniqueQuestionnaireId,
            ],
            [
                'name' => 'Test Questionnaire',
                'type' => 'single',
                'timing_config' => [
                    'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                ],
                'notification_config' => [
                    'show_notifications' => false,
                ],
            ]
        );
    }

    /** @test */
    public function it_can_create_question_with_image_url()
    {
        $this->actingAs($this->user);

        // Use unique questionnaire_id based on time
        $questionnaireId = time() % 100000 + 1000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Image URL Test',
            'type' => 'single',
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Rate this image',
                    'image_url' => 'https://example.com/test-image.jpg',
                    'type' => 'range',
                    'mandatory' => true,
                    'config' => ['min' => 1, 'max' => 10, 'step' => 1],
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Get the created schedule from response
        $responseData = $response->json();
        $scheduleId = $responseData['schedule']['id'];
        $schedule = MartSchedule::find($scheduleId);
        $question = $schedule->questions()->first();

        $this->assertEquals('https://example.com/test-image.jpg', $question->image_url);
        $this->assertNull($question->video_url);
    }

    /** @test */
    public function it_can_create_question_with_video_url()
    {
        $this->actingAs($this->user);

        $questionnaireId = time() % 100000 + 2000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Video URL Test',
            'type' => 'single',
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Watch this video and answer',
                    'video_url' => 'https://example.com/test-video.mp4',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $responseData = $response->json();
        $schedule = MartSchedule::find($responseData['schedule']['id']);
        $question = $schedule->questions()->first();

        $this->assertNull($question->image_url);
        $this->assertEquals('https://example.com/test-video.mp4', $question->video_url);
    }

    /** @test */
    public function it_can_create_question_with_both_image_and_video_urls()
    {
        $this->actingAs($this->user);

        $questionnaireId = time() % 100000 + 3000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Both URLs Test',
            'type' => 'single',
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Multi-media question',
                    'image_url' => 'https://example.com/image.png',
                    'video_url' => 'https://example.com/video.mp4',
                    'type' => 'one choice',
                    'mandatory' => false,
                    'config' => ['options' => ['Yes', 'No']],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $responseData = $response->json();
        $schedule = MartSchedule::find($responseData['schedule']['id']);
        $question = $schedule->questions()->first();

        $this->assertEquals('https://example.com/image.png', $question->image_url);
        $this->assertEquals('https://example.com/video.mp4', $question->video_url);
    }

    /** @test */
    public function it_validates_image_url_format()
    {
        $this->actingAs($this->user);

        $questionnaireId = time() % 100000 + 4000;

        $response = $this->postJson("/projects/{$this->project->id}/questionnaires", [
            'questionnaire_id' => $questionnaireId,
            'name' => 'Invalid URL Test',
            'type' => 'single',
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'show_progress_bar' => true,
            'show_notifications' => false,
            'questions' => [
                [
                    'text' => 'Test question',
                    'image_url' => 'not-a-valid-url',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.image_url']);
    }

    /** @test */
    public function it_preserves_urls_in_question_history_when_updated()
    {
        $this->actingAs($this->user);

        // Create a question with image URL
        $question = MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 1,
            'text' => 'Original question',
            'image_url' => 'https://example.com/original.jpg',
            'video_url' => null,
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        // Update the question
        $question->updateQuestion([
            'text' => 'Updated question',
            'image_url' => 'https://example.com/updated.jpg',
            'video_url' => 'https://example.com/new-video.mp4',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
        ]);

        // Verify history was created with original URLs
        $history = MartQuestionHistory::where('question_uuid', $question->uuid)
            ->where('version', 1)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('Original question', $history->text);
        $this->assertEquals('https://example.com/original.jpg', $history->image_url);
        $this->assertNull($history->video_url);

        // Verify current question has updated URLs
        $question->refresh();
        $this->assertEquals('Updated question', $question->text);
        $this->assertEquals('https://example.com/updated.jpg', $question->image_url);
        $this->assertEquals('https://example.com/new-video.mp4', $question->video_url);
        $this->assertEquals(2, $question->version);
    }

    /** @test */
    public function it_can_update_question_urls_via_api()
    {
        $this->actingAs($this->user);

        // Create a question
        $question = MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => 1,
            'text' => 'Test question',
            'image_url' => null,
            'video_url' => null,
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        // Update via API
        $response = $this->putJson("/questionnaires/{$this->schedule->id}/questions", [
            'questions' => [
                [
                    'uuid' => $question->uuid,
                    'text' => 'Updated question',
                    'image_url' => 'https://example.com/added-image.jpg',
                    'video_url' => 'https://example.com/added-video.mp4',
                    'type' => 'text',
                    'mandatory' => true,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $question->refresh();
        $this->assertEquals('https://example.com/added-image.jpg', $question->image_url);
        $this->assertEquals('https://example.com/added-video.mp4', $question->video_url);
    }
}
