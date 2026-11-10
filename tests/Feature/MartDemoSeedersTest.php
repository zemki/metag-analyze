<?php

namespace Tests\Feature;

use App\Http\Controllers\MartApiController;
use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use MartProjectSeeder;
use MartReferenceProjectSeeder;
use StagingMartSeeder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * StagingMartSeeder and MartProjectSeeder, each run once without a terminal.
 * The MART connection is rolled back here too.
 */
class MartDemoSeedersTest extends TestCase
{
    private const EDITOR_TYPES = ['number', 'range', 'range values', 'text', 'textarea', 'one choice', 'multiple choice', 'display', 'photo', 'audio', 'video'];

    /** The scale type the app gets for each question type. */
    private const APP_SCALE_TYPES = [
        'number' => 'number', 'range' => 'range', 'range values' => 'rangeValues', 'text' => 'text', 'textarea' => 'textarea',
        'one choice' => 'radio', 'multiple choice' => 'checkbox',
        'photo' => 'photoUpload', 'audio' => 'audioUpload', 'video' => 'videoUpload',
    ];

    private const DEMO_PROJECTS = ['Daily Wellbeing Tracker', 'Workplace Stress Research', 'Social Media & Mood Study', 'ESM Demo - Consultable Entries'];

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    /** @test */
    public function staging_builds_project_1_for_each_account()
    {
        $this->runStaging([['email' => 'tester@example.test', 'projects' => ['Staging A']]]);

        $tester = User::where('email', 'tester@example.test')->firstOrFail();
        $project = Project::where('name', 'Staging A')->firstOrFail();

        $this->assertSame($tester->id, $project->created_by);
        $this->assertSame(
            count(MartReferenceProjectSeeder::fixture('questionnaires')),
            MartSchedule::where('mart_project_id', $project->martProject()->id)->count(),
        );
        $this->assertTrue($project->cases()->where('user_id', $tester->id)->exists());
    }

    /** @test */
    public function staging_repeating_questionnaires_are_open_today()
    {
        $repeating = $this->stagingProjectOptions()['repeatingQuestionnaires'];

        $this->assertNotEmpty($repeating);
        foreach ($repeating as $questionnaire) {
            $this->assertOpenToday($questionnaire, "Questionnaire {$questionnaire['questionnaireId']}");
        }
    }

    /** @test */
    public function a_staging_project_is_open_today()
    {
        $this->assertOpenToday($this->stagingProjectOptions(), 'The project');
    }

    /** @test */
    public function staging_questionnaires_keep_the_length_directus_gives_them()
    {
        $directus = collect(MartReferenceProjectSeeder::fixture('project_options')['options']['repeatingQuestionnaires'])
            ->mapWithKeys(fn ($questionnaire) => [$questionnaire['questionnaireId'] => $this->days(
                $questionnaire['startDateAndTime']['date'],
                $questionnaire['endDateAndTime']['date'],
            )])->all();

        $staging = collect($this->stagingProjectOptions()['repeatingQuestionnaires'])
            ->mapWithKeys(fn ($questionnaire) => [$questionnaire['questionnaireId'] => $this->days(
                $questionnaire['startDateAndTime']['date'],
                $questionnaire['endDateAndTime']['date'],
            )])->all();

        $this->assertSame($directus, $staging);
    }

    /** @test */
    public function staging_gives_a_new_account_a_random_password()
    {
        $this->runStaging([['email' => 'tester@example.test', 'projects' => ['Staging A']]]);

        $this->assertFalse(Hash::check('password', User::where('email', 'tester@example.test')->first()->password));
    }

