<?php

namespace Tests\Feature;

use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use Tests\TestCase;

/**
 * Duplicating a MART project copies its questionnaires question by question.
 * The MART connection is rolled back too, so nothing stays behind.
 */
class ProjectDuplicationMartQuestionsTest extends TestCase
{
    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    /** @test */
    public function duplicating_a_mart_project_copies_each_questions_image_and_video_url()
    {
        $this->project->update([
            'name' => 'Project with media questions',
            'inputs' => json_encode([['type' => 'mart', 'name' => 'MART Configuration', 'answers' => []]]),
        ]);
        $schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + 700,
            'name' => 'Media questions',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
        ]);
        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 1,
            'text' => 'Look at this',
            'type' => 'display',
            'config' => [],
            'is_mandatory' => false,
            'image_url' => 'https://example.com/picture.jpg',
            'video_url' => 'https://example.com/clip.mp4',
            'version' => 1,
        ]);

        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}/duplicate")
            ->assertStatus(200);

        $this->assertSame(
            ['https://example.com/picture.jpg', 'https://example.com/clip.mp4'],
            [$this->copiedQuestion()->image_url, $this->copiedQuestion()->video_url],
        );
    }

    /** The one question of the duplicated project. */
    private function copiedQuestion(): MartQuestion
    {
        $copy = Project::where('name', 'Project with media questions')
            ->where('id', '!=', $this->project->id)
            ->firstOrFail();
        $scheduleIds = MartSchedule::where('mart_project_id', $copy->martProject()->id)->pluck('id');

        return MartQuestion::whereIn('schedule_id', $scheduleIds)->firstOrFail();
    }
}
