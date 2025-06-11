<?php

use App\User;
use App\Project;
use App\Cases;
use App\Entry;
use App\Media;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RealisticDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating realistic test data...');
        
        // Ask if user wants to create a large-scale test project
        $createLargeProject = $this->command->confirm('Do you want to create a test project with 500+ cases for performance testing?', false);
        
        // Get or create admin user
        $admin = User::find(1) ?? User::factory()->create(['id' => 1]);
        
        // Ensure we have media items
        $this->ensureMediaExists();
        
        // Create realistic projects
        $projects = $this->createProjects($admin);
        
        foreach ($projects as $project) {
            $this->command->info("Creating data for project: {$project->name}");
            
            // Create cases for each project
            $casesCount = rand(2, 5);
            for ($i = 0; $i < $casesCount; $i++) {
                $case = $this->createCase($project, $admin);
                $this->command->info("  Created case: {$case->name}");
                
                // Create realistic entries for each case
                $this->createRealisticEntries($case, $project);
            }
        }
        
        // Create large-scale test project if requested
        if ($createLargeProject) {
            $this->createLargeScaleTestProject($admin);
        }
        
        $this->command->info('Realistic test data created successfully!');
    }
    
    private function ensureMediaExists()
    {
        // Check if media exists, if not create some basic ones
        if (Media::count() < 5) {
            $this->command->info('Creating basic media items...');
            $mediaSeeder = new MediaSeeder();
            $mediaSeeder->setCommand($this->command);
            $mediaSeeder->run();
        }
    }
    
    private function createProjects($admin)
    {
        $projectTemplates = [
            // Legacy project (no entity fields set - traditional media usage)
            [
                'name' => 'Media Usage Study - Youth',
                'description' => 'Research on digital media consumption patterns among teenagers during crisis periods.',
                'entity_name' => null,
                'use_entity' => null,
                'inputs' => [
                    [
                        'name' => 'What are you doing?',
                        'type' => 'multiple choice',
                        'numberofanswer' => 12,
                        'mandatory' => true,
                        'answers' => [
                            'Watching movies/series/entertainment',
                            'Watching news',
                            'Educational TV/online school',
                            'Watching videos (YouTube, TikTok)',
                            'Online sports course',
                            'Listening to radio',
                            'Listening to music',
                            'Podcast/audiobook/audio drama',
                            'Video conference',
                            'Using digital learning platform',
                            'Doing homework',
                            'Reading',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Who are you with?',
                        'type' => 'multiple choice',
                        'numberofanswer' => 8,
                        'mandatory' => true,
                        'answers' => [
                            'alone',
                            'siblings',
                            'mother',
                            'father',
                            'grandparents',
                            'other relatives',
                            'friends',
                            'classmates',
                            ''
                        ]
                    ],
                    [
                        'name' => 'How is your mood?',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ]
                ]
            ],
            // Project with entities enabled but custom entity name
            [
                'name' => 'Workplace Productivity Study',
                'description' => 'Analysis of work patterns and productivity factors in remote work environments.',
                'entity_name' => 'tool',
                'use_entity' => 1,
                'inputs' => [
                    [
                        'name' => 'Current activity',
                        'type' => 'multiple choice',
                        'numberofanswer' => 10,
                        'mandatory' => true,
                        'answers' => [
                            'Deep work/focused tasks',
                            'Email management',
                            'Video meetings',
                            'Planning/organizing',
                            'Research',
                            'Writing/documentation',
                            'Collaboration/teamwork',
                            'Learning/training',
                            'Administrative tasks',
                            'Break/pause',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Work environment',
                        'type' => 'one choice',
                        'numberofanswer' => 6,
                        'mandatory' => true,
                        'answers' => [
                            'Home office',
                            'Company office',
                            'Co-working space',
                            'Coffee shop/cafe',
                            'Library',
                            'Other location',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Focus level',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Energy level',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ]
                ]
            ],
            // Project with entities disabled (use_entity = 0)
            [
                'name' => 'Student Learning Habits',
                'description' => 'Investigation of study patterns and learning effectiveness in digital education.',
                'entity_name' => 'device',
                'use_entity' => 0,
                'inputs' => [
                    [
                        'name' => 'Learning activity',
                        'type' => 'multiple choice',
                        'numberofanswer' => 8,
                        'mandatory' => true,
                        'answers' => [
                            'Attending online lecture',
                            'Reading textbooks/materials',
                            'Taking notes',
                            'Solving exercises/problems',
                            'Group study session',
                            'Preparing for exam',
                            'Research for assignment',
                            'Watching educational videos',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Study location',
                        'type' => 'one choice',
                        'numberofanswer' => 5,
                        'mandatory' => true,
                        'answers' => [
                            'Bedroom',
                            'Living room',
                            'Kitchen table',
                            'Library',
                            'University campus',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Concentration level',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Difficulty level',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Additional notes',
                        'type' => 'text',
                        'numberofanswer' => 0,
                        'mandatory' => false,
                        'answers' => ['']
                    ]
                ]
            ],
            // Project with entities enabled and food entity name
            [
                'name' => 'Nutrition and Mood Study',
                'description' => 'Research on the relationship between food consumption and emotional states.',
                'entity_name' => 'food',
                'use_entity' => 1,
                'inputs' => [
                    [
                        'name' => 'Meal type',
                        'type' => 'one choice',
                        'numberofanswer' => 5,
                        'mandatory' => true,
                        'answers' => [
                            'Breakfast',
                            'Lunch',
                            'Dinner',
                            'Snack',
                            'Beverage only',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Eating context',
                        'type' => 'multiple choice',
                        'numberofanswer' => 6,
                        'mandatory' => true,
                        'answers' => [
                            'Home cooking',
                            'Restaurant/takeout',
                            'Work cafeteria',
                            'Social gathering',
                            'On the go',
                            'Late night eating',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Hunger level before eating',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Satisfaction level after eating',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Mood after eating',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ]
                ]
            ],
            // Another project with entities enabled and device entity name
            [
                'name' => 'Digital Wellness Tracking',
                'description' => 'Monitoring digital device usage and its impact on well-being.',
                'entity_name' => 'device',
                'use_entity' => 1,
                'inputs' => [
                    [
                        'name' => 'Usage purpose',
                        'type' => 'multiple choice',
                        'numberofanswer' => 8,
                        'mandatory' => true,
                        'answers' => [
                            'Work/professional',
                            'Educational/learning',
                            'Entertainment',
                            'Social communication',
                            'News/information',
                            'Shopping',
                            'Health/fitness tracking',
                            'Creative projects',
                            ''
                        ]
                    ],
                    [
                        'name' => 'Usage intensity',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Eye strain level',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ],
                    [
                        'name' => 'Post-usage mood',
                        'type' => 'scale',
                        'numberofanswer' => 0,
                        'mandatory' => true,
                        'answers' => ['']
                    ]
                ]
            ]
        ];
        
        $projects = [];
        foreach ($projectTemplates as $template) {
            $projectData = [
                'name' => $template['name'],
                'description' => $template['description'],
                'inputs' => json_encode($template['inputs']),
                'created_by' => $admin->id,
                'is_locked' => 0
            ];
            
            // Add entity fields if they exist in template
            if (isset($template['entity_name'])) {
                $projectData['entity_name'] = $template['entity_name'];
            }
            if (isset($template['use_entity'])) {
                $projectData['use_entity'] = $template['use_entity'];
            }
            
            $project = Project::create($projectData);
            
            // Associate media with the project based on entity type
            $this->associateAppropriateMedia($project, $template);
            
            $projects[] = $project;
        }
        
        return $projects;
    }
    
    private function associateAppropriateMedia($project, $template)
    {
        // Choose media based on entity type and project context
        $entityName = $template['entity_name'] ?? null;
        
        if ($entityName === 'food') {
            // For food studies, associate food and beverage items
            $media = Media::whereJsonContains('properties->category', 'food')
                ->orWhereJsonContains('properties->category', 'beverage')
                ->get();
        } elseif ($entityName === 'tool') {
            // For work tool studies, associate work and digital items
            $media = Media::whereJsonContains('properties->category', 'work')
                ->orWhereJsonContains('properties->category', 'digital')
                ->get();
        } elseif ($entityName === 'device') {
            // For device studies, associate digital and wearable items
            $media = Media::whereJsonContains('properties->category', 'digital')
                ->orWhereJsonContains('properties->category', 'wearable')
                ->get();
        } else {
            // For traditional media studies or no entity, use digital and traditional media
            $media = Media::whereJsonContains('properties->category', 'digital')
                ->orWhereJsonContains('properties->category', 'traditional')
                ->orWhereJsonContains('properties->category', 'audio')
                ->get();
        }
        
        // If no specific category matches, fall back to random selection
        if ($media->isEmpty()) {
            $media = Media::inRandomOrder()->limit(rand(5, 10))->get();
        } else {
            // Take a reasonable sample
            $media = $media->shuffle()->take(rand(5, min(10, $media->count())));
        }
        
        $project->media()->sync($media->pluck('id'));
    }
    
    private function createCase($project, $admin)
    {
        $caseNames = [
            'Week 1 Data Collection',
            'Initial Phase Study',
            'Follow-up Period',
            'Extended Analysis',
            'Baseline Measurement',
            'Intervention Phase',
            'Control Group Data',
            'Pilot Study Phase'
        ];
        
        // Create realistic durations with proper lastDay format
        $durationOptions = [
            ['hours' => 168, 'days' => 7],   // 1 week
            ['hours' => 336, 'days' => 14],  // 2 weeks
            ['hours' => 504, 'days' => 21],  // 3 weeks
            ['hours' => 720, 'days' => 30],  // 1 month
        ];
        
        $selectedDuration = $durationOptions[array_rand($durationOptions)];
        
        // Calculate lastDay (end date) - some cases in the past, some in the future
        $startDaysAgo = rand(5, 60); // Start 5-60 days ago
        $startDate = Carbon::now()->subDays($startDaysAgo);
        $endDate = $startDate->copy()->addDays($selectedDuration['days']);
        
        $duration = sprintf(
            'value:%d|days:%d|lastDay:%s',
            $selectedDuration['hours'],
            $selectedDuration['days'],
            $endDate->format('d.m.Y')
        );
        
        return Cases::create([
            'name' => $caseNames[array_rand($caseNames)] . ' - ' . $project->name,
            'duration' => $duration,
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'file_token' => \Illuminate\Support\Facades\Crypt::encryptString(\App\Helpers\Helper::random_str(60))
        ]);
    }
    
    private function createRealisticEntries($case, $project, $entriesCount = null)
    {
        $inputs = json_decode($project->inputs, true);
        $entriesCount = $entriesCount ?? rand(15, 50); // Use provided count or default range
        
        // Parse case duration to get the actual time window
        $duration = $case->duration;
        preg_match('/days:(\d+)/', $duration, $daysMatch);
        preg_match('/lastDay:([\d.]+)/', $duration, $lastDayMatch);
        
        $totalDays = $daysMatch[1] ?? 7;
        $lastDayStr = $lastDayMatch[1] ?? null;
        
        // Calculate the case time window
        if ($lastDayStr) {
            $endDate = Carbon::createFromFormat('d.m.Y', $lastDayStr);
            $startDate = $endDate->copy()->subDays($totalDays);
        } else {
            // Fallback if no lastDay
            $startDate = Carbon::now()->subDays(rand(7, 30));
            $endDate = $startDate->copy()->addDays($totalDays);
        }
        
        // Ensure we don't create entries in the future
        if ($endDate->isFuture()) {
            $endDate = Carbon::now();
        }
        
        // Create entries within the case duration
        $currentDate = $startDate->copy();
        
        for ($i = 0; $i < $entriesCount; $i++) {
            // Stop if we've reached the end date
            if ($currentDate->isAfter($endDate)) {
                break;
            }
            
            // Create realistic time gaps between entries (5 minutes to 8 hours)
            $minutesGap = $this->getRealisticTimeGap();
            $currentDate->addMinutes($minutesGap);
            
            // Generate entry duration (5 minutes to 2 hours for typical activities)
            $durationMinutes = rand(5, 120);
            $entryEndTime = $currentDate->copy()->addMinutes($durationMinutes);
            
            // Don't create entries that extend beyond the case end date
            if ($entryEndTime->isAfter($endDate)) {
                $entryEndTime = $endDate->copy();
            }
            
            // Generate realistic input responses based on project schema
            $entryInputs = $this->generateRealisticInputs($inputs);
            
            // Get random media
            $media = $project->media()->inRandomOrder()->first() ?? Media::inRandomOrder()->first();
            
            Entry::create([
                'begin' => $currentDate->format('Y-m-d H:i:s.u'),
                'end' => $entryEndTime->format('Y-m-d H:i:s.u'),
                'inputs' => json_encode($entryInputs),
                'case_id' => $case->id,
                'media_id' => $media->id,
                'created_at' => $currentDate,
                'updated_at' => $currentDate
            ]);
            
            // Update currentDate to end time for next entry
            $currentDate = $entryEndTime->copy();
            
            // Add some random breaks between activities (sometimes longer gaps)
            if (rand(1, 4) == 1) { // 25% chance of longer break
                $breakMinutes = rand(30, 480); // 30 minutes to 8 hours break
                $currentDate->addMinutes($breakMinutes);
            }
            
            // Skip nights (add 6-10 hours gap if it's late evening)
            if ($currentDate->hour >= 22 || $currentDate->hour <= 6) {
                $hoursToMorning = $currentDate->hour >= 22 ? (24 - $currentDate->hour + 7) : (7 - $currentDate->hour);
                $currentDate->addHours($hoursToMorning);
            }
        }
    }
    
    private function getRealisticTimeGap()
    {
        // Weight the time gaps to be more realistic
        $weights = [
            5 => 30,    // 5 minutes - 30% chance
            15 => 25,   // 15 minutes - 25% chance
            30 => 20,   // 30 minutes - 20% chance
            60 => 15,   // 1 hour - 15% chance
            120 => 7,   // 2 hours - 7% chance
            240 => 2,   // 4 hours - 2% chance
            480 => 1    // 8 hours - 1% chance
        ];
        
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $minutes => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $minutes + rand(0, $minutes * 0.5); // Add some randomness
            }
        }
        
        return 15; // fallback
    }
    
    private function generateRealisticInputs($projectInputs)
    {
        $entryInputs = [];
        
        foreach ($projectInputs as $input) {
            switch ($input['type']) {
                case 'multiple choice':
                    // For multiple choice, select 1-3 random answers
                    $availableAnswers = array_filter($input['answers'], function($answer) {
                        return !empty(trim($answer));
                    });
                    
                    if (!empty($availableAnswers)) {
                        $selectedCount = rand(1, min(3, count($availableAnswers)));
                        $selectedAnswers = array_rand(array_flip($availableAnswers), $selectedCount);
                        
                        if ($selectedCount == 1) {
                            $entryInputs[$input['name']] = [$selectedAnswers];
                        } else {
                            $entryInputs[$input['name']] = $selectedAnswers;
                        }
                    }
                    break;
                    
                case 'one choice':
                    // For one choice, select exactly one answer
                    $availableAnswers = array_filter($input['answers'], function($answer) {
                        return !empty(trim($answer));
                    });
                    
                    if (!empty($availableAnswers)) {
                        $selectedAnswer = $availableAnswers[array_rand($availableAnswers)];
                        $entryInputs[$input['name']] = $selectedAnswer;
                    }
                    break;
                    
                case 'scale':
                    // For scale, generate realistic distribution (1-5, with 2-4 being more common)
                    $scaleWeights = [1 => 10, 2 => 25, 3 => 30, 4 => 25, 5 => 10];
                    $totalWeight = array_sum($scaleWeights);
                    $random = rand(1, $totalWeight);
                    
                    $currentWeight = 0;
                    foreach ($scaleWeights as $value => $weight) {
                        $currentWeight += $weight;
                        if ($random <= $currentWeight) {
                            $entryInputs[$input['name']] = $value;
                            break;
                        }
                    }
                    break;
                    
                case 'text':
                    // Generate realistic text responses
                    $textResponses = [
                        'Working on important project deadline',
                        'Feeling productive today',
                        'Having trouble concentrating',
                        'Great collaboration with team',
                        'Need more coffee',
                        'Excellent learning session',
                        'Challenging but rewarding task',
                        'Taking a well-deserved break',
                        'Preparing for upcoming presentation',
                        'Research going well',
                        'Good progress on assignments',
                        'Enjoying the learning process'
                    ];
                    
                    // Only include text response 60% of the time (since it's often optional)
                    if (rand(1, 10) <= 6) {
                        $entryInputs[$input['name']] = $textResponses[array_rand($textResponses)];
                    }
                    break;
            }
        }
        
        return $entryInputs;
    }
    
    private function createLargeScaleTestProject($admin)
    {
        $this->command->info('Creating large-scale test project for performance testing...');
        
        // Create a performance test project
        $project = Project::create([
            'name' => 'Performance Test Project - Large Scale',
            'description' => 'A test project with 500+ cases to evaluate system performance and optimization.',
            'entity_name' => 'activity',
            'use_entity' => 1,
            'inputs' => json_encode([
                [
                    'name' => 'Activity type',
                    'type' => 'one choice',
                    'numberofanswer' => 5,
                    'mandatory' => true,
                    'answers' => ['Work', 'Study', 'Leisure', 'Exercise', 'Social', '']
                ],
                [
                    'name' => 'Duration category',
                    'type' => 'one choice',
                    'numberofanswer' => 4,
                    'mandatory' => true,
                    'answers' => ['Short (< 30min)', 'Medium (30-60min)', 'Long (1-2h)', 'Extended (2h+)', '']
                ],
                [
                    'name' => 'Engagement level',
                    'type' => 'scale',
                    'numberofanswer' => 0,
                    'mandatory' => true,
                    'answers' => ['']
                ],
                [
                    'name' => 'Satisfaction',
                    'type' => 'scale',
                    'numberofanswer' => 0,
                    'mandatory' => true,
                    'answers' => ['']
                ]
            ]),
            'created_by' => $admin->id,
            'is_locked' => 0
        ]);
        
        // Associate some media with the project
        $media = Media::inRandomOrder()->limit(10)->get();
        $project->media()->sync($media->pluck('id'));
        
        $this->command->info("Created performance test project: {$project->name}");
        
        // Create 500+ cases with realistic distribution
        $totalCases = 520; // A bit over 500 for testing
        $this->command->info("Creating {$totalCases} cases...");
        
        $progressBar = $this->command->getOutput()->createProgressBar($totalCases);
        $progressBar->start();
        
        for ($i = 1; $i <= $totalCases; $i++) {
            // Create case with batch processing every 50 cases for memory efficiency
            $case = $this->createPerformanceTestCase($project, $admin, $i);
            
            // Create fewer entries per case for this performance test (5-15 instead of 15-50)
            $entriesCount = rand(5, 15);
            $this->createRealisticEntries($case, $project, $entriesCount);
            
            $progressBar->advance();
            
            // Memory cleanup every 50 cases
            if ($i % 50 == 0) {
                gc_collect_cycles();
            }
        }
        
        $progressBar->finish();
        $this->command->line('');
        $this->command->info("Successfully created {$totalCases} cases for performance testing!");
        $this->command->info("Project ID: {$project->id}");
        $this->command->info("You can now test the performance improvements with this large dataset.");
    }
    
    private function createPerformanceTestCase($project, $admin, $caseNumber)
    {
        // Create varied case durations
        $durationOptions = [
            ['hours' => 168, 'days' => 7],   // 1 week - 40%
            ['hours' => 336, 'days' => 14],  // 2 weeks - 30%
            ['hours' => 504, 'days' => 21],  // 3 weeks - 20%
            ['hours' => 720, 'days' => 30],  // 1 month - 10%
        ];
        
        $weights = [40, 30, 20, 10];
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $selectedIndex = 0;
        $currentWeight = 0;
        foreach ($weights as $index => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                $selectedIndex = $index;
                break;
            }
        }
        
        $selectedDuration = $durationOptions[$selectedIndex];
        
        // Distribute case start dates over the last 6 months for variety
        $startDaysAgo = rand(5, 180);
        $startDate = Carbon::now()->subDays($startDaysAgo);
        $endDate = $startDate->copy()->addDays($selectedDuration['days']);
        
        $duration = sprintf(
            'value:%d|days:%d|lastDay:%s',
            $selectedDuration['hours'],
            $selectedDuration['days'],
            $endDate->format('d.m.Y')
        );
        
        return Cases::create([
            'name' => "Performance Test Case #{$caseNumber}",
            'duration' => $duration,
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'file_token' => \Illuminate\Support\Facades\Crypt::encryptString(\App\Helpers\Helper::random_str(60))
        ]);
    }
}