    /** @test */
    public function staging_keeps_an_existing_accounts_password_and_roles()
    {
        $admin = Role::firstOrCreate(['name' => 'admin'], ['description' => 'admin']);
        $user = User::create(['email' => 'tester@example.test', 'password' => bcrypt('kept-password')]);
        $user->roles()->attach($admin->id);

        $this->runStaging([['email' => 'tester@example.test', 'password' => 'new-password', 'projects' => ['Staging A']]]);

        $user->refresh();
        $this->assertTrue(Hash::check('kept-password', $user->password));
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'researcher')->exists());
    }

    /** @test */
    public function staging_stops_when_it_has_no_accounts_and_cannot_ask()
    {
        $usersBefore = User::count();

        try {
            $this->runSeeder(new StagingMartSeeder);
            $this->fail('StagingMartSeeder ran without accounts');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('terminal', $e->getMessage());
        }

        $this->assertSame($usersBefore, User::count());
    }

    /** @test */
    public function demo_questions_use_only_types_and_keys_the_editor_saves()
    {
        $this->runDemo();

        $questions = MartQuestion::whereIn('schedule_id', $this->demoScheduleIds())->get();

        $this->assertNotEmpty($questions);
        $this->assertEmpty($questions->pluck('type')->unique()->diff(self::EDITOR_TYPES)->all());
        $this->assertTrue($questions->every(fn ($question) => ! isset($question->config['martMetadata'])
            && ! isset($question->config['minValue'])
            && ! isset($question->config['originalType'])));
    }

    /** @test */
    public function demo_structures_send_each_question_as_its_own_scale_type()
    {
        $this->runDemo();

        foreach (self::DEMO_PROJECTS as $name) {
            $project = Project::where('name', $name)->latest('id')->firstOrFail();
            $structure = (new MartApiController)->getProjectStructure(Request::create('/test'), $project)->toArray(null);
            $structure = json_decode(json_encode($structure), true);
            $scaleTypes = collect($structure['scales'])->pluck('options.type', 'scaleId');

            $sent = collect($structure['questionnaires'])->flatMap(fn ($questionnaire) => collect($questionnaire['items'])
                ->map(fn ($item) => [$item['text'], $scaleTypes[$item['scaleId']] ?? null]))->all();
            $expected = MartQuestion::whereIn('schedule_id', $this->demoScheduleIds($project))
                ->join('mart_schedules', 'mart_schedules.id', '=', 'mart_questions.schedule_id')
                ->orderBy('mart_schedules.questionnaire_id')->orderBy('position')
                ->get(['text', 'mart_questions.type'])
                ->map(fn ($question) => [$question->text, self::APP_SCALE_TYPES[$question->type] ?? "unknown type {$question->type}"])
                ->all();

            $this->assertSame($expected, $sent, $name);
        }
    }

    /** @test */
    public function demo_single_choice_stays_single_choice()
    {
        $this->runDemo();

        $this->assertSame(
            ['one choice'],
            MartQuestion::whereIn('schedule_id', $this->demoScheduleIds())
                ->where('text', 'How are you feeling right now?')
                ->pluck('type')->unique()->values()->all(),
        );
    }

    /** @test */
    public function demo_projects_keep_their_questions_out_of_the_project_row()
    {
        $this->runDemo();

        foreach (self::DEMO_PROJECTS as $name) {
            $inputs = json_decode(Project::where('name', $name)->latest('id')->first()->inputs, true);

            $this->assertSame(['mart'], array_column($inputs, 'type'), $name);
        }
    }

    /** @test */
    public function demo_answers_are_option_values_from_1_and_never_0()
    {
        $this->runDemo();

        $questions = MartQuestion::whereIn('schedule_id', $this->demoScheduleIds())->get()->keyBy('uuid');
        $entryIds = MartEntry::whereIn('schedule_id', $this->demoScheduleIds())->pluck('id');
        $answers = MartAnswer::whereIn('entry_id', $entryIds)->get();

        $this->assertNotEmpty($answers);
        foreach ($answers as $answer) {
            $question = $questions[$answer->question_uuid];
            $values = json_decode($answer->answer_value, true);

            $this->assertIsArray($values, $answer->answer_value);
            if (in_array($question->type, ['one choice', 'multiple choice', 'range'], true)) {
                foreach ($values as $value) {
                    $this->assertIsInt($value, "{$question->text}: {$answer->answer_value}");
                    $this->assertGreaterThanOrEqual(1, $value, "{$question->text}: {$answer->answer_value}");
                }
            }
            if (in_array($question->type, ['one choice', 'multiple choice'], true)) {
                $this->assertLessThanOrEqual(count($question->config['options']), max($values ?: [1]));
            }
        }
    }

    /** @test */
    public function demo_entries_are_never_in_the_future()
    {
        $this->runDemo();

        $this->assertFalse(MartEntry::whereIn('schedule_id', $this->demoScheduleIds())
            ->where('completed_at', '>', now())
            ->exists());
    }

    /**
     * The options of a new staging project, as the app reads them.
     */
    private function stagingProjectOptions(): array
    {
        $this->runStaging([['email' => 'tester@example.test', 'projects' => ['Staging A']]]);
        $project = Project::where('name', 'Staging A')->firstOrFail();

        $structure = (new MartApiController)->getProjectStructure(Request::create('/test'), $project)->toArray(null);

        return json_decode(json_encode($structure['projectOptions']), true)['options'];
    }

    private function assertOpenToday(array $window, string $label): void
    {
        $today = now()->startOfDay();
        $start = Carbon::createFromFormat('d.m.Y', $window['startDateAndTime']['date'])->startOfDay();
        $end = Carbon::createFromFormat('d.m.Y', $window['endDateAndTime']['date'])->startOfDay();

        $this->assertTrue($start->lte($today) && $end->gte($today), "{$label} runs {$start->toDateString()} to {$end->toDateString()}");
    }

    private function days(string $start, string $end): int
    {
        return (int) Carbon::parse($start)->diffInDays(Carbon::parse($end));
    }

    private function runStaging(array $accounts): void
    {
        $seeder = new StagingMartSeeder;
        $seeder->accounts = $accounts;

        $this->runSeeder($seeder);
    }

    private function runDemo(): void
    {
        $seeder = new MartProjectSeeder;
        $seeder->answersPerCase = 2;

        $this->runSeeder($seeder);
    }

    private function runSeeder(Seeder $seeder): void
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $command = new Command;
        $command->setOutput(new OutputStyle($input, new NullOutput));

        $seeder->setContainer($this->app)->setCommand($command)->__invoke();
    }

    private function demoScheduleIds(?Project $project = null)
    {
        $projectIds = $project ? [$project->id] : Project::whereIn('name', self::DEMO_PROJECTS)->pluck('id');

        return MartSchedule::whereIn(
            'mart_project_id',
            MartProject::whereIn('main_project_id', $projectIds)->pluck('id'),
        )->pluck('id');
    }
}
