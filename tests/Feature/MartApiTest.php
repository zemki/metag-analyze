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

        // Create questions for schedule 1 (positions start from 0)
        MartQuestion::create([
            'mart_questionnaire_id' => $schedule1->id,
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
            'mart_questionnaire_id' => $schedule1->id,
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

        // Create question for schedule 2 (position starts from 0)
        MartQuestion::create([
            'mart_questionnaire_id' => $schedule2->id,
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

}
