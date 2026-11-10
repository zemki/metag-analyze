<?php

namespace Tests\Feature;

use App\Http\Controllers\MartApiController;
use App\Http\Resources\Mart\ScaleResource;
use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * What the app reads from the structure payload, and how its answers come back.
 * Positions start at 1 here because that is what the questionnaire editor saves.
 */
class MartScalePayloadTest extends TestCase
{
    protected MartProject $martProject;

    protected int $nextQuestionnaireId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update([
            'inputs' => json_encode([[
                'type' => 'mart',
                'questionnaireName' => 'Test Questionnaire',
                'projectOptions' => [],
            ]]),
        ]);

        // The MART DB doesn't roll back between tests.
        $this->martProject = MartProject::firstOrCreate(['main_project_id' => $this->project->id]);
        MartSchedule::where('mart_project_id', $this->martProject->id)->delete();

        $this->nextQuestionnaireId = $this->project->id * 1000 + 1;

        $this->case->update(['name' => 'Participant_001']);
    }

    /** @test */
    public function scale_ids_are_unique_across_questionnaires()
    {
        $this->addQuestionnaire([
            ['type' => 'number', 'config' => ['min' => 1, 'max' => 5]],
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]],
        ]);
        $this->addQuestionnaire([
            ['type' => 'number', 'config' => ['min' => 1, 'max' => 9]],
            ['type' => 'one choice', 'config' => ['options' => ['Often', 'Rarely']]],
        ]);

        $scaleIds = array_column($this->structure()['scales'], 'scaleId');

        $this->assertCount(4, $scaleIds);
        $this->assertCount(4, array_unique($scaleIds));
    }

    /** @test */
    public function each_item_finds_its_own_scale_with_the_apps_lookup()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]],
            ['type' => 'number', 'config' => ['min' => 1, 'max' => 5]],
        ]);
        $this->addQuestionnaire([
            ['type' => 'text', 'config' => []],
            ['type' => 'one choice', 'config' => ['options' => ['Often', 'Rarely']]],
        ]);

        $structure = $this->structure();
        $first = $structure['questionnaires'][0]['items'];
        $second = $structure['questionnaires'][1]['items'];

        $this->assertSame('radio', $this->appScaleFor($first[0], $structure)['type']);
        $this->assertSame('Yes', $this->appScaleFor($first[0], $structure)['radioOptions'][0]['text']);
        $this->assertSame('number', $this->appScaleFor($first[1], $structure)['type']);
        $this->assertSame('text', $this->appScaleFor($second[0], $structure)['type']);
        $this->assertSame('Often', $this->appScaleFor($second[1], $structure)['radioOptions'][0]['text']);
    }

    /** @test */
    public function identical_scales_share_one_id()
    {
        $sameScale = ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]];

        $this->addQuestionnaire([$sameScale, $sameScale]);
        $this->addQuestionnaire([$sameScale]);

        $structure = $this->structure();
        $first = $structure['questionnaires'][0]['items'];
        $second = $structure['questionnaires'][1]['items'];

        $this->assertCount(1, $structure['scales']);
        $this->assertSame($first[0]['scaleId'], $first[1]['scaleId']);
        $this->assertSame($first[0]['scaleId'], $second[0]['scaleId']);
    }

    /** @test */
    public function a_different_jump_keeps_two_scales_apart()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]],
            ['type' => 'one choice', 'config' => [
                'options' => ['Yes', 'No'],
                'jump' => ['jumpCondition' => 1, 'jumpOver' => 1],
            ]],
        ]);

        $structure = $this->structure();
        $items = $structure['questionnaires'][0]['items'];

        $this->assertCount(2, $structure['scales']);
        $this->assertNotSame($items[0]['scaleId'], $items[1]['scaleId']);
        $this->assertArrayHasKey('jump', $this->appScaleFor($items[1], $structure));
    }

    /** @test */
    public function display_items_have_no_scale_and_keep_the_numbering_of_later_items()
    {
        $this->addQuestionnaire([
            ['type' => 'text', 'config' => []],
            ['type' => 'display', 'config' => []],
            ['type' => 'number', 'config' => ['min' => 1, 'max' => 5]],
        ]);

        $structure = $this->structure();
        $items = $structure['questionnaires'][0]['items'];

        $this->assertCount(2, $structure['scales']);
        $this->assertNull($items[1]['itemId']);
        $this->assertNull($items[1]['scaleId']);
        $this->assertSame(3, $items[2]['itemId']);
        $this->assertSame('number', $this->appScaleFor($items[2], $structure)['type']);
    }

    /** @test */
    public function item_ids_restart_in_each_questionnaire()
    {
        $this->addQuestionnaire([['type' => 'text', 'config' => []]]);
        $this->addQuestionnaire([['type' => 'textarea', 'config' => []]]);

        $questionnaires = $this->structure()['questionnaires'];

        $this->assertSame(1, $questionnaires[0]['items'][0]['itemId']);
        $this->assertSame(1, $questionnaires[1]['items'][0]['itemId']);
    }

    /** @test */
    public function a_scale_has_only_a_scale_id_and_options()
    {
        $this->addQuestionnaire([['type' => 'text', 'config' => []]]);

        $this->assertSame(['scaleId', 'options'], array_keys($this->structure()['scales'][0]));
    }

    /** @test */
    public function each_answer_is_stored_on_its_own_question()
    {
        $schedule = $this->addQuestionnaire([
            ['type' => 'text', 'text' => 'First', 'config' => []],
            ['type' => 'display', 'text' => 'Read this', 'config' => []],
            ['type' => 'text', 'text' => 'Third', 'config' => []],
        ]);

        $this->submit($schedule, ['1' => ['first answer'], '3' => ['third answer']]);

        $this->assertSame(
            ['First' => '["first answer"]', 'Third' => '["third answer"]'],
            $this->storedAnswers($schedule),
        );
    }

    /** @test */
    public function a_question_in_a_later_questionnaire_gets_its_own_answer()
    {
        $this->addQuestionnaire([['type' => 'text', 'text' => 'Other questionnaire', 'config' => []]]);
        $schedule = $this->addQuestionnaire([
            ['type' => 'number', 'text' => 'How many', 'config' => ['min' => 1, 'max' => 5]],
        ]);

        $this->submit($schedule, ['1' => [4]]);

        $this->assertSame(['How many' => '[4]'], $this->storedAnswers($schedule));
    }

    /** @test */
    public function an_other_option_on_a_radio_is_a_text_option_plus_text_options()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => [
                'options' => ['Yes', 'No'],
                'includeOtherOption' => true,
                'otherOptionLabel' => 'Sonstiges',
            ]],
        ]);

        $this->assertSame([
            'type' => 'radio',
            'radioOptions' => [
                ['value' => 1, 'text' => 'Yes'],
                ['value' => 2, 'text' => 'No'],
                ['value' => 'text', 'text' => 'Sonstiges'],
            ],
            'textOptions' => ['type' => 'text'],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function an_other_option_on_a_checkbox_is_a_text_option_plus_text_options()
    {
        $this->addQuestionnaire([
            ['type' => 'multiple choice', 'config' => [
                'options' => ['Yes', 'No'],
                'includeOtherOption' => true,
                'otherOptionLabel' => 'Sonstiges',
            ]],
        ]);

        $this->assertSame([
            'type' => 'checkbox',
            'checkboxOptions' => [
                ['value' => 1, 'text' => 'Yes'],
                ['value' => 2, 'text' => 'No'],
                ['value' => 'text', 'text' => 'Sonstiges'],
            ],
            'textOptions' => ['type' => 'text'],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_multi_line_other_field_keeps_the_value_text()
    {
        $this->addQuestionnaire([
            ['type' => 'multiple choice', 'config' => [
                'options' => ['Yes', 'No'],
                'includeOtherOption' => true,
                'otherTextType' => 'textarea',
            ]],
        ]);

        $options = $this->structure()['scales'][0]['options'];

        $this->assertSame('textarea', $options['textOptions']['type']);
        $this->assertSame('text', $options['checkboxOptions'][2]['value']);
    }

    /** @test */
    public function an_other_option_without_a_label_is_called_other()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => [
                'options' => ['Yes', 'No'],
                'includeOtherOption' => true,
            ]],
        ]);

        $this->assertSame('Other', $this->structure()['scales'][0]['options']['radioOptions'][2]['text']);
    }

    /** @test */
    public function a_choice_without_an_other_option_has_no_text_options()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]],
        ]);

        $options = $this->structure()['scales'][0]['options'];

        $this->assertArrayNotHasKey('textOptions', $options);
        $this->assertCount(2, $options['radioOptions']);
    }

    /** @test */
    public function a_text_placeholder_goes_in_text_options()
    {
        $this->addQuestionnaire([
            ['type' => 'text', 'config' => ['placeholder' => 'Your answer']],
        ]);

        $this->assertSame([
            'type' => 'text',
            'textOptions' => ['type' => 'text', 'placeholder' => 'Your answer'],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_textarea_placeholder_goes_in_text_options()
    {
        $this->addQuestionnaire([
            ['type' => 'textarea', 'config' => ['placeholder' => 'Your answer']],
        ]);

        $this->assertSame([
            'type' => 'textarea',
            'textOptions' => ['type' => 'textarea', 'placeholder' => 'Your answer'],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_text_scale_without_a_placeholder_has_no_text_options()
    {
        $this->addQuestionnaire([['type' => 'text', 'config' => []]]);

        $this->assertSame(['type' => 'text'], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_range_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'range', 'config' => ['min' => 1, 'max' => 100, 'defaultValue' => 55]],
        ]);

        $this->assertSame(55, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_range_default_stays_in_range_options_as_well()
    {
        $this->addQuestionnaire([
            ['type' => 'range', 'config' => ['min' => 1, 'max' => 100, 'defaultValue' => 55]],
        ]);

        $this->assertSame(55, $this->structure()['scales'][0]['options']['rangeOptions']['defaultValue']);
    }

    /** @test */
    public function a_range_without_a_default_has_none_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'range', 'config' => ['min' => 1, 'max' => 100]],
        ]);

        $this->assertArrayNotHasKey('defaultValue', $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_number_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'number', 'config' => ['min' => 1, 'max' => 9, 'defaultValue' => 4]],
        ]);

        $this->assertSame(4, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_radio_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No'], 'defaultValue' => 1]],
        ]);

        $this->assertSame(2, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_checkbox_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'multiple choice', 'config' => ['options' => ['Yes', 'No'], 'defaultValue' => 1]],
        ]);

        $this->assertSame(2, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_text_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'text', 'config' => ['defaultValue' => 'Nothing yet']],
        ]);

        $this->assertSame('Nothing yet', $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_textarea_default_goes_at_the_top_of_the_options()
    {
        $this->addQuestionnaire([
            ['type' => 'textarea', 'config' => ['defaultValue' => 'Nothing yet']],
        ]);

        $this->assertSame('Nothing yet', $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_default_of_zero_is_still_sent()
    {
        $this->addQuestionnaire([
            ['type' => 'range', 'config' => ['min' => 0, 'max' => 10, 'defaultValue' => 0]],
        ]);

        $this->assertSame(0, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_different_default_keeps_two_scales_apart()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No'], 'defaultValue' => 1]],
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No']]],
        ]);

        $this->assertCount(2, $this->structure()['scales']);
    }

    /** @test */
    public function a_jump_condition_saved_as_a_string_is_sent_as_a_number()
    {
        // The editor saves a string, and the app compares with === against a number.
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => [
                'options' => ['Yes', 'No'],
                'jump' => ['jumpCondition' => '1', 'jumpOver' => 2],
            ]],
        ]);

        $this->assertSame(
            ['jumpCondition' => 2, 'jumpOver' => 2],
            $this->structure()['scales'][0]['options']['jump'],
        );
    }

    /** @test */
    public function single_choice_values_start_at_1()
    {
        $this->addQuestionnaire([
            ['type' => 'one choice', 'config' => ['options' => ['Yes', 'No', 'Maybe']]],
        ]);

        $options = $this->structure()['scales'][0]['options']['radioOptions'];

        $this->assertSame([1, 2, 3], array_column($options, 'value'));
    }

    /** @test */
    public function multiple_choice_values_start_at_1()
    {
        $this->addQuestionnaire([
            ['type' => 'multiple choice', 'config' => ['options' => ['Yes', 'No', 'Maybe']]],
        ]);

        $options = $this->structure()['scales'][0]['options']['checkboxOptions'];

        $this->assertSame([1, 2, 3], array_column($options, 'value'));
    }

    /** @test */
    public function the_first_single_choice_option_is_saved_when_picked()
    {
        $schedule = $this->addQuestionnaire([
            ['type' => 'one choice', 'text' => 'Pick one', 'config' => ['options' => ['Yes', 'No']]],
        ]);

        $firstOption = $this->structure()['scales'][0]['options']['radioOptions'][0];

        $this->submit($schedule, ['1' => $this->appRadioAnswer($firstOption['value'])]);

        $this->assertSame(['Pick one' => '[1]'], $this->storedAnswers($schedule));
    }

    /** @test */
    public function a_jump_fires_on_its_own_option_and_no_other()
    {
        // The editor saves option N as the string N - 1.
        $this->addQuestionnaire(array_map(fn ($key) => ['type' => 'one choice', 'config' => [
            'options' => ['Yes', 'No', 'Maybe'],
            'jump' => ['jumpCondition' => (string) $key, 'jumpOver' => 1],
        ]], [0, 1, 2]));

        $optionsThatJump = array_map(fn ($scale) => array_keys(array_filter(
            $scale['options']['radioOptions'],
            fn ($option) => $this->appJumps($option['value'], $scale['options']['jump']),
        )), $this->structure()['scales']);

        $this->assertSame([[0], [1], [2]], $optionsThatJump);
    }

    /** @test */
    public function a_radio_default_selects_its_own_option()
    {
        $this->assertSame(
            [['Yes'], ['No'], ['Maybe']],
            $this->optionsSelectedByDefault('one choice', 'radioOptions'),
        );
    }

    /** @test */
    public function a_checkbox_default_selects_its_own_option()
    {
        $this->assertSame(
            [['Yes'], ['No'], ['Maybe']],
            $this->optionsSelectedByDefault('multiple choice', 'checkboxOptions'),
        );
    }

    /** @test */
    public function range_values_labels_are_numbered_from_1()
    {
        $this->addQuestionnaire([
            ['type' => 'range values', 'config' => ['options' => ['sehr schlecht', 'ok', 'sehr gut']]],
        ]);

        $this->assertSame([
            'type' => 'rangeValues',
            'rangeValueOptions' => [
                ['value' => 1, 'text' => 'sehr schlecht'],
                ['value' => 2, 'text' => 'ok'],
                ['value' => 3, 'text' => 'sehr gut'],
            ],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function a_range_values_default_is_the_value_of_its_option()
    {
        $this->addQuestionnaire([
            ['type' => 'range values', 'config' => ['options' => ['Low', 'Mid', 'High'], 'defaultValue' => 1]],
        ]);

        $this->assertSame(2, $this->structure()['scales'][0]['options']['defaultValue']);
    }

    /** @test */
    public function a_range_values_scale_without_a_default_has_none()
    {
        $this->addQuestionnaire([
            ['type' => 'range values', 'config' => ['options' => ['Low', 'High']]],
        ]);

        $this->assertSame(['type', 'rangeValueOptions'], array_keys($this->structure()['scales'][0]['options']));
    }

    /** @test */
    public function a_range_values_default_shows_its_own_label()
    {
        $this->addQuestionnaire(array_map(fn ($key) => ['type' => 'range values', 'config' => [
            'options' => ['Low', 'Mid', 'High'],
            'defaultValue' => $key,
        ]], [0, 1, 2]));

        $this->assertSame(
            ['Low', 'Mid', 'High'],
            array_map(fn ($scale) => $this->appRangeValuesLabel($scale['options']), $this->structure()['scales']),
        );
    }

    /**
     * The app reads only maxFileSize and allowedFormats. martTypes.ts also
     * names a fileType, which the app never reads, so it isn't sent.
     *
     * @test
     */
    public function a_file_upload_sends_its_size_and_formats()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => ['.jpg', '.pdf']]]],
        ]);

        $this->assertSame([
            'type' => 'fileUpload',
            'fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => ['.jpg', '.pdf']],
        ], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function file_upload_formats_are_sent_lowercase_with_a_leading_dot_and_once()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['allowedFormats' => [' JPG', 'pdf', '.Pdf', '.mp4 ']]]],
        ]);

        $this->assertSame(
            ['.jpg', '.pdf', '.mp4'],
            $this->structure()['scales'][0]['options']['fileUploadOptions']['allowedFormats'],
        );
    }

    /** @test */
    public function a_file_upload_leaves_out_options_without_a_value()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['maxFileSize' => null, 'allowedFormats' => ['.pdf']]]],
        ]);

        $this->assertSame(['allowedFormats' => ['.pdf']], $this->structure()['scales'][0]['options']['fileUploadOptions']);
    }

    /** @test */
    public function a_file_upload_size_saved_as_text_is_sent_as_a_number()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['maxFileSize' => '20', 'allowedFormats' => ['.pdf']]]],
        ]);

        $this->assertSame(20, $this->structure()['scales'][0]['options']['fileUploadOptions']['maxFileSize']);
    }

    /** @test */
    public function a_bare_file_upload_has_only_its_type()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['maxFileSize' => null, 'allowedFormats' => []]]],
        ]);

        $this->assertSame(['type' => 'fileUpload'], $this->structure()['scales'][0]['options']);
    }

    /** @test */
    public function the_app_takes_only_files_with_a_listed_extension_in_any_case()
    {
        $this->addQuestionnaire([
            ['type' => 'file', 'config' => ['fileUploadOptions' => ['allowedFormats' => ['PDF']]]],
        ]);
        $options = $this->structure()['scales'][0]['options'];

        $this->assertSame(
            [true, false],
            [$this->appAllowsFile($options, 'Scan.Final.PDF'), $this->appAllowsFile($options, 'notes.txt')],
        );
    }

    /** @test */
    public function metag_single_choice_values_stay_as_they_are()
    {
        $options = $this->metagScaleOptions('one choice')['radioOptions'];

        $this->assertSame([0, 1], array_column($options, 'value'));
    }

    /** @test */
    public function metag_multiple_choice_values_stay_as_they_are()
    {
        $options = $this->metagScaleOptions('multiple choice')['checkboxOptions'];

        $this->assertSame([0, 1], array_column($options, 'value'));
    }

    private function metagScaleOptions(string $type): array
    {
        $input = (object) ['type' => $type, 'answers' => ['Yes', 'No']];

        return (new ScaleResource($input, 0, false))->scaleOptions();
    }

    /**
     * One scale per option, with that option as its default, and the options
     * the app selects for each. The app skips a default that is falsy.
     */
    private function optionsSelectedByDefault(string $type, string $optionsKey): array
    {
        $this->addQuestionnaire(array_map(fn ($key) => ['type' => $type, 'config' => [
            'options' => ['Yes', 'No', 'Maybe'],
            'defaultValue' => $key,
        ]], [0, 1, 2]));

        return array_map(function ($scale) use ($optionsKey) {
            $options = $scale['options'];
            $default = $options['defaultValue'];

            return array_column(array_filter(
                $options[$optionsKey],
                fn ($option) => $default && $option['value'] === $default,
            ), 'text');
        }, $this->structure()['scales']);
    }

    /**
     * How the app builds a single-choice answer: a falsy value is dropped.
     */
    private function appRadioAnswer(int|string $value): array
    {
        return $value ? [$value] : [];
    }

    /**
     * The label the app shows for a range values default: it sets the answer
     * to Number(defaultValue) when that is truthy, then shows
     * rangeValueOptions[answer - 1].text.
     */
    private function appRangeValuesLabel(array $options): ?string
    {
        $answer = ! empty($options['defaultValue']) ? (int) $options['defaultValue'] : null;

        return $answer ? ($options['rangeValueOptions'][$answer - 1]['text'] ?? null) : null;
    }

    /**
     * The app's file check: no allowedFormats means any file, otherwise
     * "." + the lowercased last extension of the name must be listed.
     */
    private function appAllowsFile(array $options, string $fileName): bool
    {
        $formats = $options['fileUploadOptions']['allowedFormats'] ?? [];
        $parts = explode('.', $fileName);

        return $formats === [] || in_array('.' . strtolower(end($parts)), $formats, true);
    }

    /**
     * The app's jump check: answer.radio && answer.radio === jump.jumpCondition.
     */
    private function appJumps(int|string $radio, array $jump): bool
    {
        return $radio && $radio === $jump['jumpCondition'];
    }

    private function addQuestionnaire(array $questions): MartSchedule
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => $this->martProject->id,
            'questionnaire_id' => $this->nextQuestionnaireId++,
            'name' => 'Questionnaire ' . $this->nextQuestionnaireId,
            'type' => 'single',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'end_date_time' => ['date' => '2099-12-31', 'time' => '21:00'],
            ],
            'notification_config' => [],
        ]);

        foreach ($questions as $index => $question) {
            MartQuestion::create([
                'schedule_id' => $schedule->id,
                'position' => $index + 1,
                'text' => $question['text'] ?? 'Question ' . ($index + 1),
                'type' => $question['type'],
                'config' => $question['config'],
                'is_mandatory' => false,
                'version' => 1,
            ]);
        }

        return $schedule;
    }

    private function structure(): array
    {
        $request = Request::create('/test', 'GET');
        $resource = (new MartApiController)->getProjectStructure($request, $this->project);

        return json_decode(json_encode($resource->toArray($request)), true);
    }

    /**
     * The app's own lookup: scales.find(s => s.scaleId === item.scaleId) over
     * the whole list, then it reads the scale's options.
     */
    private function appScaleFor(array $item, array $structure): array
    {
        foreach ($structure['scales'] as $scale) {
            if ($scale['scaleId'] === $item['scaleId']) {
                return $scale['options'];
            }
        }

        $this->fail("No scale with id {$item['scaleId']}");
    }

    private function submit(MartSchedule $schedule, array $answers): void
    {
        $request = new Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $schedule->questionnaire_id,
            'userId' => 'test@example.com',
            'participantId' => 'Participant_001',
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => $answers,
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $response = (new MartApiController)->submitEntry($request, $this->case);

        $this->assertTrue($response->getData(true)['success']);
    }

    /**
     * Stored answers of a questionnaire as question text => raw value.
     */
    private function storedAnswers(MartSchedule $schedule): array
    {
        $entryIds = MartEntry::where('schedule_id', $schedule->id)->pluck('id');
        $textByUuid = MartQuestion::where('schedule_id', $schedule->id)->pluck('text', 'uuid');

        return MartAnswer::whereIn('entry_id', $entryIds)
            ->get()
            ->mapWithKeys(fn ($answer) => [$textByUuid[$answer->question_uuid] => $answer->answer_value])
            ->sortKeys()
            ->all();
    }
}
