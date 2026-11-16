<?php

use App\Cases;
use App\Http\Controllers\MartApiController;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Four demo MART projects, optionally with made-up experience sampling data.
 *
 * Questions carry the config the editor saves, and live only in the MART
 * database. Answers go through the same code as the app's submits: option
 * values from 1, an array for every item, and never 0 on a range, which the
 * app would drop. Random numbers come from a fixed seed, so runs repeat.
 *
 * The demo cases all belong to the project owner, so they're for the
 * researcher's screens and exports. Only one of them could sign in to the app.
 */
class MartProjectSeeder extends Seeder
{
    /** Answers per case. Null picks a realistic number; tests set a small one. */
    public ?int $answersPerCase = null;

    private const RANDOM_SEED = 20261108;

    public function run()
    {
        $this->command->info('Creating MART projects...');

        // Ask if user wants ESM data
        $includeEsmData = $this->command->confirm(
            'Do you want to include ESM (Experience Sampling Method) data in MART projects?',
            true // Default to yes
        );

        mt_srand(self::RANDOM_SEED);

        try {
            $user = $this->owner();
            $this->command->info("Assigning MART projects to user: {$user->email} (ID: {$user->id})");

            $projects = [
                $this->createWellbeingProject($user, $includeEsmData),
                $this->createStressStudyProject($user, $includeEsmData),
                $this->createSocialMediaProject($user, $includeEsmData),
                $this->createConsultableDemoProject($user, $includeEsmData),
            ];
        } finally {
            mt_srand();
        }

        $this->command->info('Created ' . count($projects) . ' MART projects.');

        if ($includeEsmData) {
            $this->command->info('ESM data included: Cases with realistic mobile entries.');
        } else {
            $this->command->info('ESM data skipped: Projects created without cases/entries.');
        }

        $this->command->info('MART projects seeded successfully!');
    }

