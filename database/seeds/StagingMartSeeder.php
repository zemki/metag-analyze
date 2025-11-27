<?php

use App\Cases;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StagingMartSeeder extends Seeder
{
    /**
     * Run the database seeds for staging MART setup.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('======================================');
        $this->command->info('   STAGING MART SETUP');
        $this->command->info('======================================');
        $this->command->newLine();

        // Step 1: Get number of users
        $userCount = null;
        while ($userCount === null || !is_numeric($userCount) || $userCount < 1) {
            $input = $this->command->ask('How many users do you want to create?', '1');
            if (is_numeric($input) && $input >= 1) {
                $userCount = (int) $input;
            } else {
                $this->command->error('Please enter a valid number (1 or greater)');
            }
        }

        $users = [];
        $allProjects = [];
        $allCases = [];
        $globalProjectCounter = 1;

        for ($i = 0; $i < $userCount; $i++) {
            $this->command->info("User " . ($i + 1) . ":");
            $email = $this->command->ask('  Email address');
            $password = $this->command->ask('  Password (default: password)', 'password');

            // Create or update user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => bcrypt($password),
                    'email_verified_at' => now(),
                    'deviceID' => 'STAGING_' . Str::random(10),
                ]
            );

            // Assign researcher role
            $researcherRole = Role::where('name', 'researcher')->first();
            if ($researcherRole) {
                $user->roles()->sync([$researcherRole->id]);
            }

            // Update password if user already exists
            if (!$user->wasRecentlyCreated) {
                $user->password = bcrypt($password);
                $user->save();
                $this->command->warn("  User already exists - password updated");
            } else {
                $this->command->info("  ✓ User created with researcher role");
            }

            // Ask how many projects for this user
            $projectCount = null;
            while ($projectCount === null || !is_numeric($projectCount) || $projectCount < 1) {
                $input = $this->command->ask('  How many MART projects for this user?', '1');
                if (is_numeric($input) && $input >= 1) {
                    $projectCount = (int) $input;
                } else {
                    $this->command->error('  Please enter a valid number (1 or greater)');
                }
            }

            // Create projects for this user
            $userProjects = [];
            for ($j = 0; $j < $projectCount; $j++) {
                $projectNumber = $j + 1;
                $this->command->info("  Project $projectNumber for {$email}:");
                $projectName = $this->command->ask('    Project name', "Staging MART Project $globalProjectCounter");

                $project = $this->createStagingMartProject($user, $projectName, $globalProjectCounter);
                $this->command->info("    ✓ Project created (ID: {$project->id})");

                $userProjects[] = $project;
                $allProjects[] = $project;
                $globalProjectCounter++;
            }

            // Create case for this user in each of their projects
            $participantCounter = 1;
            foreach ($userProjects as $project) {
                $participantName = 'participant-' . str_pad($participantCounter, 3, '0', STR_PAD_LEFT);

                $case = Cases::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'name' => $participantName,
                    'duration' => 'startDay:' . now()->format('d.m.Y') . '|lastDay:' . now()->addMonths(3)->format('d.m.Y'),
                    'created_at' => now(),
                ]);

                $allCases[] = [
                    'case' => $case,
                    'user_email' => $email,
                    'project_name' => $project->name,
                ];

                $participantCounter++;
            }

            $users[] = [
                'user' => $user,
                'email' => $email,
                'password' => $password,
                'projects' => $userProjects,
            ];

            $this->command->newLine();
        }

        $this->command->info("✓ Created " . count($allCases) . " cases across " . count($allProjects) . " projects");
        $this->command->newLine();

        // Display summary
        $this->displaySummary($users, $allProjects, $allCases);
    }

    /**
     * Create a simple staging MART project
     */
    private function createStagingMartProject($owner, $projectName, $projectNumber)
    {
        // Create main project
        $project = Project::create([
            'name' => $projectName,
            'description' => 'Staging MART project for API testing and development',
            'created_by' => $owner->id,
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
                                'content' => '<h1>Welcome to ' . $projectName . '</h1><p>This is a staging environment for API testing.</p>',
                                'buttonText' => 'Start',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        // Create MART project in MART database
        $martProject = MartProject::create(['main_project_id' => $project->id]);

        // Create MART pages
        $this->createMartPages($project, $martProject);

        // Create questionnaire schedule with questions
        $this->createQuestionnaireSchedule($project, $martProject, $projectNumber);

        return $project;
    }

    /**
     * Create MART pages for project
     */
    private function createMartPages($project, $martProject)
    {
        $inputs = json_decode($project->inputs, true);
        $martConfig = collect($inputs)->firstWhere('type', 'mart');

        if ($martConfig && isset($martConfig['projectOptions']['pages'])) {
            foreach ($martConfig['projectOptions']['pages'] as $pageData) {
                MartPage::create([
                    'mart_project_id' => $martProject->id,
                    'name' => $pageData['name'],
                    'content' => $pageData['content'],
                    'button_text' => $pageData['buttonText'],
                    'show_on_first_app_start' => $pageData['showOnFirstAppStart'],
                    'sort_order' => $pageData['sortOrder'],
                ]);
            }
        }
    }

    /**
     * Create a simple questionnaire schedule with questions
     */
    private function createQuestionnaireSchedule($project, $martProject, $projectNumber)
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addMonths(3)->endOfDay();

        // Create repeating schedule
        $schedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $projectNumber,
            'name' => 'Daily Check-in',
            'type' => 'repeating',
            'timing_config' => [
                'start_date_time' => [
                    'date' => $startDate->format('Y-m-d'),
                    'time' => '09:00',
                ],
                'end_date_time' => [
                    'date' => $endDate->format('Y-m-d'),
                    'time' => '21:00',
                ],
                'daily_interval_duration' => 4,
                'min_break_between' => 180,
                'max_daily_submits' => 5,
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

        // Create simple questions for testing
        MartQuestion::create([
            'schedule_id' => $schedule->id,
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
            'schedule_id' => $schedule->id,
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

        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 2,
            'text' => 'Any additional notes?',
            'type' => 'text',
            'config' => [],
            'is_mandatory' => false,
            'version' => 1,
        ]);
    }

    /**
     * Display summary of created data
     */
    private function displaySummary($users, $projects, $cases)
    {
        $this->command->newLine();
        $this->command->info('======================================');
        $this->command->info('   SUMMARY');
        $this->command->info('======================================');
        $this->command->newLine();

        // Users
        $this->command->info('USERS CREATED:');
        foreach ($users as $userData) {
            $this->command->line("  • Email: {$userData['email']}");
            $this->command->line("    Password: {$userData['password']}");
            $this->command->line("    User ID: {$userData['user']->id}");
            $this->command->line("    Projects owned: " . count($userData['projects']));
        }
        $this->command->newLine();

        // Projects
        $this->command->info('MART PROJECTS CREATED:');
        foreach ($projects as $project) {
            $this->command->line("  • {$project->name}");
            $this->command->line("    Project ID: {$project->id}");
        }
        $this->command->newLine();

        // Cases
        $this->command->info('CASES CREATED (Participant Links):');
        foreach ($cases as $caseData) {
            $this->command->line("  • Case ID: {$caseData['case']->id}");
            $this->command->line("    Participant: {$caseData['case']->name}");
            $this->command->line("    User: {$caseData['user_email']}");
            $this->command->line("    Project: {$caseData['project_name']}");
            $this->command->newLine();
        }

        // MART API Token
        $this->command->info('MART API AUTHENTICATION:');
        $this->command->line('  Bearer Token: mart_test_token_2025');
        $this->command->comment('  (This token is created by MartAuthSeeder - run it if not already done)');
        $this->command->newLine();

        // Example API calls
        $this->command->info('EXAMPLE API REQUESTS:');
        $this->command->newLine();

        if (!empty($cases)) {
            $firstCase = $cases[0];
            $projectId = $firstCase['case']->project_id;
            $caseId = $firstCase['case']->id;
            $userId = $firstCase['user_email'];
            $participantId = $firstCase['case']->name;

            // Get structure
            $this->command->comment('1. Get Project Structure:');
            $this->command->line("curl -X GET 'https://metagtest.uni-bremen.de/mart-api/projects/{$projectId}/structure' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025'");
            $this->command->newLine();

            // Submit entry
            $this->command->comment('2. Submit Entry:');
            $this->command->line("curl -X POST 'https://metagtest.uni-bremen.de/mart-api/cases/{$caseId}/submit' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025' \\");
            $this->command->line("  -H 'Content-Type: application/json' \\");
            $this->command->line("  -d '{");
            $this->command->line("    \"projectId\": {$projectId},");
            $this->command->line("    \"questionnaireId\": 1,");
            $this->command->line("    \"userId\": \"{$userId}\",");
            $this->command->line("    \"participantId\": \"{$participantId}\",");
            $this->command->line("    \"sheetId\": 1,");
            $this->command->line("    \"questionnaireStarted\": " . (now()->timestamp * 1000) . ",");
            $this->command->line("    \"questionnaireDuration\": 120000,");
            $this->command->line("    \"answers\": {\"0\": 7, \"1\": [0, 2], \"2\": \"Test note\"},");
            $this->command->line("    \"timestamp\": " . (now()->timestamp * 1000) . ",");
            $this->command->line("    \"timezone\": \"Europe/Berlin\"");
            $this->command->line("  }'");
            $this->command->newLine();

            // Device info
            $this->command->comment('3. Submit Device Info:');
            $this->command->line("curl -X POST 'https://metagtest.uni-bremen.de/mart-api/device-infos' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025' \\");
            $this->command->line("  -H 'Content-Type: application/json' \\");
            $this->command->line("  -d '{");
            $this->command->line("    \"projectId\": {$projectId},");
            $this->command->line("    \"userId\": \"{$userId}\",");
            $this->command->line("    \"participantId\": \"{$participantId}\",");
            $this->command->line("    \"os\": \"android\",");
            $this->command->line("    \"osVersion\": \"14\",");
            $this->command->line("    \"model\": \"Pixel 7\",");
            $this->command->line("    \"manufacturer\": \"Google\",");
            $this->command->line("    \"timestamp\": " . (now()->timestamp * 1000) . ",");
            $this->command->line("    \"timezone\": \"Europe/Berlin\"");
            $this->command->line("  }'");
        }

        $this->command->newLine();
        $this->command->info('======================================');
        $this->command->info('Staging MART setup complete!');
        $this->command->info('======================================');
    }
}
