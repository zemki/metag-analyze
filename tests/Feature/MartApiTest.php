<?php

namespace Tests\Feature;

use App\Entry;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\MartPage as OldMartPage;
use App\MartQuestionnaireSchedule;
use App\Project;
use App\User;
use Tests\TestCase;

class MartApiTest extends TestCase
{
    protected $rawToken;

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

        // Create MART project in MART database
        $martProject = MartProject::create(['main_project_id' => $this->project->id]);

        // Create questionnaire schedules in MART database with separate questions
        $schedule1 = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => 1,
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

        // Create questions for schedule 1
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
            'questionnaire_id' => 2,
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
    public function it_returns_project_structure_with_questionnaire_schedules()
    {
        // Test the resource directly to avoid authentication issues
        $controller = new \App\Http\Controllers\MartApiController;
        $request = \Illuminate\Http\Request::create('/test');
        $resource = $controller->getProjectStructure($request, $this->project);
        $structureArray = $resource->toArray(null);

        // Check project options exist and are properly serialized
        $this->assertArrayHasKey('projectOptions', $structureArray);

        // Get project options as array (handle Laravel resource)
        $projectOptions = $structureArray['projectOptions'];
        if (is_object($projectOptions) && method_exists($projectOptions, 'toArray')) {
            $projectOptions = $projectOptions->toArray(null);
        }

        $this->assertEquals($this->project->id, $projectOptions['projectId']);
        $this->assertEquals('Test MART Project', $projectOptions['projectName']);

        // Check questionnaire schedules exist
        $this->assertArrayHasKey('options', $projectOptions);
        $options = $projectOptions['options'];

        $this->assertArrayHasKey('repeatingQuestionnaires', $options);
        $this->assertArrayHasKey('singleQuestionnaires', $options);

        // Verify repeating questionnaire structure
        $this->assertCount(1, $options['repeatingQuestionnaires']);
        $repeating = $options['repeatingQuestionnaires'][0];

        $this->assertEquals(1, $repeating['questionnaireId']);
        $this->assertEquals('repeating', $repeating['type']);
        $this->assertEquals(4, $repeating['dailyIntervalDuration']);
        $this->assertEquals(180, $repeating['minBreakBetweenQuestionnaire']);
        $this->assertEquals(6, $repeating['maxDailySubmits']);
        $this->assertEquals('randomTimeWithinInterval', $repeating['questAvailableAt']);

        // Verify single questionnaire structure
        $this->assertCount(1, $options['singleQuestionnaires']);
        $single = $options['singleQuestionnaires'][0];

        $this->assertEquals(2, $single['questionnaireId']);
        $this->assertEquals('single', $single['type']);

        // Check questionnaires and scales exist
        $this->assertArrayHasKey('questionnaires', $structureArray);
        $this->assertArrayHasKey('scales', $structureArray);
        $this->assertArrayHasKey('pages', $structureArray);

        // Verify we have 2 questionnaires (one per schedule)
        $questionnaires = $structureArray['questionnaires'];
        $this->assertCount(2, $questionnaires);

        // Verify first questionnaire (repeating) has correct structure
        $questionnaire1 = is_object($questionnaires[0]) && method_exists($questionnaires[0], 'toArray')
            ? $questionnaires[0]->toArray(null)
            : $questionnaires[0];
        $this->assertEquals(1, $questionnaire1['questionnaireId']);
        $this->assertArrayHasKey('items', $questionnaire1);
        $this->assertCount(2, $questionnaire1['items']); // 2 questions

        // Verify second questionnaire (single) has correct structure
        $questionnaire2 = is_object($questionnaires[1]) && method_exists($questionnaires[1], 'toArray')
            ? $questionnaires[1]->toArray(null)
            : $questionnaires[1];
        $this->assertEquals(2, $questionnaire2['questionnaireId']);
        $this->assertArrayHasKey('items', $questionnaire2);
        $this->assertCount(1, $questionnaire2['items']); // 1 question
    }

    /** @test */
    public function it_submits_entry_with_questionnaire_id()
    {
        // Test controller method directly for core logic verification
        $request = new \Illuminate\Http\Request([
            'projectId' => $this->project->id,
            'questionnaireId' => 1,
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
        $this->assertEquals(1, $martEntry->questionnaire_id);
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
            'questionnaireId' => 1,
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
        $this->assertEquals(1, $structureArray['repeatingSubmits'][0]['questionnaireId']);
        $this->assertIsNumeric($structureArray['repeatingSubmits'][0]['timestamp']);
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
    public function it_returns_correct_schedule_format_for_mobile()
    {
        // Test the resource directly to verify mobile format
        $controller = new \App\Http\Controllers\MartApiController;
        $request = \Illuminate\Http\Request::create('/test');
        $resource = $controller->getProjectStructure($request, $this->project);
        $structureArray = $resource->toArray(null);

        // Get project options as array
        $projectOptions = $structureArray['projectOptions'];
        if (is_object($projectOptions) && method_exists($projectOptions, 'toArray')) {
            $projectOptions = $projectOptions->toArray(null);
        }

        // Check that the schedule format matches martTypes.ts expectations
        $repeatingQuest = $projectOptions['options']['repeatingQuestionnaires'][0];

        // These fields should exist for repeating questionnaires
        $this->assertArrayHasKey('questionnaireId', $repeatingQuest);
        $this->assertArrayHasKey('type', $repeatingQuest);
        $this->assertArrayHasKey('startDateAndTime', $repeatingQuest);
        $this->assertArrayHasKey('endDateAndTime', $repeatingQuest);
        $this->assertArrayHasKey('minBreakBetweenQuestionnaire', $repeatingQuest);
        $this->assertArrayHasKey('dailyIntervalDuration', $repeatingQuest);
        $this->assertArrayHasKey('maxDailySubmits', $repeatingQuest);
        $this->assertArrayHasKey('dailyStartTime', $repeatingQuest);
        $this->assertArrayHasKey('dailyEndTime', $repeatingQuest);
        $this->assertArrayHasKey('questAvailableAt', $repeatingQuest);

        // Check date/time format
        $this->assertIsArray($repeatingQuest['startDateAndTime']);
        $this->assertArrayHasKey('date', $repeatingQuest['startDateAndTime']);
        $this->assertArrayHasKey('time', $repeatingQuest['startDateAndTime']);
    }
}
