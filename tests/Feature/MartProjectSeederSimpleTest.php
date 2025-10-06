<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\MartPage;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MartProjectSeederSimpleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that we can create a user correctly
     *
     * @return void
     */
    public function test_can_create_mart_user()
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => bcrypt('password'),
                'deviceID' => 'TEST_DEVICE_123',
            ]
        );

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertNotNull($user->id);
    }

    /**
     * Test that we can create a MART project correctly
     *
     * @return void
     */
    public function test_can_create_mart_project()
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => bcrypt('password'),
                'deviceID' => 'TEST_DEVICE_123',
            ]
        );

        $project = Project::create([
            'name' => 'Test MART Project',
            'description' => 'A test MART project',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Test Questionnaire',
                    'projectOptions' => [
                        'pages' => [
                            [
                                'name' => 'Welcome',
                                'content' => '<h1>Welcome</h1>',
                                'buttonText' => 'Continue',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'How do you feel?',
                    'type' => 'one choice',
                    'mandatory' => true,
                    'answers' => ['Good', 'Bad'],
                    'numberofanswer' => 2,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
            ]),
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test MART Project',
        ]);

        $this->assertTrue($project->isMartProject());
    }

    /**
     * Test that we can create MartPage records
     *
     * @return void
     */
    public function test_can_create_mart_pages()
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => bcrypt('password'),
                'deviceID' => 'TEST_DEVICE_123',
            ]
        );

        $project = Project::create([
            'name' => 'Test MART Project',
            'description' => 'A test MART project',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Test Questionnaire',
                    'projectOptions' => [
                        'pages' => [],
                    ],
                ],
            ]),
        ]);

        $page = MartPage::create([
            'project_id' => $project->id,
            'name' => 'Welcome',
            'content' => '<h1>Welcome to the study</h1>',
            'button_text' => 'Continue',
            'show_on_first_app_start' => true,
            'sort_order' => 0,
        ]);

        $this->assertDatabaseHas('mart_pages', [
            'project_id' => $project->id,
            'name' => 'Welcome',
        ]);
    }

    /**
     * Test that we can create Cases and Entries
     *
     * @return void
     */
    public function test_can_create_cases_and_entries()
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'password' => bcrypt('password'),
                'deviceID' => 'TEST_DEVICE_123',
            ]
        );

        $project = Project::create([
            'name' => 'Test MART Project',
            'description' => 'A test MART project',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Test Questionnaire',
                    'projectOptions' => ['pages' => []],
                ],
            ]),
        ]);

        $case = Cases::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'name' => 'Test_Participant_001',
            'duration' => 'value:45min',
        ]);

        $entry = Entry::create([
            'case_id' => $case->id,
            'begin' => '2025-07-17 10:00:00',
            'end' => '2025-07-17 10:03:00',
            'inputs' => json_encode([
                'How do you feel?' => 'Good',
                'Stress level' => 5,
            ]),
            'media_id' => null,
        ]);

        $this->assertDatabaseHas('cases', [
            'project_id' => $project->id,
            'name' => 'Test_Participant_001',
        ]);

        $this->assertDatabaseHas('entries', [
            'case_id' => $case->id,
        ]);

        $entryData = json_decode($entry->inputs, true);
        $this->assertEquals('Good', $entryData['How do you feel?']);
        $this->assertEquals(5, $entryData['Stress level']);
    }

}
