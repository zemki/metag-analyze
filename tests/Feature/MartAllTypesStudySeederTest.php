<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Http\Controllers\MartApiController;
use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Http\Request;
use MartAllTypesStudySeeder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * MartAllTypesStudySeeder: one realistic study with every question type the
 * app answers, except uploads, and completed cases a researcher can consult.
 * The MART connection is rolled back too.
 */
class MartAllTypesStudySeederTest extends TestCase
{
    private const STUDY = 'Everyday Life & Wellbeing';

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    /** @test */
    public function the_study_asks_every_question_type_except_uploads()
    {
        $this->seedStudy();

        $this->assertEqualsCanonicalizing(
            ['display', 'range', 'range values', 'one choice', 'multiple choice', 'number', 'text', 'textarea'],
            $this->questions()->pluck('type')->unique()->values()->all(),
        );
    }

    /** @test */
    public function the_app_gets_each_question_with_its_own_scale()
    {
        $this->seedStudy();

        $structure = (new MartApiController)->getProjectStructure(Request::create('/test'), $this->study())->toArray(null);
        $structure = json_decode(json_encode($structure), true);
        $scaleTypes = collect($structure['scales'])->pluck('options.type', 'scaleId');
        $items = collect($structure['questionnaires'][0]['items']);

        $this->assertSame(
            ['display', 'range', 'rangeValues', 'radio', 'checkbox', 'number', 'radio', 'number', 'text', 'textarea', 'range', 'display'],
            $items->map(fn ($item) => $item['itemId'] === null ? 'display' : $scaleTypes[$item['scaleId']])->all(),
        );
    }

    /** @test */
    public function the_study_belongs_to_the_account_with_the_typed_email()
    {
        $researcher = User::factory()->researcher()->create();

        $this->runSeeder($researcher->email);

        $this->assertSame($researcher->id, $this->study()->created_by);
    }

    /** @test */
    public function the_participant_cases_belong_to_that_account_too()
    {
        $researcher = User::factory()->researcher()->create();

        $this->runSeeder($researcher->email);

        $this->assertSame([$researcher->id], $this->study()->cases()->pluck('user_id')->unique()->values()->all());
    }

    /** @test */
    public function it_stops_when_no_account_has_the_email()
    {
        $this->expectExceptionMessage('No account with the email nobody@example.test');

        $this->runSeeder('nobody@example.test');
    }

    /** @test */
    public function it_stops_without_an_email_when_it_cannot_ask()
    {
        $this->expectExceptionMessage('needs the email of an existing account');

        $this->runSeeder(null);
    }

    /** @test */
    public function every_participant_case_is_completed_so_it_can_be_consulted()
    {
        $this->seedStudy();

        $cases = $this->study()->cases()->get();

        $this->assertCount(6, $cases);
        $this->assertTrue($cases->every(fn ($case) => $case->getStatus() === CaseStatus::COMPLETED && $case->isAccessible()));
    }

    /** @test */
    public function every_answerable_question_gets_real_answers()
    {
        $this->seedStudy();

        $filled = $this->answers()
            ->reject(fn (MartAnswer $answer) => in_array($answer->answer_value, ['[]', '[""]'], true))
            ->pluck('question_uuid')
            ->unique();

        $this->assertEqualsCanonicalizing(
            $this->questions()->where('type', '!=', 'display')->pluck('uuid')->all(),
            $filled->values()->all(),
        );
    }

    /** @test */
    public function a_text_only_item_never_gets_an_answer_as_in_the_app()
    {
        $this->seedStudy();

        $displayUuids = $this->questions()->where('type', 'display')->pluck('uuid');

        $this->assertFalse($this->answers()->contains(fn (MartAnswer $answer) => $displayUuids->contains($answer->question_uuid)));
    }

    /** @test */
    public function some_participants_pick_other_and_type_their_own_answer()
    {
        $this->seedStudy();

        $choiceUuids = $this->questions()->whereIn('type', ['one choice', 'multiple choice'])->pluck('uuid');

        $this->assertTrue($this->answers()->contains(fn (MartAnswer $answer) => $choiceUuids->contains($answer->question_uuid)
            && in_array('text', json_decode($answer->answer_value, true), true)));
    }

    /** @test */
    public function each_participant_answers_at_most_once_an_evening()
    {
        $this->seedStudy();

        $perDay = $this->entries()->groupBy(fn (MartEntry $entry) => $entry->case_id . '@' . $entry->started_at->toDateString());

        $this->assertTrue($perDay->every(fn ($entries) => $entries->count() === 1));
    }

    /** @test */
    public function no_answer_is_in_the_future()
    {
        $this->seedStudy();

        $this->assertFalse($this->entries()->contains(fn (MartEntry $entry) => $entry->completed_at->isFuture()));
    }

    private function seedStudy(): void
    {
        $this->runSeeder($this->user->email);
    }

    /**
     * The seeder without a terminal, so it can't ask: the email comes from
     * $ownerEmail or not at all.
     */
    private function runSeeder(?string $email): void
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $command = new Command;
        $command->setOutput(new OutputStyle($input, new NullOutput));

        $seeder = new MartAllTypesStudySeeder;
        $seeder->ownerEmail = $email;
        $seeder->setContainer($this->app)->setCommand($command)->__invoke();
    }

    private function study(): Project
    {
        return Project::where('name', self::STUDY)->latest('id')->firstOrFail();
    }

    private function questions()
    {
        return MartQuestion::whereIn('schedule_id', MartSchedule::where('mart_project_id', $this->study()->martProject()->id)->pluck('id'))->get();
    }

    private function entries()
    {
        return MartEntry::whereIn('case_id', $this->study()->cases()->pluck('id'))->get();
    }

    private function answers()
    {
        return MartAnswer::whereIn('entry_id', $this->entries()->pluck('id'))->get();
    }
}