    /**
     * The first user, as for RealisticDataSeeder, or a new admin with a random
     * password shown once.
     */
    private function owner(): User
    {
        $user = User::find(1);
        if ($user) {
            return $user;
        }

        $password = Str::password(20, symbols: false);
        $user = User::create([
            'email' => 'admin@metag-analyze.test',
            'password' => bcrypt($password),
            'deviceID' => 'ADMIN_' . Str::random(10),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->command->info("No user 1, so created {$user->email}  password: {$password}");

        return $user;
    }

    /**
     * Create a wellbeing tracking MART project
     */
    private function createWellbeingProject($user, $includeEsmData)
    {
        $project = $this->createProject($user, 'Daily Wellbeing Tracker',
            'A mobile experience sampling study tracking daily mood, stress levels, and activities to understand patterns in psychological wellbeing.',
            'Daily Wellbeing Check-in',
            [
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
        );

        $this->createQuestionnaireSchedules($project, 'wellbeing', [
            $this->choice('How are you feeling right now?', ['Very bad', 'Bad', 'Neutral', 'Good', 'Very good'], true),
            $this->range('Rate your current stress level', 0, 10, 1, true),
            $this->choice('What are you doing right now?', ['Working/studying', 'Socializing', 'Exercising', 'Eating', 'Traveling', 'Relaxing', 'Other'], false, multiple: true),
            $this->textarea('Any additional thoughts or feelings?', false),
        ]);

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
        $project = $this->createProject($user, 'Workplace Stress Research',
            'An ESM study examining workplace stress patterns, coping mechanisms, and their impact on performance and wellbeing among office workers.',
            'Workplace Stress Monitor',
            [
                [
                    'name' => 'Study Information',
                    'content' => '<h1>Workplace Stress Research</h1><p>This study investigates how workplace stress affects daily performance and wellbeing.</p><p><strong>Duration:</strong> 21 days<br><strong>Frequency:</strong> 4 times per day during work hours</p><p>Your participation helps us understand stress patterns in modern workplaces.</p>',
                    'buttonText' => 'Continue',
                    'showOnFirstAppStart' => true,
                    'sortOrder' => 0,
                ],
            ],
        );

        $this->createQuestionnaireSchedules($project, 'workplace', [
            $this->range('Rate your current stress level', 1, 7, 1, true),
            $this->choice('What is your primary work activity right now?', ['Email/communication', 'Meetings', 'Focused work', 'Planning/organizing', 'Problem-solving', 'Break/pause', 'Other'], true),
            $this->range('How demanding is your current task?', 1, 5, 1, true),
            $this->choice('Which stressors are affecting you right now?', ['Time pressure', 'Workload', 'Difficult colleagues', 'Technical issues', 'Unclear instructions', 'Interruptions', 'None'], false, multiple: true),
            $this->range('Rate your current energy level', 1, 10, 1, true),
        ]);

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
        $project = $this->createProject($user, 'Social Media & Mood Study',
            'An experience sampling study exploring the relationship between social media usage patterns and emotional wellbeing in young adults.',
            'Social Media & Mood Check',
            [
                [
                    'name' => 'Study Overview',
                    'content' => '<h1>Social Media & Mood Study</h1><p>Help us understand how social media affects daily mood and wellbeing.</p><p><strong>What we\'ll ask:</strong></p><ul><li>Your current mood</li><li>Recent social media activity</li><li>Social interactions</li></ul><p>All responses are anonymous and confidential.</p>',
                    'buttonText' => 'Begin',
                    'showOnFirstAppStart' => true,
                    'sortOrder' => 0,
                ],
            ],
        );

        $this->createQuestionnaireSchedules($project, 'social', [
            $this->choice('How would you describe your current mood?', ['Very negative', 'Negative', 'Neutral', 'Positive', 'Very positive'], true),
            $this->range('In the past hour, how much time did you spend on social media?', 0, 60, 5, true),
            $this->choice('Which social media platforms did you use?', ['Instagram', 'Facebook', 'Twitter/X', 'TikTok', 'YouTube', 'LinkedIn', 'Other', 'None'], false, multiple: true),
            $this->choice('How did social media make you feel?', ['Much worse', 'Worse', 'No change', 'Better', 'Much better', 'Did not use social media'], false),
            $this->range('Rate your current social connectedness', 1, 7, 1, true),
        ]);

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
        $project = $this->createProject($user, 'ESM Demo - Consultable Entries',
            'A demonstration ESM project with clear, readable entries for testing and consultation. Contains structured data that is easy to analyze and understand.',
            'Daily Experience Survey',
            [
                [
                    'name' => 'Demo Instructions',
                    'content' => '<h1>ESM Demo Project</h1><p>This is a demonstration project showing how ESM data collection works.</p><p><strong>Purpose:</strong> To provide clear, consultable entries that researchers can easily analyze.</p><ul><li>Questions are simple and clear</li><li>Responses are realistic and varied</li><li>Data structure is optimized for analysis</li></ul>',
                    'buttonText' => 'Start Demo',
                    'showOnFirstAppStart' => true,
                    'sortOrder' => 0,
                ],
            ],
        );

        $this->createQuestionnaireSchedules($project, 'demo', [
            $this->range('Current mood (1-10 scale)', 1, 10, 1, true),
            $this->choice('Primary activity right now', ['Working', 'Studying', 'Socializing', 'Exercising', 'Eating', 'Commuting', 'Relaxing', 'Sleeping'], true),
            $this->range('Energy level (1-5 scale)', 1, 5, 1, true),
            $this->choice('Location', ['Home', 'Office', 'School', 'Transportation', 'Restaurant/Cafe', 'Gym', 'Outdoors', 'Other'], false),
            $this->textarea('Notable thoughts or events', false),
        ]);

        if ($includeEsmData) {
            $this->createConsultableDemoData($project);
        }

        return $project;
    }

    /**
     * The project row with only the MART marker in inputs, as
     * ProjectController and editproject.vue save it, plus its MART rows.
     */
    protected function createProject(User $user, string $name, string $description, string $questionnaireName, array $pages): Project
    {
        $project = Project::create([
            'name' => $name,
            'description' => $description,
            'created_by' => $user->id,
            'is_locked' => false,
            'use_entity' => false, // MART projects don't use entities
            'entity_name' => null,
            'inputs' => json_encode([[
                'type' => 'mart',
                'name' => 'MART Configuration',
                'answers' => [],
                'questionnaireName' => $questionnaireName,
                'projectOptions' => ['pages' => $pages],
            ]]),
        ]);

        $martProject = MartProject::create(['main_project_id' => $project->id]);

        foreach ($pages as $page) {
            MartPage::create([
                'mart_project_id' => $martProject->id,
                'name' => $page['name'],
                'content' => $page['content'],
                'button_text' => $page['buttonText'],
                'show_on_first_app_start' => $page['showOnFirstAppStart'],
                'sort_order' => $page['sortOrder'],
            ]);
        }

        return $project;
    }

    /**
     * Questions as the editor saves them.
     */
    private function choice(string $text, array $options, bool $mandatory, bool $multiple = false): array
    {
        return [
            'text' => $text,
            'type' => $multiple ? 'multiple choice' : 'one choice',
            'mandatory' => $mandatory,
            'config' => ['options' => $options],
        ];
    }

    private function range(string $text, int $min, int $max, int $step, bool $mandatory): array
    {
        return [
            'text' => $text,
            'type' => 'range',
            'mandatory' => $mandatory,
            'config' => ['min' => $min, 'max' => $max, 'step' => $step],
        ];
    }

    private function textarea(string $text, bool $mandatory): array
    {
        return ['text' => $text, 'type' => 'textarea', 'mandatory' => $mandatory, 'config' => []];
    }

    /**
     * Create highly structured, consultable demo data
     */
    private function createConsultableDemoData($project)
    {
        // Create 3 demo participants with different patterns
        $participants = [
            ['name' => 'Demo_Participant_A_HighMood', 'pattern' => 'high_mood', 'entries' => 25],
            ['name' => 'Demo_Participant_B_WorkStress', 'pattern' => 'work_stress', 'entries' => 30],
            ['name' => 'Demo_Participant_C_Balanced', 'pattern' => 'balanced', 'entries' => 28],
        ];

        foreach ($participants as $participantData) {
            // A finished 7-day study, so its cases are completed.
            $firstDay = Carbon::now()->subDays(rand(10, 20))->startOfDay();
            $lastDay = $firstDay->copy()->addDays(7);

            $this->createCaseWithAnswers(
                $project,
                $participantData['name'],
                $firstDay,
                $lastDay,
                $this->answersPerCase ?? $participantData['entries'],
                fn ($index, $entryDate) => $this->generatePatternedInputs($participantData['pattern'], $index, $entryDate),
            );
        }
    }

    /**
     * Create ESM cases and entries for a project
     */
    private function createEsmCases($project, $type)
    {
        $participants = [
            'Participant_001' => [20, 40],
            'Participant_002' => [15, 35],
            'Participant_003' => [25, 45],
            'Participant_004' => [18, 38],
            'Participant_005' => [22, 42],
        ];

        foreach ($participants as $name => [$least, $most]) {
            [$firstDay, $lastDay] = $this->caseWindow();

            $this->createCaseWithAnswers(
                $project,
                $name,
                $firstDay,
                $lastDay,
                $this->answersPerCase ?? rand($least, $most),
                fn ($index) => $this->generateRealisticInputs($type, $index),
            );
        }
    }

    /**
     * A case window: a third finished, a third running, a third not started.
     */
    private function caseWindow(): array
    {
        $chance = rand(1, 3);

        if ($chance === 1) {
            $firstDay = Carbon::now()->subDays(rand(15, 30))->startOfDay();

            return [$firstDay, $firstDay->copy()->addDays(7)];
        }
        if ($chance === 2) {
            $firstDay = Carbon::now()->subDays(rand(3, 8))->startOfDay();

            return [$firstDay, $firstDay->copy()->addDays(14)];
        }

        $firstDay = Carbon::now()->addDays(rand(1, 10))->startOfDay();

        return [$firstDay, $firstDay->copy()->addDays(7)];
    }

    /**
     * A case that answers questionnaire 1 while it's open, like a participant
     * during the study, and then gets its final dates. A case that hasn't
     * started yet has no answers.
     */
    private function createCaseWithAnswers(Project $project, string $name, Carbon $firstDay, Carbon $lastDay, int $answerCount, callable $inputs): void
    {
        $case = Cases::create([
            'project_id' => $project->id,
            'user_id' => $project->created_by,
            'name' => $name,
            'duration' => "firstDay:{$firstDay->format('d.m.Y')}|",
            'created_at' => $firstDay->copy()->min(now()),
        ]);

        $schedule = MartSchedule::where('mart_project_id', $project->martProject()->id)
            ->where('questionnaire_id', 1)
            ->firstOrFail();
        $questions = $schedule->questions()->get();

        // Answers end 10 minutes before now at the latest.
        $answerWindow = (int) $firstDay->diffInMinutes($lastDay->copy()->min(now()->subMinutes(10)), false);
        if ($answerWindow > 0) {
            for ($i = 0; $i < $answerCount; $i++) {
                $startedAt = $firstDay->copy()->addMinutes(rand(0, $answerWindow));
                $this->submit($case, $schedule, $questions, $inputs($i, $startedAt), $startedAt, rand(2, 5) * 60);
            }
        }

        $minutes = $answerCount * rand(2, 4);
        $case->update([
            'duration' => "duration:{$minutes}min|firstDay:{$firstDay->format('d.m.Y')}|lastDay:{$lastDay->format('d.m.Y')}|value:{$minutes}min",
        ]);
    }

    /**
     * One submit through the same code as the app's.
     */
    protected function submit(Cases $case, MartSchedule $schedule, Collection $questions, array $inputs, Carbon $startedAt, int $durationSeconds): void
    {
        $answers = [];
        foreach ($questions->values() as $index => $question) {
            // A text-only item has no itemId, so the app sends nothing for it;
            // later items keep their place-based itemIds.
            if ($question->type === 'display') {
                continue;
            }
            $answers[(string) ($index + 1)] = $this->appAnswer($question, $inputs[$question->text] ?? null);
        }

        $request = new Request([
            'projectId' => $case->project_id,
            'questionnaireId' => $schedule->questionnaire_id,
            'userId' => $case->user->email,
            'participantId' => $case->name,
            'questionnaireStarted' => $startedAt->getTimestampMs(),
            'questionnaireDuration' => $durationSeconds * 1000,
            'answers' => $answers,
            'timestamp' => $startedAt->copy()->addSeconds($durationSeconds)->getTimestampMs(),
            'timezone' => 'Europe/Berlin',
        ]);

        $result = (new MartApiController)->submitEntry($request, $case)->getData(true);
        if (! ($result['success'] ?? false)) {
            throw new RuntimeException('A seeded submit was rejected: ' . ($result['message'] ?? 'no message'));
        }
    }

    /**
     * The answer as the app sends it: always an array, empty when unanswered.
     * Choices and sliders with labels are option values from 1, given here as
     * their label. The app drops a range or number of 0.
     */
    private function appAnswer(MartQuestion $question, $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        return match ($question->type) {
            'one choice' => $this->choiceValues($question, [$value]),
            'multiple choice' => $this->choiceValues($question, $value),
            'range values' => [$this->optionValue($question, $value)],
            'range', 'number' => $value === 0 ? [] : [$value],
            default => [$value],
        };
    }

    /**
     * Option values from 1. An ['other' => typed words] choice becomes "text"
     * followed by the typed words, as the app sends the "other" option.
     */
    private function choiceValues(MartQuestion $question, array $choices): array
    {
        $values = [];
        foreach ($choices as $choice) {
            if (is_array($choice) && isset($choice['other'])) {
                $values[] = 'text';
                $values[] = $choice['other'];

                continue;
            }
            $values[] = $this->optionValue($question, $choice);
        }

        return $values;
    }

    private function optionValue(MartQuestion $question, string $text): int
    {
        $key = array_search($text, $question->config['options'], true);
        if ($key === false) {
            throw new RuntimeException("\"{$text}\" is not an option of \"{$question->text}\".");
        }

        return $key + 1;
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
     * Create questionnaire schedules for a MART project in MART database
     */
    private function createQuestionnaireSchedules($project, $type, array $questions)
    {
        $martProject = $project->martProject();

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addMonths(3)->endOfDay();

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
     * Create individual MartQuestion records for a schedule, positions from 1
     * as the editor saves them.
     */
    protected function createQuestionsForSchedule($schedule, array $questions)
    {
        foreach ($questions as $index => $question) {
            MartQuestion::create([
                'schedule_id' => $schedule->id,
                'position' => $index + 1,
                'text' => $question['text'],
                'type' => $question['type'],
                'config' => $question['config'],
                'is_mandatory' => $question['mandatory'],
                'version' => 1,
            ]);
        }
    }

    /**
     * Generate realistic inputs based on project type. Ranges never answer 0,
     * which the app would drop.
     */
    private function generateRealisticInputs($type, $entryIndex)
    {
        switch ($type) {
            case 'wellbeing':
                return [
                    'How are you feeling right now?' => $this->randomChoice(['Very bad', 'Bad', 'Neutral', 'Good', 'Very good']),
                    'Rate your current stress level' => rand(1, 10),
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
                    'In the past hour, how much time did you spend on social media?' => rand(1, 12) * 5,
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
}
