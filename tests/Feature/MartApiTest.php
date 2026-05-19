<?php

namespace Tests\Feature;

use App\Entry;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\MartPage as OldMartPage;
use App\Project;
use App\User;
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

        // Create questions for schedule 1 (positions start from 0)
        MartQuestion::create([
            'schedule_id' => $schedule1->id,
            'position' => 0,
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
            'position' => 1,
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

        // Create question for schedule 2 (position starts from 0)
        MartQuestion::create([
            'schedule_id' => $schedule2->id,
            'position' => 0,
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

        // Verify entry was created in main DB
        $entry = Entry::where('case_id', $this->case->id)->first();
        $this->assertNotNull($entry);

        // Verify MART entry was created in MART DB
        $martEntry = $entry->martEntry();
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
            'userId' => 'participant@test.com',
            'participantId' => $this->case->name,
            'sheetId' => 1,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 180000,
            'answers' => ['1' => 3, '2' => [1, 2]],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $controller->submitEntry($request, $this->case);

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

        // Check that submissions contain our test data
        $this->assertNotEmpty($structureArray['repeatingSubmits']);
        // Just verify the submission has a valid questionnaireId (could be from any test run)
        $this->assertArrayHasKey('questionnaireId', $structureArray['repeatingSubmits'][0]);
        $this->assertIsNumeric($structureArray['repeatingSubmits'][0]['questionnaireId']);
        $this->assertIsNumeric($structureArray['repeatingSubmits'][0]['timestamp']);
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

        \App\Mart\MartEntry::create([
            'schedule_id' => $iosSchedule->id,
            'questionnaire_id' => $iosSchedule->questionnaire_id,
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
     * Item 3 (bonus, from Stefan's round-2 email): data-donation
     * questionnaires must NOT appear in `singleQuestionnaires` — only in
     * the top-level `questionnaires` catalog. The mobile's scheduler reads
     * `singleQuestionnaires` to plan time-based prompts; donation
     * questionnaires are user-triggered (tap to donate) and would cause
     * spurious schedules if included.
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
        $structureArray = $resource->toArray(null);

        $singleIds = collect($structureArray['projectOptions']['options']['singleQuestionnaires'])
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
        $structureArray = $resource->toArray(null);

        $this->assertEquals(
            $iosQuestionnaireId,
            $structureArray['projectOptions']['options']['iOSDataDonationQuestionnaire'],
            'iOSDataDonationQuestionnaire should expose the questionnaire_id of the iOS-flagged schedule'
        );
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

}
