<?php

namespace Tests\Feature;

use App\Http\Controllers\MartApiController;
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
