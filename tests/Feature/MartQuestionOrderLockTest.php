<?php

namespace Tests\Feature;

use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The app numbers a questionnaire's items by position, and the submit maps
 * each answer back to a question by that position. A phone that loaded the
 * questionnaire before a reorder would send answers that land on the wrong
 * questions, so once a questionnaire has answers the questions route refuses
 * to move its saved questions, and new ones only go at the end. The MART
 * connection is rolled back too, so nothing stays behind.
 */
class MartQuestionOrderLockTest extends TestCase
{
    private const LOCKED_MESSAGE = 'This questionnaire already has answers, so its saved questions can\'t be moved: the app matches answers to questions by their position. Add new questions at the end.';

    private MartSchedule $schedule;

    private MartQuestion $first;

    private MartQuestion $second;

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

        $this->schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + 600,
            'name' => 'Order lock',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
        ]);

        $this->first = $this->savedQuestion(1, 'First question');
        $this->second = $this->savedQuestion(2, 'Second question');
    }

    /** @test */
    public function a_questionnaire_with_answers_refuses_a_reorder_of_its_saved_questions()
    {
        $this->addAnswers();

        $this->putQuestions([$this->sent($this->second), $this->sent($this->first)])
            ->assertStatus(422)
            ->assertJsonPath('message', self::LOCKED_MESSAGE);
    }

    /** @test */
    public function a_refused_reorder_leaves_the_question_order_as_it_was()
    {
        $this->addAnswers();

        $this->putQuestions([$this->sent($this->second), $this->sent($this->first)])->assertStatus(422);

        $this->assertSame([$this->first->uuid, $this->second->uuid], $this->questionOrder());
    }

    /** @test */
    public function a_questionnaire_with_answers_refuses_a_reorder_sent_with_uuids_in_upper_case()
    {
        $this->addAnswers();

        $this->putQuestions([
            $this->sent($this->second, ['uuid' => strtoupper($this->second->uuid)]),
            $this->sent($this->first, ['uuid' => strtoupper($this->first->uuid)]),
        ])->assertStatus(422)->assertJsonPath('message', self::LOCKED_MESSAGE);

        $this->assertSame([$this->first->uuid, $this->second->uuid], $this->questionOrder());
    }

    /**
     * Keys 1 then 0 make a JSON object, not a list. The positions follow the
     * order the questions arrive in, the same order the guard checks.
     *
     * @test
     */
    public function questions_sent_as_an_object_are_saved_in_the_order_they_arrive()
    {
        $this->addAnswers();

        $this->putQuestions([1 => $this->sent($this->first), 0 => $this->sent($this->second)])->assertOk();

        $this->assertSame(
            [$this->first->uuid => 1, $this->second->uuid => 2],
            $this->schedule->questions()->pluck('position', 'uuid')->all(),
        );
    }

    /**
     * The guard runs before anything is written, so the name and text sent
     * with the refused order are not saved either.
     *
     * @test
     */
    public function a_refused_reorder_saves_none_of_the_other_changes_sent_with_it()
    {
        $this->addAnswers();

        $this->putQuestions(
            [$this->sent($this->second, ['text' => 'Changed text']), $this->sent($this->first)],
            ['name' => 'Renamed'],
        )->assertStatus(422);

        $this->assertSame(
            ['Order lock', 'Second question', 1],
            [$this->schedule->fresh()->name, $this->second->fresh()->text, $this->second->fresh()->version],
        );
    }

    /** @test */
    public function a_questionnaire_with_answers_refuses_a_new_question_before_a_saved_one()
    {
        $this->addAnswers();

        $this->putQuestions([$this->sent($this->first), $this->newQuestion(), $this->sent($this->second)])
            ->assertStatus(422)
            ->assertJsonPath('message', self::LOCKED_MESSAGE);
    }

    /** @test */
    public function a_refused_new_question_before_a_saved_one_is_not_added()
    {
        $this->addAnswers();

        $this->putQuestions([$this->newQuestion(), $this->sent($this->first), $this->sent($this->second)])->assertStatus(422);

        $this->assertSame([$this->first->uuid, $this->second->uuid], $this->questionOrder());
    }

    /** @test */
    public function a_questionnaire_with_answers_takes_a_new_question_at_the_end()
    {
        $this->addAnswers();

        $this->putQuestions([$this->sent($this->first), $this->sent($this->second), $this->newQuestion()])
            ->assertOk();

        $this->assertSame(
            ['First question', 'Second question', 'New question'],
            $this->schedule->questions()->pluck('text')->all(),
        );
    }

    /** @test */
    public function a_questionnaire_with_answers_still_deletes_a_question()
    {
        $this->addAnswers();

        $this->putQuestions([$this->sent($this->second)])->assertOk();

        $this->assertSame([$this->second->uuid], $this->questionOrder());
    }

    /** @test */
    public function a_questionnaire_with_answers_takes_edits_that_move_no_question()
    {
        $this->addAnswers();

        $this->putQuestions([
            $this->sent($this->first, ['text' => 'First, reworded', 'config' => ['placeholder' => 'Type here']]),
            $this->sent($this->second),
        ])->assertOk();

        $first = $this->first->fresh();
        $this->assertSame(['First, reworded', ['placeholder' => 'Type here']], [$first->text, $first->config]);
    }

    /** @test */
    public function a_questionnaire_without_answers_takes_a_reorder_of_its_questions()
    {
        $this->putQuestions([$this->sent($this->second), $this->sent($this->first)])->assertOk();

        $this->assertSame(
            [$this->second->uuid => 1, $this->first->uuid => 2],
            $this->schedule->questions()->pluck('position', 'uuid')->all(),
        );
    }

    /** @test */
    public function the_questionnaire_list_says_a_questionnaire_with_answers_has_entries()
    {
        $this->addAnswers();

        $this->assertTrue($this->listedSchedule()['has_entries']);
    }

    /** @test */
    public function the_questionnaire_list_says_a_questionnaire_without_answers_has_no_entries()
    {
        $this->assertFalse($this->listedSchedule()['has_entries']);
    }

    private function savedQuestion(int $position, string $text): MartQuestion
    {
        return MartQuestion::create([
            'schedule_id' => $this->schedule->id,
            'position' => $position,
            'text' => $text,
            'type' => 'text',
            'config' => [],
            'is_mandatory' => true,
            'version' => 1,
        ]);
    }

    /**
     * One submit from the test case, as the submit route writes it: a
     * mart_entries row without a main entry.
     */
    private function addAnswers(): void
    {
        MartEntry::create([
            'case_id' => $this->case->id,
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => $this->schedule->questionnaire_id,
            'participant_id' => $this->case->name,
            'user_id' => $this->user->email,
            'started_at' => '2026-05-01 10:00:00',
            'completed_at' => '2026-05-01 10:05:00',
            'duration_ms' => 300000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => 1777629600000,
        ]);
    }

    /** A saved question as the editor sends it back. */
    private function sent(MartQuestion $question, array $changes = []): array
    {
        return [
            'uuid' => $question->uuid,
            'text' => $question->text,
            'type' => $question->type,
            'mandatory' => $question->is_mandatory,
            'config' => $question->config,
            ...$changes,
        ];
    }

    private function newQuestion(): array
    {
        return ['text' => 'New question', 'type' => 'text', 'mandatory' => true, 'config' => []];
    }

    private function putQuestions(array $questions, array $fields = []): TestResponse
    {
        return $this->actingAs($this->user)
            ->putJson("/questionnaires/{$this->schedule->id}/questions", [...$fields, 'questions' => $questions]);
    }

    /** The schedule's question uuids in position order. */
    private function questionOrder(): array
    {
        return $this->schedule->questions()->pluck('uuid')->all();
    }

    private function listedSchedule(): array
    {
        $questionnaires = $this->actingAs($this->user)
            ->getJson("/projects/{$this->project->id}/questionnaires")
            ->assertOk()
            ->json('questionnaires');

        return collect($questionnaires)->firstWhere('id', $this->schedule->id);
    }
}
