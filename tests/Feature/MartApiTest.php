<?php

namespace Tests\Feature;

use App\Cases;
use App\Mart\MartAnswer;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\MartPage as OldMartPage;
use App\Project;
use App\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MartApiTest extends TestCase
{
    protected $rawToken;
    protected $questionnaireId1;
    protected $questionnaireId2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create raw token and store hashed version for MART API testing
        $this->rawToken = 'test_token_12345678901234567890123456';
        $this->user->update([
            'api_token' => hash('sha256', $this->rawToken),
        ]);

        // Update existing project with MART configuration
        $this->project->update([
            'name' => 'Test MART Project',
            'description' => 'Test ESM project',
            'created_by' => $this->user->id,
            'use_entity' => false,
            'inputs' => json_encode([
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Test Questionnaire',
                    'projectOptions' => [
                        'startDateAndTime' => ['date' => '2025-01-01', 'time' => '09:00'],
                        'endDateAndTime' => ['date' => '2025-12-31', 'time' => '21:00'],
                        'showProgressBar' => true,
                        'showNotifications' => true,
                        'notificationText' => 'Time for your check-in!',
                        'collectDeviceInfos' => true,
                    ],
                ],
            ]),
        ]);

        // Create or get MART project in MART database (MART DB doesn't rollback between tests)
        $martProject = MartProject::firstOrCreate(['main_project_id' => $this->project->id]);

        // Use unique questionnaire_id based on project ID to avoid conflicts
        $baseQuestionnaireId = $this->project->id * 1000;
        $this->questionnaireId1 = $baseQuestionnaireId + 1;
        $this->questionnaireId2 = $baseQuestionnaireId + 2;

        // Delete any existing schedules for this project to avoid conflicts
        MartSchedule::where('mart_project_id', $martProject->id)->delete();

        // Create questionnaire schedules in MART database with separate questions
        $schedule1 = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->questionnaireId1,
            'name' => 'Daily Check-in',
            'type' => 'repeating',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'end_date_time' => ['date' => '2025-12-31', 'time' => '21:00'],
                'daily_interval_duration' => 4,
                'min_break_between' => 180,
                'max_daily_submits' => 6,
                'daily_start_time' => '09:00',
                'daily_end_time' => '21:00',
                'quest_available_at' => 'randomTimeWithinInterval',
            ],
            'notification_config' => [
                'show_progress_bar' => true,
                'show_notifications' => true,
                'notification_text' => 'Time for your check-in!',
            ],
        ]);

        // Create questions for schedule 1 (positions start from 1, as the editor saves them)
        MartQuestion::create([
            'schedule_id' => $schedule1->id,
            'position' => 1,
            'text' => 'How are you feeling?',
            'type' => 'scale',
            'config' => [
                'minValue' => 1,
                'maxValue' => 10,
                'steps' => 1,
            ],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        MartQuestion::create([
            'schedule_id' => $schedule1->id,
            'position' => 2,
            'text' => 'What are you doing?',
            'type' => 'multiple choice',
            'config' => [
                'options' => [
                    0 => 'Working',
                    1 => 'Relaxing',
                    2 => 'Socializing',
                    3 => 'Other',
                ],
            ],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        // Create schedule 2
        $schedule2 = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->questionnaireId2,
            'name' => 'Weekly Reflection',
            'type' => 'single',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-07', 'time' => '17:00'],
            ],
            'notification_config' => [
                'show_progress_bar' => true,
                'show_notifications' => true,
                'notification_text' => 'Weekly reflection time',
            ],
        ]);

        // Create question for schedule 2
        MartQuestion::create([
            'schedule_id' => $schedule2->id,
            'position' => 1,
            'text' => 'Any thoughts?',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        // Create MART pages in MART database
        MartPage::create([
            'mart_project_id' => $martProject->id,
            'name' => 'Welcome',
            'content' => '<h1>Welcome to the study</h1>',
            'button_text' => 'Continue',
            'show_on_first_app_start' => true,
            'sort_order' => 0,
        ]);

        // Update existing case
        $this->case->update([
            'name' => 'Participant_001',
            'duration' => 'duration:7days|firstDay:' . now()->format('d.m.Y') . '|lastDay:' . now()->addDays(7)->format('d.m.Y') . '|value:7days',
        ]);
    }

    /** @test */
    public function it_submits_entry_with_questionnaire_id()
    {
        // Test controller method directly for core logic verification
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $this->questionnaireId1,
            'userId' => 'test@example.com',
            'participantId' => 'Participant_001',
            'sheetId' => 1,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => [
                '1' => 2,
                '2' => [0, 1],  // Use indices instead of strings
            ],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->submitEntry($request, $this->case);

        $responseData = $response->getData(true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Entry created successfully', $responseData['message']);

        // Verify MART entry was created in MART DB
        $martEntry = MartEntry::where('case_id', $this->case->id)->first();
        $this->assertNotNull($martEntry);
        $this->assertEquals($this->questionnaireId1, $martEntry->questionnaire_id);
        $this->assertEquals('test@example.com', $martEntry->user_id);
        $this->assertEquals('Participant_001', $martEntry->participant_id);
        $this->assertEquals(300000, $martEntry->duration_ms);
        $this->assertEquals('Europe/Berlin', $martEntry->timezone);

        // Verify MART answers were created
        $this->assertCount(2, $martEntry->answers);
    }

    /** @test */
    public function it_returns_participant_data_when_participant_id_provided()
    {
        // First create an entry for a participant
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $this->questionnaireId1,
            'userId' => $this->user->email,
            'participantId' => $this->case->name,
            'sheetId' => 1,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 180000,
            'answers' => ['1' => 3, '2' => [1, 2]],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $this->assertTrue($controller->submitEntry($request, $this->case)->getData(true)['success']);

        // Now test structure endpoint with participant_id
        $structureRequest = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $this->case->name]);
        $resource = $controller->getProjectStructure($structureRequest, $this->project);
        $structureArray = $resource->toArray(null);

        // Check that participant data sections are included
        $this->assertArrayHasKey('deviceInfos', $structureArray);
        $this->assertArrayHasKey('repeatingSubmits', $structureArray);
        $this->assertArrayHasKey('singleSubmits', $structureArray);
        $this->assertArrayHasKey('lastDataDonationSubmit', $structureArray);
        $this->assertArrayHasKey('lastAndroidStatsSubmit', $structureArray);

        // The submission just made, and only that one
        $this->assertCount(1, $structureArray['repeatingSubmits']);
        $this->assertEquals($this->questionnaireId1, $structureArray['repeatingSubmits'][0]['questionnaireId']);
        $this->assertIsNumeric($structureArray['repeatingSubmits'][0]['timestamp']);
    }

    /**
     * Case names can repeat across projects, so two projects can each have a
     * "P01". The per-participant data must come from the participant's
     * own project, as Directus filters by projectId and participantId.
     * Otherwise the other P01's single submits mark questionnaires as done.
     *
     * @test
     */
    public function participant_data_comes_only_from_the_participants_own_project()
    {
        $name = 'P01-' . uniqid();
        $this->case->update(['name' => $name]);
        $ownMartProject = $this->project->martProject();
        $ownSchedule = MartSchedule::where('mart_project_id', $ownMartProject->id)
            ->where('questionnaire_id', $this->questionnaireId1)
            ->first();

        // A second project with its own participant of the same name.
        $otherProject = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => $this->project->inputs,
        ]);
        $otherMartProject = MartProject::firstOrCreate(['main_project_id' => $otherProject->id]);
        $otherUser = User::factory()->create();
        Cases::factory()->create([
            'project_id' => $otherProject->id,
            'user_id' => $otherUser->id,
            'name' => $name,
        ]);

        foreach (['repeating' => 1, 'single' => 2] as $type => $offset) {
            $otherSchedule = MartSchedule::create([
                'mart_project_id' => $otherMartProject->id,
                'questionnaire_id' => $otherProject->id * 1000 + $offset,
                'name' => "Other {$type}",
                'type' => $type,
                'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
                'notification_config' => [],
            ]);
            $this->createMartEntry($otherSchedule, $name, $otherUser->email, 1700000900);
        }
        MartStat::create([
            'mart_project_id' => $otherMartProject->id,
            'participant_id' => $name,
            'user_id' => $otherUser->email,
            'android_usage_stats' => [['packageName' => 'com.example.other']],
            'timestamp' => 1700000900,
            'timezone' => 'Europe/Berlin',
        ]);
        MartDeviceInfo::create([
            'participant_id' => $name,
            'user_id' => $otherUser->email,
            'os' => 'ios',
            'os_version' => '18',
            'model' => 'Other phone',
            'manufacturer' => 'Apple',
            'last_updated' => now(),
        ]);

        // The participant's own data, which must still come through.
        $this->createMartEntry($ownSchedule, $name, $this->user->email, 1700000100);
        MartStat::create([
            'mart_project_id' => $ownMartProject->id,
            'participant_id' => $name,
            'user_id' => $this->user->email,
            'android_usage_stats' => [['packageName' => 'com.example.own']],
            'timestamp' => 1700000100,
            'timezone' => 'Europe/Berlin',
        ]);
        MartDeviceInfo::create([
            'participant_id' => $name,
            'user_id' => $this->user->email,
            'os' => 'android',
            'os_version' => '14',
            'model' => 'Own phone',
            'manufacturer' => 'Google',
            'last_updated' => now(),
        ]);

        $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $name]);
        $structure = (new \App\Http\Controllers\MartApiController)
            ->getProjectStructure($request, $this->project)
            ->toArray($request);

        $this->assertSame(
            [['questionnaireId' => $this->questionnaireId1, 'timestamp' => 1700000100]],
            $structure['repeatingSubmits'],
        );
        $this->assertSame([], $structure['singleSubmits']);
        $this->assertSame(['timestamp' => 1700000100], $structure['lastAndroidStatsSubmit']);
        $this->assertSame(['Own phone'], array_column($structure['deviceInfos'], 'model'));
    }

    /**
     * Android stats rows run to megabytes of usage and event data. MySQL
     * refuses to sort rows that outgrow its sort buffer ("Out of sort
     * memory"), so finding the latest submit must not sort whole rows.
     *
     * @test
     */
    public function a_participant_with_megabytes_of_android_stats_still_gets_the_structure()
    {
        $name = 'P-stats-' . uniqid();
        $this->case->update(['name' => $name]);
        $stat = MartStat::create([
            'mart_project_id' => $this->project->martProject()->id,
            'participant_id' => $name,
            'user_id' => $this->user->email,
            'android_usage_stats' => [['packageName' => 'com.example.app']],
            'android_event_stats' => array_fill(0, 30000, ['packageName' => 'com.example.app', 'eventType' => 1, 'timeStamp' => 1700000000000]),
            'timestamp' => 1700000500,
            'timezone' => 'Europe/Berlin',
        ]);

        try {
            $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $name]);
            $structure = (new \App\Http\Controllers\MartApiController)
                ->getProjectStructure($request, $this->project)
                ->toArray($request);

            $this->assertSame(['timestamp' => 1700000500], $structure['lastAndroidStatsSubmit']);
        } finally {
            // The MART connection isn't rolled back in this class.
            $stat->delete();
        }
    }

    private function createMartEntry(MartSchedule $schedule, string $participantId, string $userId, int $timestamp): MartEntry
    {
        return MartEntry::create([
            'schedule_id' => $schedule->id,
            'questionnaire_id' => $schedule->questionnaire_id,
            'participant_id' => $participantId,
            'user_id' => $userId,
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'duration_ms' => 60000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => $timestamp,
        ]);
    }

    /**
     * Item 8 from Stefan's round-3 report: `lastDataDonationSubmit` must
     * reflect the most recent submission to a schedule flagged either
     * `is_ios_data_donation` OR `is_android_data_donation` (whichever was
     * submitted later). The previous implementation only scanned the
     * iOS-flagged schedule, so Android-only donations returned null and
     * mixed-platform donations returned the wrong timestamp.
     *
     * @test
     */
    public function it_returns_latest_data_donation_across_ios_and_android_schedules()
    {
        $martProject = MartProject::where('main_project_id', $this->project->id)->first();

        $iosSchedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->project->id * 1000 + 100,
            'name' => 'iOS data donation',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
            'is_ios_data_donation' => true,
        ]);

        $androidSchedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->project->id * 1000 + 101,
            'name' => 'Android data donation',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
            'is_android_data_donation' => true,
        ]);

        // iOS submitted EARLIER, Android submitted LATER. The fix must
        // return the Android timestamp; the old iOS-only code would have
        // returned the iOS one (and on Android-only projects, null).
        $iosTimestamp = 1700000000;
        $androidTimestamp = 1700000500;

        // `mart_entries.main_entry_id` has a UNIQUE constraint, so we need
        // distinct values for each MartEntry. Create stub Entry rows in
        // the main DB and use their auto-assigned IDs — the main DB rolls
        // back via DatabaseTransactions, so this doesn't leave residue.
        $iosEntry = \App\Entry::create([
            'case_id' => $this->case->id,
            'begin' => now()->subMinutes(10),
            'end' => now()->subMinutes(9),
            'inputs' => '[]',
            'media_id' => null,
        ]);
        $androidEntry = \App\Entry::create([
            'case_id' => $this->case->id,
            'begin' => now()->subMinutes(1),
            'end' => now(),
            'inputs' => '[]',
            'media_id' => null,
        ]);

        \App\Mart\MartEntry::create([
            'schedule_id' => $iosSchedule->id,
            'questionnaire_id' => $iosSchedule->questionnaire_id,
            'main_entry_id' => $iosEntry->id,
            'participant_id' => $this->case->name,
            'user_id' => 'donation@test.com',
            'started_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(9),
            'duration_ms' => 60000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => $iosTimestamp,
        ]);

        \App\Mart\MartEntry::create([
            'schedule_id' => $androidSchedule->id,
            'questionnaire_id' => $androidSchedule->questionnaire_id,
            'main_entry_id' => $androidEntry->id,
            'participant_id' => $this->case->name,
            'user_id' => 'donation@test.com',
            'started_at' => now()->subMinutes(1),
            'completed_at' => now(),
            'duration_ms' => 60000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => $androidTimestamp,
        ]);

        $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $this->case->name]);
        $controller = new \App\Http\Controllers\MartApiController;
        $resource = $controller->getProjectStructure($request, $this->project);
        $structureArray = $resource->toArray(null);

        $this->assertNotNull(
            $structureArray['lastDataDonationSubmit'],
            'lastDataDonationSubmit should not be null when donations exist on either platform'
        );
        $this->assertEquals(
            $androidTimestamp,
            $structureArray['lastDataDonationSubmit']['timestamp'],
            'lastDataDonationSubmit should return the latest timestamp across iOS and Android donation schedules'
        );
    }

    /**
     * Data-donation questionnaires must NOT appear in `singleQuestionnaires`,
     * only in the top-level `questionnaires` catalog. The mobile's scheduler
     * reads `singleQuestionnaires` to plan time-based prompts; the app shows
     * a donation questionnaire after a regular one instead, and would plan
     * spurious schedules if it were included.
     *
     * @test
     */
    public function data_donation_schedules_are_excluded_from_single_questionnaires()
    {
        $martProject = MartProject::where('main_project_id', $this->project->id)->first();

        // A regular single questionnaire — should appear in singleQuestionnaires.
        $regularQuestionnaireId = $this->project->id * 1000 + 300;
        MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $regularQuestionnaireId,
            'name' => 'Regular single',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
        ]);

        // A donation-flagged single questionnaire — should NOT appear.
        $donationQuestionnaireId = $this->project->id * 1000 + 301;
        MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $donationQuestionnaireId,
            'name' => 'iOS donation',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
            'is_ios_data_donation' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $this->case->name]);
        $controller = new \App\Http\Controllers\MartApiController;
        $resource = $controller->getProjectStructure($request, $this->project);
        // `toArray()` returns the resource's top-level array but leaves
        // nested resources (e.g. ProjectOptionsResource) unresolved.
        // `response()->getData(true)` forces a JSON round-trip so every
        // nested resource is flattened to a plain array.
        $structureArray = $resource->response()->getData(true);

        $singleIds = collect($structureArray['data']['projectOptions']['options']['singleQuestionnaires'])
            ->pluck('questionnaireId')
            ->all();

        $this->assertContains(
            $regularQuestionnaireId,
            $singleIds,
            'Regular single questionnaires should appear in singleQuestionnaires'
        );
        $this->assertNotContains(
            $donationQuestionnaireId,
            $singleIds,
            'Data-donation questionnaires should NOT appear in singleQuestionnaires'
        );
    }

    /**
     * Item 9 from Stefan's round-3 report: `projectOptions.iOSDataDonationQuestionnaire`
     * must reflect the questionnaire_id of whichever schedule is flagged
     * `is_ios_data_donation = true` for this project. Regression net for the
     * `ProjectOptionsResource::getIOSDataCollectionQuestionnaireId` getter.
     *
     * @test
     */
    public function ios_data_donation_questionnaire_is_exposed_in_project_options()
    {
        $martProject = MartProject::where('main_project_id', $this->project->id)->first();

        $iosQuestionnaireId = $this->project->id * 1000 + 200;
        MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $iosQuestionnaireId,
            'name' => 'iOS data donation',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
            'is_ios_data_donation' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $this->case->name]);
        $controller = new \App\Http\Controllers\MartApiController;
        $resource = $controller->getProjectStructure($request, $this->project);
        // See sibling test for why `response()->getData(true)` rather than
        // `toArray()` — the latter leaves nested ProjectOptionsResource
        // unresolved and `$arr['projectOptions']['options']` would be null.
        $structureArray = $resource->response()->getData(true);

        $this->assertEquals(
            $iosQuestionnaireId,
            $structureArray['data']['projectOptions']['options']['iOSDataDonationQuestionnaire'],
            'iOSDataDonationQuestionnaire should expose the questionnaire_id of the iOS-flagged schedule'
        );
    }

    /**
     * The editor hides the date fields for a data donation questionnaire, so
     * it sends an empty start date.
     *
     * @test
     */
    public function a_data_donation_questionnaire_without_a_start_date_is_stored_and_served()
    {
        $questionnaireId = $this->project->id * 1000 + 504;

        $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/questionnaires", [
                'questionnaire_id' => $questionnaireId,
                'name' => 'Donation ' . uniqid(),
                'type' => 'single',
                'start_date_time' => ['date' => '', 'time' => '09:00'],
                'show_progress_bar' => true,
                'show_notifications' => false,
                'is_ios_data_donation' => true,
                'questions' => [[
                    'text' => 'Screen time',
                    'type' => 'text',
                    'mandatory' => true,
                    'config' => [],
                ]],
            ])->assertOk();

        $request = \Illuminate\Http\Request::create('/test', 'GET', ['participant_id' => $this->case->name]);
        $structure = (new \App\Http\Controllers\MartApiController)
            ->getProjectStructure($request, $this->project)
            ->response()->getData(true)['data'];

        $this->assertSame($questionnaireId, $structure['projectOptions']['options']['iOSDataDonationQuestionnaire']);
        $this->assertContains($questionnaireId, array_column($structure['questionnaires'], 'questionnaireId'));
    }

    /**
     * BE.7 from Stefan's round-2 report: the "Show after completing a
     * repeating questionnaire" option was not persisting when a
     * questionnaire was *edited* (creating worked). Root cause: the update
     * path's validation rules and the `$timingFields` allow-list both
     * omitted `show_after_repeating`, so the field got silently dropped
     * before `timing_config` was rebuilt.
     *
     * @test
     */
    public function show_after_repeating_persists_on_update()
    {
        $martProject = MartProject::where('main_project_id', $this->project->id)->first();

        // Seed a single questionnaire that already has show_after_repeating set
        // (= the "create worked" half of the original bug).
        $schedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->project->id * 1000 + 400,
            'name' => 'Follow-up',
            'type' => 'single',
            'timing_config' => [
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'show_after_repeating' => [
                    'repeatingQuestId' => $this->questionnaireId1,
                    'showAfterAmount' => 3,
                ],
            ],
            'notification_config' => [],
        ]);

        \App\Mart\MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 0,
            'text' => 'How was it?',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        // Now edit the questionnaire and send a NEW show_after_repeating value
        // (different completion count). Before the fix this got dropped on
        // save and the schedule fell back to its previous value.
        $request = new \Illuminate\Http\Request([
            'name' => 'Follow-up',
            'type' => 'single',
            'show_after_repeating' => [
                'repeatingQuestId' => $this->questionnaireId1,
                'showAfterAmount' => 5,
            ],
            'questions' => [
                [
                    'text' => 'How was it?',
                    'type' => 'text',
                    'mandatory' => false,
                    'config' => [],
                ],
            ],
        ]);

        // The controller's authorize() check requires an authenticated user
        // who can update the project. The test user owns the project (set up
        // in Tests\TestCase::setUp), so act as them.
        $this->actingAs($this->user);

        $controller = new \App\Http\Controllers\MartQuestionnaireController;
        $controller->updateQuestions($request, $schedule);

        $schedule->refresh();
        $this->assertEquals(
            ['repeatingQuestId' => $this->questionnaireId1, 'showAfterAmount' => 5],
            $schedule->timing_config['show_after_repeating'],
            'show_after_repeating must persist through an update (BE.7 regression)'
        );
    }

    /** @test */
    public function it_stores_device_info()
    {
        // Test controller method directly
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'userId' => 'test@example.com',
            'participantId' => 'Participant_001',
            'os' => 'android',
            'osVersion' => '14',
            'model' => 'Pixel 7',
            'manufacturer' => 'Google',
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);
        $request->setUserResolver(fn () => $this->user);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->storeDeviceInfo($request);

        $responseData = $response->getData(true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Device information stored successfully', $responseData['message']);

        // Verify device info was stored in MART database
        $deviceInfo = \App\Mart\MartDeviceInfo::where('participant_id', 'Participant_001')
            ->where('user_id', 'test@example.com')
            ->first();
        $this->assertNotNull($deviceInfo);
        $this->assertEquals('android', $deviceInfo->os);
        $this->assertEquals('Pixel 7', $deviceInfo->model);
    }

    /** @test */
    public function it_requires_questionnaire_id_for_submission()
    {
        // Test validation directly by creating invalid request
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            // Missing questionnaireId
            'userId' => 'test@example.com',
            'participantId' => 'Participant_001',
            'sheetId' => 1,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => ['1' => 5],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $controller = new \App\Http\Controllers\MartApiController;
        $controller->submitEntry($request, $this->case);
    }

    /** @test */
    public function it_auto_creates_case_when_structure_called_with_authenticated_user()
    {
        // Create a new user without a case
        $newUser = User::factory()->create();

        // Verify user has no case in this project
        $this->assertDatabaseMissing('cases', [
            'user_id' => $newUser->id,
            'project_id' => $this->project->id,
        ]);

        // Create request with authenticated user
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->setUserResolver(fn() => $newUser);

        // Call controller directly
        $controller = new \App\Http\Controllers\MartApiController;
        $resource = $controller->getProjectStructure($request, $this->project);

        // Verify case was auto-created
        $this->assertDatabaseHas('cases', [
            'user_id' => $newUser->id,
            'project_id' => $this->project->id,
        ]);

        // Verify case has correct format
        $case = \App\Cases::where('user_id', $newUser->id)
            ->where('project_id', $this->project->id)
            ->first();

        $this->assertNotNull($case);
        $this->assertMatchesRegularExpression('/^P[A-F0-9]{6}$/', $case->name);
        $this->assertNotNull($case->first_login_at);
    }

    /** @test */
    public function it_does_not_create_duplicate_case_on_subsequent_structure_calls()
    {
        // Create a new user without a case
        $newUser = User::factory()->create();

        // Create request with authenticated user
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->setUserResolver(fn() => $newUser);

        // Call controller twice
        $controller = new \App\Http\Controllers\MartApiController;
        $controller->getProjectStructure($request, $this->project);
        $controller->getProjectStructure($request, $this->project);

        // Verify only one case exists
        $caseCount = \App\Cases::where('user_id', $newUser->id)
            ->where('project_id', $this->project->id)
            ->count();

        $this->assertEquals(1, $caseCount);
    }

    /** @test */
    public function it_returns_403_with_hint_for_mismatched_access()
    {
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'userId' => 'test@example.com',
            'participantId' => 'WRONG_PARTICIPANT_ID',
            'os' => 'android',
            'osVersion' => '14',
            'model' => 'Pixel 7',
            'manufacturer' => 'Google',
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);
        $request->setUserResolver(fn () => $this->user);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->storeDeviceInfo($request);

        $responseData = $response->getData(true);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('hint', $responseData);
        $this->assertStringContainsString('check-access', $responseData['hint']);
    }

    /** @test */
    public function it_returns_404_with_hint_for_unknown_user()
    {
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'userId' => 'nonexistent@example.com',
            'participantId' => 'Participant_001',
            'os' => 'android',
            'osVersion' => '14',
            'model' => 'Pixel 7',
            'manufacturer' => 'Google',
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->storeDeviceInfo($request);

        $responseData = $response->getData(true);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('hint', $responseData);
        $this->assertStringContainsString('email', $responseData['hint']);
    }

    /** @test */
    public function it_rejects_submit_when_userId_does_not_match_case_owner()
    {
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $this->questionnaireId1,
            'userId' => 'attacker@example.com', // does NOT match case owner
            'participantId' => $this->case->name,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => ['1' => 2],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->submitEntry($request, $this->case);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('userId does not match the case owner', $responseData['message']);
    }

    /**
     * The invite keeps the email as the researcher typed it, and login finds
     * the account in any case, so the app may send another case on submit.
     *
     * @test
     */
    public function it_accepts_submit_when_userId_differs_from_the_case_owner_only_in_case()
    {
        $this->user->update(['email' => 'Anna.Mueller@Example.com']);

        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $this->questionnaireId1,
            'userId' => 'anna.mueller@example.com',
            'participantId' => $this->case->name,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => ['1' => 2],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $response = (new \App\Http\Controllers\MartApiController)->submitEntry($request, $this->case->fresh());

        $this->assertTrue($response->getData(true)['success']);
    }

    /** @test */
    public function it_returns_404_when_participant_id_is_invalid()
    {
        $request = \Illuminate\Http\Request::create(
            "/mart-api/projects/{$this->project->id}/structure?participant_id=DOES_NOT_EXIST",
            'GET'
        );

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->getProjectStructure($request, $this->project);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('participant_id', $responseData['message']);
    }

    /**
     * Every item carries an `options` block read from the question's config,
     * with noValueAllowed derived from is_mandatory. Before, all keys read
     * attributes that do not exist on MartQuestion, so the defaults always
     * fired and every item looked mandatory to the app.
     *
     * @test
     */
    public function item_options_are_read_from_the_question_rather_than_defaulted()
    {
        $schedule2 = MartSchedule::where('mart_project_id', $this->project->martProject()->id)
            ->where('questionnaire_id', $this->questionnaireId2)
            ->first();

        // itemGroup stored as an int on purpose: martTypes.ts declares a
        // string, so the resource has to normalise.
        MartQuestion::create([
            'schedule_id' => $schedule2->id,
            'position' => 2,
            'text' => 'Grouped and randomised',
            'type' => 'text',
            'config' => [
                'itemGroup' => 2,
                'randomizationGroupId' => 3,
                'randomizeAnswers' => true,
                'randomPositionWithinQuestionnaire' => true,
                'posterUrl' => 'https://example.test/poster.png',
            ],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        // A display item marked mandatory must still be skippable.
        MartQuestion::create([
            'schedule_id' => $schedule2->id,
            'position' => 3,
            'text' => 'Thanks for taking part.',
            'type' => 'display',
            'config' => [],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $resource = (new \App\Http\Controllers\MartApiController)
            ->getProjectStructure($request, $this->project);
        $structure = json_decode(json_encode($resource->toArray($request)), true);

        $byId = collect($structure['questionnaires'])->keyBy('questionnaireId');

        // setUp's first questionnaire: question 1 is mandatory, question 2 is not.
        $first = $byId[$this->questionnaireId1]['items'];
        $this->assertFalse($first[0]['options']['noValueAllowed'], 'mandatory question must not be skippable');
        $this->assertTrue($first[1]['options']['noValueAllowed'], 'optional question must be skippable');

        $second = $byId[$this->questionnaireId2]['items'];

        // Empty config still emits the whole block with its defaults.
        $this->assertSame([
            'randomizationGroupId' => null,
            'randomizeAnswers' => false,
            'randomPositionWithinQuestionnaire' => false,
            'itemGroup' => null,
            'noValueAllowed' => true,
        ], $second[0]['options']);
        $this->assertArrayNotHasKey('posterUrl', $second[0]);

        // Everything authored in config surfaces, with itemGroup as a string.
        $this->assertSame([
            'randomizationGroupId' => 3,
            'randomizeAnswers' => true,
            'randomPositionWithinQuestionnaire' => true,
            'itemGroup' => '2',
            'noValueAllowed' => false,
        ], $second[1]['options']);
        $this->assertSame('https://example.test/poster.png', $second[1]['posterUrl']);

        // Display item: skippable despite is_mandatory being true.
        $this->assertNull($second[2]['itemId']);
        $this->assertTrue($second[2]['options']['noValueAllowed']);
    }

    /** @test */
    public function the_questionnaire_route_saves_a_range_values_question()
    {
        $question = $this->rangeValuesQuestion();

        $this->assertSame('range values', $question->type);
        $this->assertSame(['options' => ['bad', 'ok', 'good'], 'defaultValue' => 1], $question->config);
    }

    /** @test */
    public function the_questions_route_can_turn_a_question_into_range_values()
    {
        $question = $this->questionOfSchedule2();

        $this->updateToRangeValues($question, ['options' => ['bad', 'ok', 'good']])->assertOk();

        $this->assertSame('range values', $question->fresh()->type);
    }

    /** @test */
    public function the_questionnaire_route_keeps_every_config_key_of_a_range_values_question()
    {
        $config = [
            'options' => ['gar nicht', 'mittel', 'sehr'],
            'defaultValue' => 1,
            'timer' => ['time' => 30, 'showCountdown' => true],
            'randomizationGroupId' => 2,
            'itemGroup' => '3',
        ];

        $this->assertEquals($config, $this->rangeValuesQuestion($config)->config);
    }

    /** @test */
    public function a_blank_range_values_label_is_sent_as_empty_text()
    {
        // ConvertEmptyStringsToNull stores the blank label as null.
        $question = $this->rangeValuesQuestion(['options' => ['gar nicht', '', 'sehr']]);

        $scale = (new \App\Http\Resources\Mart\ScaleResource($question, 0, true))->scaleOptions();

        $this->assertSame(['gar nicht', '', 'sehr'], array_column($scale['rangeValueOptions'], 'text'));
    }

    /** @test */
    public function the_questionnaire_route_rejects_range_values_without_options()
    {
        // The app reads rangeValueOptions[0] without a check, so an empty list crashes it.
        $this->storeRangeValuesQuestionnaire(['config' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_range_values_without_a_config()
    {
        $this->storeRangeValuesQuestionnaire([])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questions_route_rejects_range_values_with_one_option()
    {
        $question = $this->questionOfSchedule2();

        $this->updateToRangeValues($question, ['options' => ['Only']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);

        $this->assertSame('text', $question->fresh()->type);
    }

    /** @test */
    public function an_untouched_range_values_answer_is_stored_as_sent()
    {
        // The app sends the default as a number when the slider isn't moved.
        $question = $this->rangeValuesQuestion();

        $this->assertSame('[2]', $this->submitRangeValuesAnswer($question, [2]));
    }

    /** @test */
    public function a_moved_range_values_answer_is_stored_as_sent()
    {
        // A moved slider gives the app a string.
        $question = $this->rangeValuesQuestion();

        $this->assertSame('["2"]', $this->submitRangeValuesAnswer($question, ['2']));
    }

    /** @test */
    public function the_questionnaire_route_keeps_every_config_key_of_a_file_upload_question()
    {
        $config = [
            'fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => ['.jpg', '.pdf']],
            'timer' => ['time' => 30, 'showCountdown' => true],
            'itemGroup' => '3',
        ];

        $response = $this->storeFileUploadQuestionnaire($config)->assertOk();
        $question = MartSchedule::findOrFail($response->json('schedule.id'))->questions()->firstOrFail();

        $this->assertEquals(['file', $config], [$question->type, $question->config]);
    }

    /** @test */
    public function the_questions_route_can_turn_a_question_into_a_file_upload()
    {
        $question = $this->questionOfSchedule2();

        $this->updateToFileUpload($question, ['fileUploadOptions' => ['maxFileSize' => 5, 'allowedFormats' => ['.pdf']]])
            ->assertOk();

        $this->assertSame('file', $question->fresh()->type);
    }

    /**
     * Without formats the app takes any file. When the upload endpoint
     * refuses it, the app stops the whole submit.
     *
     * @test
     */
    public function the_questionnaire_route_rejects_a_file_upload_without_formats()
    {
        $this->storeFileUploadQuestionnaire(['fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => []]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /**
     * The server takes files up to 50 MB. A larger file passes the app's own
     * check, the upload is refused, and the app stops the whole submit.
     *
     * @test
     */
    public function the_questionnaire_route_rejects_a_file_upload_over_50_mb()
    {
        $this->storeFileUploadQuestionnaire(['fileUploadOptions' => ['maxFileSize' => 51, 'allowedFormats' => ['.pdf']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_file_upload_without_a_size()
    {
        // ConvertEmptyStringsToNull turns the empty field into null.
        $this->storeFileUploadQuestionnaire(['fileUploadOptions' => ['maxFileSize' => '', 'allowedFormats' => ['.pdf']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /**
     * The upload endpoint refuses these, and the app then stops the submit.
     * TrimStrings and ConvertEmptyStringsToNull turn a blank format into
     * null, which the app would read as no formats at all: any file.
     *
     * @test
     *
     * @dataProvider formatsTheServerDoesNotTake
     */
    public function the_questionnaire_route_rejects_a_format_the_server_does_not_take(array $formats)
    {
        $this->storeFileUploadQuestionnaire(['fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => $formats]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    public static function formatsTheServerDoesNotTake(): array
    {
        return [
            'an office file' => [['.pdf', '.docx']],
            'svg' => [['.svg']],
            'a blank entry' => [['']],
            'a blank entry among offered ones' => [['.pdf', ' ']],
            'a number' => [[5]],
        ];
    }

    /**
     * The config is saved as sent. ScaleResource sends the formats as the
     * app matches them.
     *
     * @test
     */
    public function the_questionnaire_route_takes_formats_in_any_case_and_without_a_dot()
    {
        $config = ['fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => ['PDF', 'jpg']]];

        $response = $this->storeFileUploadQuestionnaire($config)->assertOk();
        $question = MartSchedule::findOrFail($response->json('schedule.id'))->questions()->firstOrFail();

        $this->assertSame($config, $question->config);
    }

    /** @test */
    public function the_questions_route_rejects_a_file_upload_without_formats()
    {
        $question = $this->questionOfSchedule2();

        $this->updateToFileUpload($question, ['fileUploadOptions' => ['maxFileSize' => 5]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);

        $this->assertSame('text', $question->fresh()->type);
    }

    /**
     * The app sends an answer for every item with an itemId and none for a
     * display item, so a display item saved as mandatory used to fail every
     * submit with a 422.
     *
     * @test
     */
    public function a_submit_without_an_answer_for_a_mandatory_display_item_is_accepted()
    {
        MartQuestion::create([
            'schedule_id' => $this->questionOfSchedule2()->schedule_id,
            'position' => 2,
            'text' => 'Options shown disabled',
            'type' => 'display',
            'config' => ['displaySelection' => 'checkbox', 'options' => ['Option 1', 'Option 2']],
            'is_mandatory' => true,
            'version' => 1,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/mobile/cases/{$this->case->id}/submit", [
                'userId' => $this->user->email,
                'projectId' => $this->project->id,
                'questionnaireId' => $this->questionnaireId2,
                'participantId' => $this->case->name,
                'answers' => ['1' => []],
                'questionnaireStarted' => now()->timestamp * 1000,
                'questionnaireDuration' => 60000,
                'timestamp' => now()->timestamp * 1000,
                'timestampInherited' => 0,
                'timezone' => 'Europe/Berlin',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    /**
     * With no item that has an itemId, the app sends `answers: {}`, which
     * Laravel decodes to an empty array. `required` refused that, and the
     * submission stayed on the phone.
     *
     * @test
     */
    public function a_questionnaire_with_only_display_items_can_be_submitted()
    {
        $schedule = $this->displayOnlySchedule();

        $response = $this->submitAnswersOverHttp($schedule, ['answers' => (object) []])->assertOk();

        $entry = MartEntry::findOrFail($response->json('entry_id'));
        $this->assertSame([$schedule->id, 0], [$entry->schedule_id, $entry->answers()->count()]);
    }

    /** @test */
    public function a_submit_without_an_answers_key_is_refused()
    {
        $this->submitAnswersOverHttp($this->displayOnlySchedule(), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['answers']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_blank_option_between_others_on_a_display_item()
    {
        // ScaleResource would send the blank one as an option without text.
        $this->storeDisplayQuestionnaire(['displaySelection' => 'radio', 'options' => ['A', '', 'B']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_accepts_two_options_on_a_display_item()
    {
        $this->storeDisplayQuestionnaire(['displaySelection' => 'radio', 'options' => ['A', 'B']])->assertOk();
    }

    /** @test */
    public function the_questionnaire_route_keeps_every_config_key_of_a_display_item_with_answer_options()
    {
        $config = [
            'displaySelection' => 'checkbox',
            'options' => ['Option 1', 'Option 2'],
            'includeOtherOption' => true,
            'otherTextType' => 'textarea',
            'otherOptionLabel' => 'etwas anders',
            'timer' => ['time' => 10, 'showCountdown' => true],
            'itemGroup' => '2',
        ];

        $response = $this->storeDisplayQuestionnaire($config)->assertOk();
        $question = MartSchedule::findOrFail($response->json('schedule.id'))->questions()->firstOrFail();

        $this->assertEquals(['display', $config], [$question->type, $question->config]);
    }

    /** @test */
    public function the_questions_route_keeps_every_config_key_of_a_display_item_with_answer_options()
    {
        $question = $this->questionOfSchedule2();
        $config = [
            'displaySelection' => 'radio',
            'options' => ['Yes', 'No'],
            'includeOtherOption' => true,
            'otherTextType' => 'text',
        ];

        $this->updateToDisplay($question, $config)->assertOk();

        $this->assertEquals(['display', $config], [$question->fresh()->type, $question->fresh()->config]);
    }

    /** @test */
    public function the_questionnaire_route_saves_a_display_item_without_answer_options()
    {
        $this->storeDisplayQuestionnaire([])->assertOk();
    }

    /** @test */
    public function the_questionnaire_route_rejects_answer_options_on_a_display_item_with_one_option()
    {
        $this->storeDisplayQuestionnaire(['displaySelection' => 'radio', 'options' => ['Only']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_blank_second_option_on_a_display_item()
    {
        // ConvertEmptyStringsToNull turns the blank option into null.
        $this->storeDisplayQuestionnaire(['displaySelection' => 'checkbox', 'options' => ['Option 1', '']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_display_selection_other_than_single_or_multiple_choice()
    {
        $this->storeDisplayQuestionnaire(['displaySelection' => 'dropdown', 'options' => ['Yes', 'No']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questions_route_rejects_answer_options_on_a_display_item_with_one_option()
    {
        $question = $this->questionOfSchedule2();

        $this->updateToDisplay($question, ['displaySelection' => 'checkbox', 'options' => ['Only']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);

        $this->assertSame('text', $question->fresh()->type);
    }

    /**
     * A questionnaire whose items are all text only, so none has an itemId.
     */
    private function displayOnlySchedule(): MartSchedule
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => $this->project->martProject()->id,
            'questionnaire_id' => $this->project->id * 1000 + 3,
            'name' => 'Information only',
            'type' => 'single',
            'timing_config' => ['start_date_time' => ['date' => '2025-01-01', 'time' => '09:00']],
            'notification_config' => [],
        ]);

        foreach (['Welcome', 'Thank you'] as $position => $text) {
            MartQuestion::create([
                'schedule_id' => $schedule->id,
                'position' => $position + 1,
                'text' => $text,
                'type' => 'display',
                'config' => [],
                'is_mandatory' => false,
                'version' => 1,
            ]);
        }

        return $schedule;
    }

    /**
     * Submits to the questionnaire as the app does, with $fields merged in.
     */
    private function submitAnswersOverHttp(MartSchedule $schedule, array $fields): TestResponse
    {
        return $this->actingAs($this->user, 'api')
            ->postJson("/api/mobile/cases/{$this->case->id}/submit", [
                'userId' => $this->user->email,
                'projectId' => $this->project->id,
                'questionnaireId' => $schedule->questionnaire_id,
                'participantId' => $this->case->name,
                'questionnaireStarted' => now()->timestamp * 1000,
                'questionnaireDuration' => 60000,
                'timestamp' => now()->timestamp * 1000,
                'timestampInherited' => 0,
                'timezone' => 'Europe/Berlin',
                ...$fields,
            ]);
    }

    /**
     * Posts a questionnaire with one display item to the questionnaire route.
     */
    private function storeDisplayQuestionnaire(array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/questionnaires", [
                'questionnaire_id' => $this->project->id * 1000 + 502,
                'name' => 'Display ' . uniqid(),
                'type' => 'single',
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'show_progress_bar' => true,
                'show_notifications' => false,
                'questions' => [[
                    'text' => 'Options shown disabled',
                    'type' => 'display',
                    'mandatory' => false,
                    'config' => $config,
                ]],
            ]);
    }

    /**
     * Turns the question into a display item through the questions route.
     */
    private function updateToDisplay(MartQuestion $question, array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->putJson("/questionnaires/{$question->schedule_id}/questions", [
                'questions' => [[
                    'uuid' => $question->uuid,
                    'text' => $question->text,
                    'type' => 'display',
                    'mandatory' => false,
                    'config' => $config,
                ]],
            ]);
    }

    /**
     * Posts a questionnaire with one file upload question to the
     * questionnaire route.
     */
    private function storeFileUploadQuestionnaire(array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/questionnaires", [
                'questionnaire_id' => $this->project->id * 1000 + 501,
                'name' => 'Files ' . uniqid(),
                'type' => 'single',
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'show_progress_bar' => true,
                'show_notifications' => false,
                'questions' => [[
                    'text' => 'Upload your screenshot',
                    'type' => 'file',
                    'mandatory' => true,
                    'config' => $config,
                ]],
            ]);
    }

    /**
     * Turns the question into a file upload question through the questions
     * route.
     */
    private function updateToFileUpload(MartQuestion $question, array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->putJson("/questionnaires/{$question->schedule_id}/questions", [
                'questions' => [[
                    'uuid' => $question->uuid,
                    'text' => $question->text,
                    'type' => 'file',
                    'mandatory' => false,
                    'config' => $config,
                ]],
            ]);
    }

    /**
     * A questionnaire with one range values question, saved through the
     * questionnaire route.
     */
    private function rangeValuesQuestion(array $config = ['options' => ['bad', 'ok', 'good'], 'defaultValue' => 1]): MartQuestion
    {
        $response = $this->storeRangeValuesQuestionnaire(['config' => $config])->assertOk();

        return MartSchedule::findOrFail($response->json('schedule.id'))->questions()->firstOrFail();
    }

    /**
     * Posts a questionnaire with one range values question, merged with
     * $fields, to the questionnaire route.
     */
    private function storeRangeValuesQuestionnaire(array $fields): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/questionnaires", [
                'questionnaire_id' => $this->project->id * 1000 + 500,
                'name' => 'Mood ' . uniqid(),
                'type' => 'single',
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'show_progress_bar' => true,
                'show_notifications' => false,
                'questions' => [[
                    'text' => 'How do you feel?',
                    'type' => 'range values',
                    'mandatory' => true,
                    ...$fields,
                ]],
            ]);
    }

    private function questionOfSchedule2(): MartQuestion
    {
        return MartSchedule::where('mart_project_id', $this->project->martProject()->id)
            ->where('questionnaire_id', $this->questionnaireId2)
            ->firstOrFail()
            ->questions()
            ->firstOrFail();
    }

    /**
     * Turns the question into a range values question through the questions
     * route.
     */
    private function updateToRangeValues(MartQuestion $question, array $config): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->putJson("/questionnaires/{$question->schedule_id}/questions", [
                'questions' => [[
                    'uuid' => $question->uuid,
                    'text' => $question->text,
                    'type' => 'range values',
                    'mandatory' => false,
                    'config' => $config,
                ]],
            ]);
    }

    /**
     * Submits one answer to the question's questionnaire and returns the
     * stored value.
     */
    private function submitRangeValuesAnswer(MartQuestion $question, array $answer): string
    {
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $question->schedule->questionnaire_id,
            'userId' => 'test@example.com',
            'participantId' => 'Participant_001',
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 300000,
            'answers' => ['1' => $answer],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $response = (new \App\Http\Controllers\MartApiController)->submitEntry($request, $this->case);
        $this->assertTrue($response->getData(true)['success']);

        return MartAnswer::where('question_uuid', $question->uuid)->sole()->answer_value;
    }
}
