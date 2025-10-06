<?php

use App\Cases;
use App\Entry;
use App\Mart\MartAnswer;
use App\Mart\MartEntry;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MartProjectSeeder extends Seeder
{
    /**
     * Run the database seeds for MART projects.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating MART projects...');

        // Ask if user wants ESM data
        $includeEsmData = $this->command->confirm(
            'Do you want to include ESM (Experience Sampling Method) data in MART projects?',
            true // Default to yes
        );

        // Get or create admin user (same approach as RealisticDataSeeder)
        $user = User::find(1);

        if (! $user) {
            $this->command->info('No admin user found, creating one...');
            $user = User::firstOrCreate(
                ['email' => 'admin@metag-analyze.test'],
                [
                    'password' => bcrypt('admin123'),
                    'deviceID' => 'ADMIN_' . Str::random(10),
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info("Assigning MART projects to user: {$user->email} (ID: {$user->id})");

        // Create MART projects
        $projects = [
            $this->createWellbeingProject($user, $includeEsmData),
            $this->createStressStudyProject($user, $includeEsmData),
            $this->createSocialMediaProject($user, $includeEsmData),
            $this->createConsultableDemoProject($user, $includeEsmData),
        ];

        $this->command->info('Created ' . count($projects) . ' MART projects.');

        if ($includeEsmData) {
            $this->command->info('ESM data included: Cases with realistic mobile entries.');
        } else {
            $this->command->info('ESM data skipped: Projects created without cases/entries.');
        }

        $this->command->info('MART projects seeded successfully!');
    }

    /**
     * Create a wellbeing tracking MART project
     */
    private function createWellbeingProject($user, $includeEsmData)
    {
        $project = Project::create([
            'name' => 'Daily Wellbeing Tracker',
            'description' => 'A mobile experience sampling study tracking daily mood, stress levels, and activities to understand patterns in psychological wellbeing.',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false, // MART projects don't use entities
            'entity_name' => null,
            'inputs' => json_encode([
                // MART configuration
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Daily Wellbeing Check-in',
                    'projectOptions' => [
                        'pages' => [
                            [
                                'name' => 'Welcome',
                                'content' => '<h1>Welcome to the Daily Wellbeing Study</h1><p>Thank you for participating in our research. Over the next 14 days, you\'ll receive notifications to complete brief surveys about your mood and activities.</p><p><strong>Important:</strong> Please respond as honestly as possible. Your responses are completely anonymous.</p>',
                                'buttonText' => 'Start Study',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                            [
                                'name' => 'Instructions',
                                'content' => '<h2>How it works</h2><ul><li>You\'ll receive 5 random notifications per day</li><li>Each survey takes 2-3 minutes</li><li>Answer based on how you feel <em>right now</em></li><li>Don\'t worry if you miss some - just answer when you can</li></ul>',
                                'buttonText' => 'Got it!',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 1,
                            ],
                        ],
                    ],
                ],
                // Questionnaire questions
                [
                    'name' => 'How are you feeling right now?',
                    'type' => 'one choice',
                    'mandatory' => true,
                    'answers' => ['Very bad', 'Bad', 'Neutral', 'Good', 'Very good'],
                    'numberofanswer' => 5,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Rate your current stress level',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 0,
                        'maxValue' => 10,
                        'steps' => 1,
                    ],
                ],
                [
                    'name' => 'What are you doing right now?',
                    'type' => 'multiple choice',
                    'mandatory' => false,
                    'answers' => ['Working/studying', 'Socializing', 'Exercising', 'Eating', 'Traveling', 'Relaxing', 'Other'],
                    'numberofanswer' => 7,
                    'martMetadata' => [
                        'originalType' => 'checkbox',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Any additional thoughts or feelings?',
                    'type' => 'text',
                    'mandatory' => false,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'textarea',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
            ]),
        ]);

        // Create MART pages
        $this->createMartPages($project);

        // Create questionnaire schedules
        $this->createQuestionnaireSchedules($project, 'wellbeing');

        if ($includeEsmData) {
            $this->createEsmCases($project, 'wellbeing');
        }

        return $project;
    }

    /**
     * Create a stress study MART project
     */
    private function createStressStudyProject($user, $includeEsmData)
    {
        $project = Project::create([
            'name' => 'Workplace Stress Research',
            'description' => 'An ESM study examining workplace stress patterns, coping mechanisms, and their impact on performance and wellbeing among office workers.',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                // MART configuration
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Workplace Stress Monitor',
                    'projectOptions' => [
                        'pages' => [
                            [
                                'name' => 'Study Information',
                                'content' => '<h1>Workplace Stress Research</h1><p>This study investigates how workplace stress affects daily performance and wellbeing.</p><p><strong>Duration:</strong> 21 days<br><strong>Frequency:</strong> 4 times per day during work hours</p><p>Your participation helps us understand stress patterns in modern workplaces.</p>',
                                'buttonText' => 'Continue',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                        ],
                    ],
                ],
                // Questionnaire questions
                [
                    'name' => 'Rate your current stress level',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 7,
                        'steps' => 1,
                    ],
                ],
                [
                    'name' => 'What is your primary work activity right now?',
                    'type' => 'one choice',
                    'mandatory' => true,
                    'answers' => ['Email/communication', 'Meetings', 'Focused work', 'Planning/organizing', 'Problem-solving', 'Break/pause', 'Other'],
                    'numberofanswer' => 7,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'How demanding is your current task?',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 5,
                        'steps' => 1,
                    ],
                ],
                [
                    'name' => 'Which stressors are affecting you right now?',
                    'type' => 'multiple choice',
                    'mandatory' => false,
                    'answers' => ['Time pressure', 'Workload', 'Difficult colleagues', 'Technical issues', 'Unclear instructions', 'Interruptions', 'None'],
                    'numberofanswer' => 7,
                    'martMetadata' => [
                        'originalType' => 'checkbox',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Rate your current energy level',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 10,
                        'steps' => 1,
                    ],
                ],
            ]),
        ]);

        $this->createMartPages($project);

        // Create questionnaire schedules
        $this->createQuestionnaireSchedules($project, 'workplace');

        if ($includeEsmData) {
            $this->createEsmCases($project, 'stress');
        }

        return $project;
    }

    /**
     * Create a social media usage MART project
     */
    private function createSocialMediaProject($user, $includeEsmData)
    {
        $project = Project::create([
            'name' => 'Social Media & Mood Study',
            'description' => 'An experience sampling study exploring the relationship between social media usage patterns and emotional wellbeing in young adults.',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                // MART configuration
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Social Media & Mood Check',
                    'projectOptions' => [
                        'pages' => [
                            [
                                'name' => 'Study Overview',
                                'content' => '<h1>Social Media & Mood Study</h1><p>Help us understand how social media affects daily mood and wellbeing.</p><p><strong>What we\'ll ask:</strong></p><ul><li>Your current mood</li><li>Recent social media activity</li><li>Social interactions</li></ul><p>All responses are anonymous and confidential.</p>',
                                'buttonText' => 'Begin',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                        ],
                    ],
                ],
                // Questionnaire questions
                [
                    'name' => 'How would you describe your current mood?',
                    'type' => 'one choice',
                    'mandatory' => true,
                    'answers' => ['Very negative', 'Negative', 'Neutral', 'Positive', 'Very positive'],
                    'numberofanswer' => 5,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'In the past hour, how much time did you spend on social media?',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 0,
                        'maxValue' => 60,
                        'steps' => 5,
                    ],
                ],
                [
                    'name' => 'Which social media platforms did you use?',
                    'type' => 'multiple choice',
                    'mandatory' => false,
                    'answers' => ['Instagram', 'Facebook', 'Twitter/X', 'TikTok', 'YouTube', 'LinkedIn', 'Other', 'None'],
                    'numberofanswer' => 8,
                    'martMetadata' => [
                        'originalType' => 'checkbox',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'How did social media make you feel?',
                    'type' => 'one choice',
                    'mandatory' => false,
                    'answers' => ['Much worse', 'Worse', 'No change', 'Better', 'Much better', 'Did not use social media'],
                    'numberofanswer' => 6,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Rate your current social connectedness',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 7,
                        'steps' => 1,
                    ],
                ],
            ]),
        ]);

        $this->createMartPages($project);

        // Create questionnaire schedules
        $this->createQuestionnaireSchedules($project, 'social');

        if ($includeEsmData) {
            $this->createEsmCases($project, 'social_media');
        }

        return $project;
    }

    /**
     * Create a consultable demo MART project with clear, readable entries
     */
    private function createConsultableDemoProject($user, $includeEsmData)
    {
        $project = Project::create([
            'name' => 'ESM Demo - Consultable Entries',
            'description' => 'A demonstration ESM project with clear, readable entries for testing and consultation. Contains structured data that is easy to analyze and understand.',
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false,
            'entity_name' => null,
            'inputs' => json_encode([
                // MART configuration
                [
                    'type' => 'mart',
                    'questionnaireName' => 'Daily Experience Survey',
                    'projectOptions' => [
                        'pages' => [
                            [
                                'name' => 'Demo Instructions',
                                'content' => '<h1>ESM Demo Project</h1><p>This is a demonstration project showing how ESM data collection works.</p><p><strong>Purpose:</strong> To provide clear, consultable entries that researchers can easily analyze.</p><ul><li>Questions are simple and clear</li><li>Responses are realistic and varied</li><li>Data structure is optimized for analysis</li></ul>',
                                'buttonText' => 'Start Demo',
                                'showOnFirstAppStart' => true,
                                'sortOrder' => 0,
                            ],
                        ],
                    ],
                ],
                // Simple, clear questions
                [
                    'name' => 'Current mood (1-10 scale)',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 10,
                        'steps' => 1,
                    ],
                ],
                [
                    'name' => 'Primary activity right now',
                    'type' => 'one choice',
                    'mandatory' => true,
                    'answers' => ['Working', 'Studying', 'Socializing', 'Exercising', 'Eating', 'Commuting', 'Relaxing', 'Sleeping'],
                    'numberofanswer' => 8,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Energy level (1-5 scale)',
                    'type' => 'scale',
                    'mandatory' => true,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'range',
                        'minValue' => 1,
                        'maxValue' => 5,
                        'steps' => 1,
                    ],
                ],
                [
                    'name' => 'Location',
                    'type' => 'one choice',
                    'mandatory' => false,
                    'answers' => ['Home', 'Office', 'School', 'Transportation', 'Restaurant/Cafe', 'Gym', 'Outdoors', 'Other'],
                    'numberofanswer' => 8,
                    'martMetadata' => [
                        'originalType' => 'radio',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
                [
                    'name' => 'Notable thoughts or events',
                    'type' => 'text',
                    'mandatory' => false,
                    'answers' => [],
                    'martMetadata' => [
                        'originalType' => 'textarea',
                        'minValue' => null,
                        'maxValue' => null,
                        'steps' => null,
                    ],
                ],
            ]),
        ]);

        $this->createMartPages($project);

        // Create questionnaire schedules
        $this->createQuestionnaireSchedules($project, 'demo');

        if ($includeEsmData) {
            $this->createConsultableDemoData($project);
        }

        return $project;
    }

    /**
     * Create highly structured, consultable demo data
     */
    private function createConsultableDemoData($project)
    {
        // Create 3 demo participants with different patterns
        $participants = [
            [
                'name' => 'Demo_Participant_A_HighMood',
                'pattern' => 'high_mood',
                'entries' => 25,
            ],
            [
                'name' => 'Demo_Participant_B_WorkStress',
                'pattern' => 'work_stress',
                'entries' => 30,
            ],
            [
                'name' => 'Demo_Participant_C_Balanced',
                'pattern' => 'balanced',
                'entries' => 28,
            ],
        ];

        foreach ($participants as $participantData) {
            $case = Cases::create([
                'project_id' => $project->id,
                'user_id' => $project->created_by,
                'name' => $participantData['name'],
                'duration' => $this->calculateCompletedDuration($participantData['entries']),
                'created_at' => now()->subDays(rand(15, 25)), // Created 15-25 days ago
            ]);

            $this->createPatternedEntries($case, $participantData['entries'], $participantData['pattern']);
        }
    }

    /**
     * Create entries following specific patterns for easy consultation (both main and MART DB)
     */
    private function createPatternedEntries($case, $entryCount, $pattern)
    {
        $startDate = $case->created_at;

        // Get MART project and schedules
        $martProject = MartProject::where('main_project_id', $case->project_id)->first();
        if (!$martProject) {
            return;
        }

        // Get the demo schedule
        $schedule = MartSchedule::where('mart_project_id', $martProject->id)
            ->where('questionnaire_id', 1)
            ->first();

        if (!$schedule) {
            return;
        }

        // Get questions for this schedule
        $questions = MartQuestion::where('schedule_id', $schedule->id)
            ->orderBy('position')
            ->get();

        for ($i = 0; $i < $entryCount; $i++) {
            $entryDate = $startDate->copy()
                ->addDays(rand(0, 6)) // Within 7-day study period
                ->addHours(rand(7, 22))
                ->addMinutes(rand(0, 59));

            $inputs = $this->generatePatternedInputs($pattern, $i, $entryDate);
            $durationMinutes = rand(2, 5);
            $endDate = $entryDate->copy()->addMinutes($durationMinutes);

            // Use cross-DB transaction
            DB::connection('mysql')->beginTransaction();
            DB::connection('mart')->beginTransaction();

            try {
                // Create Entry in main DB
                $entry = Entry::create([
                    'case_id' => $case->id,
                    'begin' => $entryDate->format('Y-m-d H:i:s'),
                    'end' => $endDate->format('Y-m-d H:i:s'),
                    'inputs' => json_encode($inputs),
                    'media_id' => null,
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate,
                ]);

                // Create MartEntry in MART DB
                $martEntry = MartEntry::create([
                    'main_entry_id' => $entry->id,
                    'schedule_id' => $schedule->id,
                    'questionnaire_id' => $schedule->questionnaire_id,
                    'participant_id' => $case->name,
                    'user_id' => 'demo@example.com',
                    'started_at' => $entryDate,
                    'completed_at' => $endDate,
                    'duration_ms' => $durationMinutes * 60 * 1000,
                    'timezone' => 'Europe/Berlin',
                    'timestamp' => $entryDate->timestamp * 1000,
                ]);

                // Create MartAnswer records for each question
                foreach ($questions as $question) {
                    $answerValue = $inputs[$question->text] ?? null;

                    if ($answerValue !== null) {
                        MartAnswer::create([
                            'entry_id' => $martEntry->id,
                            'question_uuid' => $question->uuid,
                            'question_version' => $question->version,
                            'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                        ]);
                    }
                }

                DB::connection('mysql')->commit();
                DB::connection('mart')->commit();
            } catch (\Exception $e) {
                DB::connection('mysql')->rollBack();
                DB::connection('mart')->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Generate patterned inputs based on participant type
     */
    private function generatePatternedInputs($pattern, $entryIndex, $entryDate)
    {
        $hour = (int) $entryDate->format('H');
        $isWeekend = $entryDate->isWeekend();

        switch ($pattern) {
            case 'high_mood':
                return [
                    'Current mood (1-10 scale)' => rand(7, 10), // Always high mood
                    'Primary activity right now' => $this->getTimeBasedActivity($hour, $isWeekend, ['Socializing', 'Exercising', 'Relaxing']),
                    'Energy level (1-5 scale)' => rand(3, 5), // Good energy
                    'Location' => $this->getTimeBasedLocation($hour, $isWeekend),
                    'Notable thoughts or events' => $this->getPositiveThoughts($entryIndex),
                ];

            case 'work_stress':
                $mood = $hour >= 9 && $hour <= 17 && ! $isWeekend ? rand(3, 6) : rand(6, 9); // Lower during work

                return [
                    'Current mood (1-10 scale)' => $mood,
                    'Primary activity right now' => $this->getTimeBasedActivity($hour, $isWeekend, ['Working']),
                    'Energy level (1-5 scale)' => $hour >= 9 && $hour <= 17 && ! $isWeekend ? rand(1, 3) : rand(3, 5),
                    'Location' => $hour >= 9 && $hour <= 17 && ! $isWeekend ? 'Office' : $this->getTimeBasedLocation($hour, $isWeekend),
                    'Notable thoughts or events' => $this->getWorkStressThoughts($entryIndex, $hour, $isWeekend),
                ];

            case 'balanced':
                return [
                    'Current mood (1-10 scale)' => rand(5, 8), // Moderate mood
                    'Primary activity right now' => $this->getTimeBasedActivity($hour, $isWeekend),
                    'Energy level (1-5 scale)' => rand(2, 4), // Moderate energy
                    'Location' => $this->getTimeBasedLocation($hour, $isWeekend),
                    'Notable thoughts or events' => $this->getBalancedThoughts($entryIndex),
                ];

            default:
                return [
                    'Current mood (1-10 scale)' => rand(1, 10),
                    'Primary activity right now' => 'Relaxing',
                    'Energy level (1-5 scale)' => rand(1, 5),
                    'Location' => 'Home',
                    'Notable thoughts or events' => 'Regular day',
                ];
        }
    }

    /**
     * Get activity based on time and preferences
     */
    private function getTimeBasedActivity($hour, $isWeekend, $preferences = [])
    {
        $activities = ['Working', 'Studying', 'Socializing', 'Exercising', 'Eating', 'Commuting', 'Relaxing'];

        if (! empty($preferences) && rand(0, 2) > 0) { // 66% chance to use preference
            return $preferences[array_rand($preferences)];
        }

        // Time-based logic
        if ($hour >= 7 && $hour <= 9) {
            return 'Eating';
        } // Breakfast/morning
        if ($hour >= 9 && $hour <= 17 && ! $isWeekend) {
            return 'Working';
        }
        if ($hour >= 17 && $hour <= 19) {
            return 'Commuting';
        }
        if ($hour >= 19 && $hour <= 21) {
            return 'Eating';
        } // Dinner
        if ($hour >= 22 || $hour <= 6) {
            return 'Relaxing';
        } // Evening/night

        return $activities[array_rand($activities)];
    }

    /**
     * Get location based on time
     */
    private function getTimeBasedLocation($hour, $isWeekend)
    {
        if ($hour >= 9 && $hour <= 17 && ! $isWeekend) {
            return rand(0, 1) ? 'Office' : 'Home'; // Work from home mix
        }
        if ($hour >= 7 && $hour <= 22) {
            $locations = ['Home', 'Restaurant/Cafe', 'Gym', 'Outdoors'];

            return $locations[array_rand($locations)];
        }

        return 'Home'; // Night/early morning
    }

    /**
     * Generate positive thoughts for high mood participant
     */
    private function getPositiveThoughts($index)
    {
        $thoughts = [
            'Great day so far!',
            'Feeling energized and motivated',
            'Had a wonderful conversation with friends',
            'Accomplished my goals today',
            'Beautiful weather lifted my spirits',
            'Enjoyed a delicious meal',
            'Feeling grateful for good health',
            'Made progress on personal projects',
            'Spending quality time with family',
            'Listened to uplifting music',
        ];

        return $index < count($thoughts) ? $thoughts[$index] : $thoughts[rand(0, count($thoughts) - 1)];
    }

    /**
     * Generate work stress thoughts
     */
    private function getWorkStressThoughts($index, $hour, $isWeekend)
    {
        if ($isWeekend || $hour < 9 || $hour > 17) {
            $relaxedThoughts = [
                'Finally some time to unwind',
                'Weekend feels too short',
                'Trying to disconnect from work',
                'Enjoying some personal time',
                'Preparing for the week ahead',
            ];

            return $relaxedThoughts[array_rand($relaxedThoughts)];
        }

        $stressThoughts = [
            'Deadline pressure is intense today',
            'Too many meetings, hard to focus',
            'Email overload - feeling overwhelmed',
            'System issues causing delays',
            'Difficult client meeting this morning',
            'Juggling multiple urgent projects',
            'Coffee break - much needed!',
            'Team collaboration going well',
            'Problem-solving complex technical issue',
            'Looking forward to lunch break',
        ];

        return $stressThoughts[array_rand($stressThoughts)];
    }

    /**
     * Generate balanced thoughts
     */
    private function getBalancedThoughts($index)
    {
        $thoughts = [
            'Regular productive day',
            'Finding good work-life balance',
            'Some challenges but manageable',
            'Making steady progress',
            'Taking things one step at a time',
            'Appreciating small moments',
            'Moderate energy, feeling okay',
            'Planning ahead for tomorrow',
            'Connecting with colleagues',
            'Routine day with some highlights',
        ];

        return $index < count($thoughts) ? $thoughts[$index] : $thoughts[rand(0, count($thoughts) - 1)];
    }

    /**
     * Create MART pages for a project in MART database
     */
    private function createMartPages($project)
    {
        // Get or create MartProject
        $martProject = MartProject::firstOrCreate(['main_project_id' => $project->id]);

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
     * Create questionnaire schedules for a MART project in MART database
     */
    private function createQuestionnaireSchedules($project, $type)
    {
        // Get or create MartProject
        $martProject = MartProject::firstOrCreate(['main_project_id' => $project->id]);

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addMonths(3)->endOfDay();

        // Extract questions from project to assign to schedules
        $questions = $this->extractQuestionsFromProject($project);

        switch ($type) {
            case 'wellbeing':
                // Create a repeating questionnaire for daily wellbeing check-ins
                $schedule = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => 1,
                    'name' => 'Daily Wellbeing Check-in',
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
                        'daily_interval_duration' => 4, // Every 4 hours
                        'min_break_between' => 180, // 3 hours minimum
                        'max_daily_submits' => 6,
                        'daily_start_time' => '09:00',
                        'daily_end_time' => '21:00',
                        'quest_available_at' => 'randomTimeWithinInterval',
                    ],
                    'notification_config' => [
                        'show_progress_bar' => true,
                        'show_notifications' => true,
                        'notification_text' => 'Time for your wellbeing check-in!',
                    ],
                ]);

                // Create individual MartQuestion records
                $this->createQuestionsForSchedule($schedule, $questions);
                break;

            case 'workplace':
                // Create a repeating questionnaire for workplace stress
                $schedule1 = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => 1,
                    'name' => 'Workplace Stress Monitor',
                    'type' => 'repeating',
                    'timing_config' => [
                        'start_date_time' => [
                            'date' => $startDate->format('Y-m-d'),
                            'time' => '08:00',
                        ],
                        'end_date_time' => [
                            'date' => $endDate->format('Y-m-d'),
                            'time' => '18:00',
                        ],
                        'daily_interval_duration' => 3, // Every 3 hours
                        'min_break_between' => 120, // 2 hours minimum
                        'max_daily_submits' => 4,
                        'daily_start_time' => '08:00',
                        'daily_end_time' => '18:00',
                        'quest_available_at' => 'startOfInterval',
                    ],
                    'notification_config' => [
                        'show_progress_bar' => true,
                        'show_notifications' => true,
                        'notification_text' => 'Quick stress check - how are you doing?',
                    ],
                ]);
                $this->createQuestionsForSchedule($schedule1, $questions);

                // Add a single questionnaire for end-of-week reflection
                $schedule2 = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => 2,
                    'name' => 'Weekly Reflection',
                    'type' => 'single',
                    'timing_config' => [
                        'start_date_time' => [
                            'date' => Carbon::now()->next('Friday')->format('Y-m-d'),
                            'time' => '17:00',
                        ],
                        'show_after_repeating' => [
                            'repeatingQuestId' => 1,
                            'showAfterAmount' => 10,
                        ],
                    ],
                    'notification_config' => [
                        'show_progress_bar' => true,
                        'show_notifications' => true,
                        'notification_text' => 'Time for your weekly reflection',
                    ],
                ]);
                $this->createQuestionsForSchedule($schedule2, $questions);
                break;

            case 'social':
                // Create a repeating questionnaire for social media usage
                $schedule = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => 1,
                    'name' => 'Social Media & Mood Check',
                    'type' => 'repeating',
                    'timing_config' => [
                        'start_date_time' => [
                            'date' => $startDate->format('Y-m-d'),
                            'time' => '10:00',
                        ],
                        'end_date_time' => [
                            'date' => $endDate->format('Y-m-d'),
                            'time' => '22:00',
                        ],
                        'daily_interval_duration' => 6, // Every 6 hours
                        'min_break_between' => 300, // 5 hours minimum
                        'max_daily_submits' => 3,
                        'daily_start_time' => '10:00',
                        'daily_end_time' => '22:00',
                        'quest_available_at' => 'randomTimeWithinInterval',
                    ],
                    'notification_config' => [
                        'show_progress_bar' => true,
                        'show_notifications' => true,
                        'notification_text' => 'How has social media affected your mood?',
                    ],
                ]);
                $this->createQuestionsForSchedule($schedule, $questions);
                break;

            case 'demo':
                // Create a demo questionnaire with more frequent prompts for testing
                $schedule = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => 1,
                    'name' => 'Demo Experience Survey',
                    'type' => 'repeating',
                    'timing_config' => [
                        'start_date_time' => [
                            'date' => $startDate->format('Y-m-d'),
                            'time' => '00:00',
                        ],
                        'end_date_time' => [
                            'date' => $endDate->format('Y-m-d'),
                            'time' => '23:59',
                        ],
                        'daily_interval_duration' => 2, // Every 2 hours for demo
                        'min_break_between' => 60, // 1 hour minimum
                        'max_daily_submits' => 12,
                        'daily_start_time' => '00:00',
                        'daily_end_time' => '23:59',
                        'quest_available_at' => 'startOfInterval',
                    ],
                    'notification_config' => [
                        'show_progress_bar' => true,
                        'show_notifications' => false, // No notifications for demo
                    ],
                ]);
                $this->createQuestionsForSchedule($schedule, $questions);
                break;
        }
    }

    /**
     * Create individual MartQuestion records for a schedule
     */
    private function createQuestionsForSchedule($schedule, $questions)
    {
        foreach ($questions as $position => $questionData) {
            // Map question types from old format to new format
            $type = $this->mapQuestionType($questionData['type'] ?? 'text');

            // Build config from question data
            $config = [];
            if (isset($questionData['martMetadata'])) {
                $config = $questionData['martMetadata'];
            }
            if (isset($questionData['answers']) && !empty($questionData['answers'])) {
                $config['options'] = $questionData['answers'];
            }

            MartQuestion::create([
                'schedule_id' => $schedule->id,
                'position' => $position + 1,
                'text' => $questionData['name'] ?? "Question " . ($position + 1),
                'type' => $type,
                'config' => $config,
                'is_mandatory' => $questionData['mandatory'] ?? true,
                'version' => 1,
            ]);
        }
    }

    /**
     * Map old question types to new types
     */
    private function mapQuestionType($oldType)
    {
        $mapping = [
            'one choice' => 'multiple choice',  // Will be handled as single-select in config
            'multiple choice' => 'multiple choice',
            'scale' => 'scale',
            'text' => 'text',
        ];

        return $mapping[$oldType] ?? 'text';
    }

    /**
     * Create ESM cases and entries for a project
     */
    private function createEsmCases($project, $type)
    {
        $participants = [
            ['name' => 'Participant_001', 'responses' => rand(20, 40)],
            ['name' => 'Participant_002', 'responses' => rand(15, 35)],
            ['name' => 'Participant_003', 'responses' => rand(25, 45)],
            ['name' => 'Participant_004', 'responses' => rand(18, 38)],
            ['name' => 'Participant_005', 'responses' => rand(22, 42)],
        ];

        foreach ($participants as $participant) {
            $case = Cases::create([
                'project_id' => $project->id,
                'user_id' => $project->created_by,
                'name' => $participant['name'],
                'duration' => $this->calculateDuration($participant['responses']),
                'created_at' => now()->subDays(rand(1, 7)),
            ]);

            $this->createEsmEntries($case, $participant['responses'], $type);
        }
    }

    /**
     * Create ESM entries for a case (both main DB and MART DB)
     */
    private function createEsmEntries($case, $responseCount, $type)
    {
        $startDate = $case->created_at;
        $studyDuration = $this->getStudyDuration($type); // Get duration based on study type

        // Get MART project and schedules
        $martProject = MartProject::where('main_project_id', $case->project_id)->first();
        if (!$martProject) {
            return;
        }

        // Get the first repeating schedule (questionnaire_id = 1)
        $schedule = MartSchedule::where('mart_project_id', $martProject->id)
            ->where('questionnaire_id', 1)
            ->first();

        if (!$schedule) {
            return;
        }

        // Get questions for this schedule
        $questions = MartQuestion::where('schedule_id', $schedule->id)
            ->orderBy('position')
            ->get();

        for ($i = 0; $i < $responseCount; $i++) {
            $entryDate = $startDate->copy()->addDays(rand(0, $studyDuration - 1))
                ->addHours(rand(8, 20))
                ->addMinutes(rand(0, 59));

            $inputs = $this->generateRealisticInputs($type, $i);
            $durationMinutes = rand(2, 5);
            $endDate = $entryDate->copy()->addMinutes($durationMinutes);

            // Use cross-DB transaction
            DB::connection('mysql')->beginTransaction();
            DB::connection('mart')->beginTransaction();

            try {
                // Create Entry in main DB
                $entry = Entry::create([
                    'case_id' => $case->id,
                    'begin' => $entryDate->format('Y-m-d H:i:s'),
                    'end' => $endDate->format('Y-m-d H:i:s'),
                    'inputs' => json_encode($inputs),
                    'media_id' => null, // MART projects don't use media
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate,
                ]);

                // Create MartEntry in MART DB
                $martEntry = MartEntry::create([
                    'main_entry_id' => $entry->id,
                    'schedule_id' => $schedule->id,
                    'questionnaire_id' => $schedule->questionnaire_id,
                    'participant_id' => $case->name,
                    'user_id' => 'seeded@example.com',
                    'started_at' => $entryDate,
                    'completed_at' => $endDate,
                    'duration_ms' => $durationMinutes * 60 * 1000,
                    'timezone' => 'Europe/Berlin',
                    'timestamp' => $entryDate->timestamp * 1000,
                ]);

                // Create MartAnswer records for each question
                foreach ($questions as $question) {
                    $answerValue = $inputs[$question->text] ?? null;

                    if ($answerValue !== null) {
                        MartAnswer::create([
                            'entry_id' => $martEntry->id,
                            'question_uuid' => $question->uuid,
                            'question_version' => $question->version,
                            'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                        ]);
                    }
                }

                DB::connection('mysql')->commit();
                DB::connection('mart')->commit();
            } catch (\Exception $e) {
                DB::connection('mysql')->rollBack();
                DB::connection('mart')->rollBack();
                throw $e;
            }
        }
    }

    /**
     * Generate realistic inputs based on project type
     */
    private function generateRealisticInputs($type, $entryIndex)
    {
        switch ($type) {
            case 'wellbeing':
                return [
                    'How are you feeling right now?' => $this->randomChoice(['Very bad', 'Bad', 'Neutral', 'Good', 'Very good']),
                    'Rate your current stress level' => rand(0, 10),
                    'What are you doing right now?' => $this->randomMultipleChoice(['Working/studying', 'Socializing', 'Exercising', 'Eating', 'Traveling', 'Relaxing', 'Other']),
                    'Any additional thoughts or feelings?' => $this->randomText([
                        'Feeling productive today',
                        'A bit tired from work',
                        'Enjoying time with family',
                        'Looking forward to the weekend',
                        'Stressed about deadlines',
                        'Feeling grateful',
                        '',
                        'Had a good lunch break',
                    ]),
                ];

            case 'stress':
                return [
                    'Rate your current stress level' => rand(1, 7),
                    'What is your primary work activity right now?' => $this->randomChoice(['Email/communication', 'Meetings', 'Focused work', 'Planning/organizing', 'Problem-solving', 'Break/pause', 'Other']),
                    'How demanding is your current task?' => rand(1, 5),
                    'Which stressors are affecting you right now?' => $this->randomMultipleChoice(['Time pressure', 'Workload', 'Difficult colleagues', 'Technical issues', 'Unclear instructions', 'Interruptions', 'None']),
                    'Rate your current energy level' => rand(1, 10),
                ];

            case 'social_media':
                return [
                    'How would you describe your current mood?' => $this->randomChoice(['Very negative', 'Negative', 'Neutral', 'Positive', 'Very positive']),
                    'In the past hour, how much time did you spend on social media?' => rand(0, 60),
                    'Which social media platforms did you use?' => $this->randomMultipleChoice(['Instagram', 'Facebook', 'Twitter/X', 'TikTok', 'YouTube', 'LinkedIn', 'Other', 'None']),
                    'How did social media make you feel?' => $this->randomChoice(['Much worse', 'Worse', 'No change', 'Better', 'Much better', 'Did not use social media']),
                    'Rate your current social connectedness' => rand(1, 7),
                ];

            default:
                return [];
        }
    }

    /**
     * Helper methods for generating realistic data
     */
    private function randomChoice($options)
    {
        return $options[array_rand($options)];
    }

    private function randomMultipleChoice($options)
    {
        $selected = [];
        $numToSelect = rand(1, min(3, count($options)));

        for ($i = 0; $i < $numToSelect; $i++) {
            $option = $options[array_rand($options)];
            if (! in_array($option, $selected)) {
                $selected[] = $option;
            }
        }

        return $selected;
    }

    private function randomText($options)
    {
        return $options[array_rand($options)];
    }

    private function getStudyDuration($type)
    {
        // Return study duration in days based on type
        switch ($type) {
            case 'wellbeing':
                return 14; // 14 days
            case 'stress':
                return 21; // 21 days
            case 'social_media':
                return 10; // 10 days
            default:
                return 14; // Default 14 days
        }
    }

    private function calculateDuration($responseCount)
    {
        // Estimate duration based on response count (2-3 minutes per response)
        $minutes = $responseCount * rand(2, 4);

        // Create varied date ranges for different case statuses
        $rand = rand(1, 100);

        if ($rand <= 30) {
            // 30% chance - Completed cases (ended in the past)
            $startDate = Carbon::now()->subDays(rand(15, 30));
            $endDate = $startDate->copy()->addDays(7);
        } elseif ($rand <= 60) {
            // 30% chance - Active cases (ongoing)
            $startDate = Carbon::now()->subDays(rand(3, 8));
            $endDate = $startDate->copy()->addDays(14);
        } elseif ($rand <= 90) {
            // 30% chance - Future/Pending cases (will start in future)
            $startDate = Carbon::now()->addDays(rand(1, 10));
            $endDate = $startDate->copy()->addDays(7);
        } else {
            // 10% chance - Backend cases (value:0)
            $startDate = Carbon::now()->subDays(rand(1, 5));
            $endDate = $startDate->copy()->addDays(7);

            return "duration:0min|firstDay:{$startDate->format('d.m.Y')}|lastDay:{$endDate->format('d.m.Y')}|value:0";
        }

        return "duration:{$minutes}min|firstDay:{$startDate->format('d.m.Y')}|lastDay:{$endDate->format('d.m.Y')}|value:{$minutes}min";
    }

    /**
     * Calculate duration for completed demo cases (for consultable demo project)
     */
    private function calculateCompletedDuration($responseCount)
    {
        // Estimate duration based on response count (2-3 minutes per response)
        $minutes = $responseCount * rand(2, 4);

        // Always create completed cases (ended in the past) for demo purposes
        $startDate = Carbon::now()->subDays(rand(10, 20));
        $endDate = $startDate->copy()->addDays(7);

        return "duration:{$minutes}min|firstDay:{$startDate->format('d.m.Y')}|lastDay:{$endDate->format('d.m.Y')}|value:{$minutes}min";
    }

    /**
     * Extract questions from project inputs and return as array
     */
    private function extractQuestionsFromProject($project)
    {
        $inputs = json_decode($project->inputs, true);
        $questions = [];

        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                // Skip MART configuration object
                if (!isset($input['type']) || $input['type'] !== 'mart') {
                    $questions[] = $input;
                }
            }
        }

        return $questions;
    }
}
