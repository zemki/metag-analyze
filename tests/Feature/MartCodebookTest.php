<?php

namespace Tests\Feature;

use App\Exports\Mart\FormulaSafeValueBinder;
use App\Exports\Mart\MartProjectExport;
use App\Exports\Mart\MartQuestionsSheet;
use App\Mart\MartCodebook;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\User;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

/**
 * The list of a MART project's questions with the values their answers are
 * stored as, for the project page and the Excel export. MART only.
 */
class MartCodebookTest extends TestCase
{
    private MartSchedule $schedule;

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart', 'projectOptions' => []]])]);
        $martProject = MartProject::create(['main_project_id' => $this->project->id]);
        $this->schedule = $this->schedule($martProject, 1, 'Evening check-in');
    }

    /** @test */
    public function a_choice_lists_its_options_numbered_from_1_as_stored()
    {
        $this->question(1, 'one choice', ['options' => ['At home', 'At work']]);

        $this->assertSame([['value' => 1, 'label' => 'At home'], ['value' => 2, 'label' => 'At work']], $this->first()['values']);
    }

    /** @test */
    public function the_other_option_is_listed_as_text_with_its_label()
    {
        $this->question(1, 'multiple choice', ['options' => ['Partner'], 'includeOtherOption' => true, 'otherOptionLabel' => 'Someone else']);

        $this->assertSame(['value' => 'text', 'label' => 'Someone else'], $this->first()['values'][1]);
    }

    /** @test */
    public function an_other_option_without_a_label_is_called_other()
    {
        $this->question(1, 'one choice', ['options' => ['Yes'], 'includeOtherOption' => true]);

        $this->assertSame('Other', $this->first()['values'][1]['label']);
    }

    /** @test */
    public function the_answer_describes_the_coding_in_one_line()
    {
        $this->question(1, 'one choice', ['options' => ['At home', 'At work'], 'includeOtherOption' => true, 'otherOptionLabel' => 'Somewhere else']);

        $this->assertSame('1 = At home; 2 = At work; text = Somewhere else, followed by the typed answer', $this->first()['answer']);
    }

    /** @test */
    public function a_slider_with_labels_numbers_its_steps_from_1()
    {
        $this->question(1, 'range values', ['options' => ['Not at all', 'Extremely']]);

        $this->assertSame([['value' => 1, 'label' => 'Not at all'], ['value' => 2, 'label' => 'Extremely']], $this->first()['values']);
    }

    /** @test */
    public function a_slider_gives_its_range()
    {
        $this->question(1, 'range', ['min' => 1, 'max' => 10, 'step' => 1]);

        $this->assertSame('From 1 to 10', $this->first()['answer']);
    }

    /** @test */
    public function a_slider_gives_its_step_when_it_is_not_1()
    {
        $this->question(1, 'range', ['min' => 0, 'max' => 60, 'step' => 5]);

        $this->assertSame('From 0 to 60 in steps of 5', $this->first()['answer']);
    }

    /** @test */
    public function a_number_gives_its_range()
    {
        $this->question(1, 'number', ['min' => 0, 'max' => 14, 'maxDigits' => 2]);

        $this->assertSame('A number from 0 to 14', $this->first()['answer']);
    }

    /** @test */
    public function a_text_only_item_is_never_required_and_gets_no_answer()
    {
        $this->question(1, 'display', [], mandatory: true);

        $this->assertSame([false, 'No answer (text only)'], [$this->first()['required'], $this->first()['answer']]);
    }

    /** @test */
    public function an_optional_question_is_not_required()
    {
        $this->question(1, 'textarea', [], mandatory: false);

        $this->assertFalse($this->first()['required']);
    }

    /** @test */
    public function a_question_shows_as_plain_text()
    {
        $this->question(1, 'text', [], text: 'How was<br>your <b>day</b>?');

        $this->assertSame('How was your day?', $this->first()['text']);
    }

    /** @test */
    public function an_edited_question_gives_its_version()
    {
        $this->question(1, 'text', [], version: 3);

        $this->assertSame(3, $this->first()['version']);
    }

    /** @test */
    public function questionnaires_and_questions_keep_their_order()
    {
        $second = $this->schedule(MartProject::where('main_project_id', $this->project->id)->first(), 2, 'Weekly');
        $this->question(2, 'text', [], text: 'Second');
        $this->question(1, 'text', [], text: 'First');
        $this->question(1, 'text', [], text: 'Weekly question', schedule: $second);

        $this->assertSame(
            [['Evening check-in', ['First', 'Second']], ['Weekly', ['Weekly question']]],
            array_map(fn ($questionnaire) => [$questionnaire['name'], array_column($questionnaire['questions'], 'text')], MartCodebook::forProject($this->project)),
        );
    }

    /** @test */
    public function the_page_gets_only_what_the_list_shows()
    {
        $this->question(1, 'one choice', ['options' => ['Yes'], 'jump' => ['1' => 3], 'randomizeAnswers' => true]);

        $response = $this->actingAs($this->user)->getJson("/projects/{$this->project->id}/questionnaires/codebook")->assertOk();

        $this->assertSame(['item', 'text', 'type', 'type_label', 'required', 'version', 'values', 'answer'], array_keys($response->json('questionnaires.0.questions.0')));
    }

    /** @test */
    public function an_invited_researcher_gets_the_list()
    {
        $invited = User::factory()->researcher()->create();
        $this->project->invited()->attach($invited->id);

        $this->actingAs($invited)->getJson("/projects/{$this->project->id}/questionnaires/codebook")->assertOk();
    }

    /** @test */
    public function someone_outside_the_project_gets_no_list()
    {
        $stranger = User::factory()->researcher()->create();

        $this->actingAs($stranger)->getJson("/projects/{$this->project->id}/questionnaires/codebook")->assertForbidden();
    }

    /** @test */
    public function a_metag_project_has_no_list()
    {
        $this->project->update(['inputs' => '[]']);

        $this->actingAs($this->user)->getJson("/projects/{$this->project->id}/questionnaires/codebook")->assertNotFound();
    }

    /** @test */
    public function the_export_ends_with_a_questions_sheet()
    {
        $sheets = (new MartProjectExport($this->project))->sheets();

        $this->assertSame('Questions', end($sheets)->title());
    }

    /** @test */
    public function the_questions_sheet_has_a_row_per_question_with_its_coding()
    {
        $this->question(1, 'range values', ['options' => ['Not at all', 'Extremely']], text: 'How stressed?', mandatory: true);

        $this->assertSame(
            [['Evening check-in', 1, 1, 'How stressed?', 'Slider with labels', 'yes', 1, '1 = Not at all; 2 = Extremely']],
            $this->questionsSheet()->array(),
        );
    }

    /** @test */
    public function the_questions_sheet_names_its_columns()
    {
        $this->assertSame(
            ['questionnaire', 'questionnaire_id', 'item', 'question', 'type', 'required', 'version', 'answer_values'],
            $this->questionsSheet()->headings(),
        );
    }

    /** @test */
    public function the_binder_keeps_text_starting_with_an_equals_sign_as_text()
    {
        $cell = (new Spreadsheet)->getActiveSheet()->getCell('A1');

        (new FormulaSafeValueBinder)->bindValue($cell, '=HYPERLINK("https://example.test","Open")');

        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
    }

    /** @test */
    public function the_binder_keeps_a_number_a_number()
    {
        $cell = (new Spreadsheet)->getActiveSheet()->getCell('A1');

        (new FormulaSafeValueBinder)->bindValue($cell, 3);

        $this->assertSame(DataType::TYPE_NUMERIC, $cell->getDataType());
    }

    private function questionsSheet(): MartQuestionsSheet
    {
        $sheets = (new MartProjectExport($this->project))->sheets();

        return end($sheets);
    }

    private function first(): array
    {
        return MartCodebook::forProject($this->project)[0]['questions'][0];
    }

    private function schedule(MartProject $martProject, int $questionnaireId, string $name): MartSchedule
    {
        return MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $questionnaireId,
            'name' => $name,
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);
    }

    private function question(int $position, string $type, array $config, string $text = 'A question', bool $mandatory = true, int $version = 1, ?MartSchedule $schedule = null): void
    {
        MartQuestion::create([
            'schedule_id' => ($schedule ?? $this->schedule)->id,
            'position' => $position,
            'text' => $text,
            'type' => $type,
            'config' => $config,
            'is_mandatory' => $mandatory,
            'version' => $version,
        ]);
    }
}
