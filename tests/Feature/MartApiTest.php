<?php

namespace Tests\Feature;

use App\Entry;
use App\MartPage;
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
                [
                    'name' => 'How are you feeling?',
                    'type' => 'scale',
                    'mandatory' => true,
                ],
                [
                    'name' => 'What are you doing?',
                    'type' => 'multiple choice',
                    'mandatory' => false,
                    'answers' => ['Working', 'Relaxing', 'Socializing', 'Other'],
                ],
                [
                    'name' => 'Any thoughts?',
                    'type' => 'text',
                    'mandatory' => false,
                ],
            ]),
        ]);

        // Create questionnaire schedules
        MartQuestionnaireSchedule::create([
            'project_id' => $this->project->id,
            'questionnaire_id' => 1,
            'name' => 'Daily Check-in',
            'type' => 'repeating',
            'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
            'end_date_time' => ['date' => '2025-12-31', 'time' => '21:00'],
            'show_progress_bar' => true,
            'show_notifications' => true,
            'notification_text' => 'Time for your check-in!',
            'daily_interval_duration' => 4,
            'min_break_between' => 180,
            'max_daily_submits' => 6,
            'daily_start_time' => '09:00',
            'daily_end_time' => '21:00',
            'quest_available_at' => 'randomTimeWithinInterval',
        ]);

        MartQuestionnaireSchedule::create([
            'project_id' => $this->project->id,
            'questionnaire_id' => 2,
            'name' => 'Weekly Reflection',
            'type' => 'single',
            'start_date_time' => ['date' => '2025-01-07', 'time' => '17:00'],
            'show_progress_bar' => true,
            'show_notifications' => true,
            'notification_text' => 'Weekly reflection time',
        ]);

        // Create MART pages
        MartPage::create([
            'project_id' => $this->project->id,
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
        $resource = $controller->getProjectStructure($this->project);
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

        // Check question sheets and scales exist
        $this->assertArrayHasKey('questionSheets', $structureArray);
        $this->assertArrayHasKey('scales', $structureArray);
        $this->assertArrayHasKey('pages', $structureArray);
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
                '1' => 7,
                '2' => ['Working', 'Socializing'],
                '3' => 'Feeling productive today',
            ],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]);

        $controller = new \App\Http\Controllers\MartApiController;
        $response = $controller->submitEntry($request, $this->case);

        $responseData = $response->getData(true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Entry created successfully', $responseData['message']);

        // Verify entry was created
        $entry = Entry::where('case_id', $this->case->id)->first();
        $this->assertNotNull($entry);

        // Verify MART metadata was stored
        $inputs = json_decode($entry->inputs, true);
        $this->assertArrayHasKey('_mart_metadata', $inputs);
        $this->assertEquals(1, $inputs['_mart_metadata']['questionnaire_id']);
        $this->assertEquals(300000, $inputs['_mart_metadata']['duration']);
        $this->assertEquals('Europe/Berlin', $inputs['_mart_metadata']['timezone']);
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

        // Verify device info was stored in user
        $this->user->refresh();
        $deviceInfo = json_decode($this->user->deviceID, true);
        $this->assertEquals('android', $deviceInfo['os']);
        $this->assertEquals('Pixel 7', $deviceInfo['model']);
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
        $resource = $controller->getProjectStructure($this->project);
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
