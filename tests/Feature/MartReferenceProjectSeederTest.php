<?php

namespace Tests\Feature;

use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartSchedule;
use App\Project;
use App\Role;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Hash;
use MartReferenceProjectSeeder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * MartReferenceProjectSeeder rebuilds Directus project 1. The MART connection
 * is rolled back here too, so the seeded rows don't stay behind.
 */
class MartReferenceProjectSeederTest extends TestCase
{
    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    /** @test */
    public function it_rebuilds_every_questionnaire_with_all_its_items()
    {
        $project = $this->seedReferenceProject();

        $expected = collect(MartReferenceProjectSeeder::fixture('questionnaires'))
            ->mapWithKeys(fn ($questionnaire) => [$questionnaire['questionnaireId'] => count($questionnaire['items'])])
            ->all();

        $this->assertSame($expected, MartSchedule::where('mart_project_id', $project->martProject()->id)
            ->withCount('questions')
            ->orderBy('questionnaire_id')
            ->pluck('questions_count', 'questionnaire_id')
            ->all());
    }

    /** @test */
    public function the_editor_route_saves_the_same_questionnaires()
    {
        $seeded = $this->seedReferenceProject();
        $copy = $this->martProjectOfTheTestUser();

        foreach ((new MartReferenceProjectSeeder)->questionnairePayloads() as $payload) {
            $this->actingAs($this->user)
                ->postJson("/projects/{$copy->id}/questionnaires", $payload)
                ->assertOk();
        }

        $this->assertCount(count(MartReferenceProjectSeeder::fixture('questionnaires')), $this->savedQuestionnaires($seeded));
        $this->assertSame($this->savedQuestionnaires($seeded), $this->savedQuestionnaires($copy));
    }

    /** @test */
    public function every_sample_submit_is_stored_like_an_app_submit()
    {
        $project = $this->seedReferenceProject();

        $entries = MartEntry::whereIn('case_id', $project->cases()->pluck('id'))->get();
        $answers = MartAnswer::whereIn('entry_id', $entries->pluck('id'))->pluck('answer_value');

        $this->assertCount(count(MartReferenceProjectSeeder::fixture('submits')), $entries);
        $this->assertTrue($answers->every(fn ($value) => is_array(json_decode($value, true))));
    }

    /** @test */
    public function no_answer_points_at_an_uploaded_file()
    {
        $project = $this->seedReferenceProject();

        $entryIds = MartEntry::whereIn('case_id', $project->cases()->pluck('id'))->pluck('id');

        $this->assertFalse(MartAnswer::whereIn('entry_id', $entryIds)->where('answer_value', 'like', '%http%')->exists());
    }

    /** @test */
    public function each_participant_signs_in_with_their_own_account()
    {
        $project = $this->seedReferenceProject();

        $cases = $project->cases()->with('user')->get();
        $participants = collect(MartReferenceProjectSeeder::fixture('submits'))->pluck('participantId')->unique();

        $this->assertCount($participants->count(), $cases->pluck('user_id')->unique());
        $this->assertSame(
            $cases->pluck('name')->map(fn ($name) => "{$name}@example.test")->all(),
            $cases->pluck('user.email')->all(),
        );
    }

    /** @test */
    public function an_existing_account_keeps_its_password_and_roles()
    {
        $admin = Role::firstOrCreate(['name' => 'admin'], ['description' => 'admin']);
        $user = User::create(['email' => 'reference-researcher@example.test', 'password' => bcrypt('kept-password')]);
        $user->roles()->attach($admin->id);

        $this->runSeeder();

        $user->refresh();
        $this->assertTrue(Hash::check('kept-password', $user->password));
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'researcher')->exists());
    }

    /** @test */
    public function it_never_runs_in_production()
    {
        $this->app['env'] = 'production';

        $this->runSeeder();

        $this->assertNull(User::where('email', 'reference-researcher@example.test')->first());
    }

    /**
     * The app greys out any scale on a text-only item, a text field too. The
     * editor offers single and multiple choice only, so another scale gives a
     * plain text-only item, and MartReferenceParityTest lists the difference.
     * No item in the current fixtures has one.
     *
     * @test
     */
    public function a_text_only_item_with_a_scale_the_editor_cannot_show_becomes_plain_text()
    {
        $item = ['itemId' => null, 'scaleId' => 9, 'text' => 'Read this'];
        $scales = ['9' => ['type' => 'text', 'textOptions' => ['placeholder' => 'Not for you']]];

        $payload = (new \ReflectionMethod(MartReferenceProjectSeeder::class, 'questionPayload'))
            ->invoke(new MartReferenceProjectSeeder, $item, $scales);

        $this->assertSame(['display', []], [$payload['type'], $payload['config']]);
    }

    /** @test */
    public function the_fixtures_hold_no_personal_data()
    {
        foreach (glob(MartReferenceProjectSeeder::FIXTURES . '/*.json') as $path) {
            $text = file_get_contents($path);
            $name = basename($path);

            foreach (['directus', 'foxweb', 'martapp', 'roland', 'uuid', 'timestampInherited'] as $word) {
                $this->assertStringNotContainsStringIgnoringCase($word, $text, "{$name} contains {$word}");
            }

            preg_match_all('/[\w.+-]+@[\w-]+(\.[\w-]+)+/', $text, $emails);
            foreach ($emails[0] as $email) {
                $this->assertStringEndsWith('@example.test', $email, "{$name} contains {$email}");
            }
        }
    }

    private function runSeeder(): void
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $command = new Command;
        $command->setOutput(new OutputStyle($input, new NullOutput));

        (new MartReferenceProjectSeeder)->setContainer($this->app)->setCommand($command)->__invoke();
    }

    private function seedReferenceProject(): Project
    {
        $this->runSeeder();

        return Project::where('name', MartReferenceProjectSeeder::fixture('project_options')['projectName'])
            ->latest('id')
            ->firstOrFail();
    }

    private function martProjectOfTheTestUser(): Project
    {
        $project = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => json_encode([['type' => 'mart', 'name' => 'MART Configuration', 'answers' => []]]),
        ]);
        MartProject::create(['main_project_id' => $project->id]);

        return $project;
    }

    /**
     * Everything the store route writes for each questionnaire, by questionnaire ID.
     */
    private function savedQuestionnaires(Project $project): array
    {
        return MartSchedule::where('mart_project_id', $project->martProject()->id)
            ->with('questions')
            ->orderBy('questionnaire_id')
            ->get()
            ->mapWithKeys(fn (MartSchedule $schedule) => [$schedule->questionnaire_id => [
                'name' => $schedule->name,
                'type' => $schedule->type,
                'timing_config' => $schedule->timing_config,
                'notification_config' => $schedule->notification_config,
                'is_ios_data_donation' => $schedule->is_ios_data_donation,
                'is_android_data_donation' => $schedule->is_android_data_donation,
                'questions' => $schedule->questions->map(fn ($question) => [
                    'position' => $question->position,
                    'text' => $question->text,
                    'image_url' => $question->image_url,
                    'video_url' => $question->video_url,
                    'type' => $question->type,
                    'config' => $question->config,
                    'is_mandatory' => $question->is_mandatory,
                ])->all(),
            ]])
            ->all();
    }
}
