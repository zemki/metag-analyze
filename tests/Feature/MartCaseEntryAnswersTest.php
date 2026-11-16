<?php

namespace Tests\Feature;

use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Tests\TestCase;

/**
 * The case page shows each MART submission with its questionnaire and its
 * answers. Each answer keeps the raw value the Excel export writes, so the
 * two can be compared, plus the option label where the question has one.
 * The MART connection is rolled back too.
 */
class MartCaseEntryAnswersTest extends TestCase
{
    private MartSchedule $schedule;

    private MartEntry $entry;

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update([
            'inputs' => json_encode([['type' => 'mart', 'name' => 'MART Configuration', 'answers' => []]]),
        ]);
        // Completed, so the case page may show its submissions.
        $this->case->update(['duration' => 'startDay:01.01.2025|lastDay:02.01.2025|']);

        $this->schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + 800,
            'name' => 'Morning check-in',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
        ]);

        $this->entry = MartEntry::create([
            'case_id' => $this->case->id,
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => $this->schedule->questionnaire_id,
            'participant_id' => $this->case->name,
            'user_id' => $this->user->email,
            'started_at' => '2025-01-01 10:00:00',
            'completed_at' => '2025-01-01 10:01:05',
            'duration_ms' => 65000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => 1735722065000,
        ]);

        // Saved out of order on purpose: the answers follow the question order.
        $this->answer(3, 'range values', 'How tired?', ['options' => ['wenig', null, 'viel']], [3]);
        $this->answer(1, 'one choice', '<b>How</b><br>are you?', ['options' => ['Schlecht', 'Gut', 'Sehr gut']], [2]);
        $this->answer(2, 'multiple choice', 'What did you do?', [
            'options' => ['Arbeit', 'Sport'], 'includeOtherOption' => true, 'otherOptionLabel' => 'Sonstiges',
        ], ['text', 'Joggen']);
        $this->answer(4, 'text', 'Anything else?', [], ['Hallo']);
    }

    /** @test */
    public function each_mart_submission_names_its_questionnaire()
    {
        $this->assertSame('Morning check-in', $this->listedEntry()['mart_questionnaire']);
    }

    /** @test */
    public function each_mart_submission_carries_its_duration()
    {
        $this->assertSame(65000, $this->listedEntry()['mart_duration_ms']);
    }

    /** @test */
    public function the_answers_follow_the_question_order_with_plain_question_texts()
    {
        $this->assertSame(
            ['How are you?', 'What did you do?', 'How tired?', 'Anything else?'],
            array_column($this->listedEntry()['mart_answers'], 'question'),
        );
    }

    /** @test */
    public function a_choice_answer_shows_its_option_label_next_to_the_value_the_export_writes()
    {
        $this->assertSame(['question' => 'How are you?', 'value' => '2', 'label' => 'Gut'], $this->listedAnswer('How are you?'));
    }

    /** @test */
    public function an_other_answer_shows_the_other_label_and_the_typed_text()
    {
        $this->assertSame(['value' => 'text, Joggen', 'label' => 'Sonstiges, Joggen'], $this->valueAndLabel('What did you do?'));
    }

    /** @test */
    public function a_slider_with_labels_shows_the_label_of_its_step()
    {
        $this->assertSame(['value' => '3', 'label' => 'viel'], $this->valueAndLabel('How tired?'));
    }

    /** @test */
    public function a_text_answer_has_no_label()
    {
        $this->assertSame(['value' => 'Hallo', 'label' => null], $this->valueAndLabel('Anything else?'));
    }

    private function answer(int $position, string $type, string $text, array $config, array $value): void
    {
        $question = MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => $position,
            'text' => $text,
            'type' => $type,
            'config' => $config,
            'is_mandatory' => false,
            'version' => 1,
        ]);

        MartAnswer::create([
            'entry_id' => $this->entry->id,
            'question_uuid' => $question->uuid,
            'question_version' => 1,
            'answer_value' => json_encode($value),
        ]);
    }

    private function listedEntry(): array
    {
        return $this->actingAs($this->user)
            ->getJson("/projects/{$this->project->id}/cases")
            ->assertOk()
            ->json('data.0.entries.0');
    }

    private function listedAnswer(string $question): array
    {
        return collect($this->listedEntry()['mart_answers'])->firstWhere('question', $question);
    }

    private function valueAndLabel(string $question): array
    {
        $answer = $this->listedAnswer($question);

        return ['value' => $answer['value'], 'label' => $answer['label']];
    }
}
