<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\MartPage;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MartProjectSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that MartProjectSeeder creates projects without ESM data
     *
     * @return void
     */
    public function test_mart_seeder_creates_projects_without_esm_data()
    {
        // Run seeder without ESM data
        $this->artisan('db:seed', ['--class' => 'MartProjectSeeder'])
            ->expectsQuestion('Do you want to include ESM (Experience Sampling Method) data in MART projects?', 'no');

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'email' => 'mart@example.com',
        ]);

        // Assert 3 MART projects were created
        $this->assertEquals(3, Project::count());

        // Check first project structure
        $wellbeingProject = Project::where('name', 'Daily Wellbeing Tracker')->first();
        $this->assertNotNull($wellbeingProject);
        $this->assertEquals('mart@example.com', User::find($wellbeingProject->created_by)->email);
        $this->assertFalse($wellbeingProject->use_entity);
        $this->assertNull($wellbeingProject->entity_name);

        // Check MART configuration
        $inputs = json_decode($wellbeingProject->inputs, true);
        $this->assertIsArray($inputs);
        $this->assertGreaterThan(0, count($inputs));
        $this->assertEquals('mart', $inputs[0]['type']);
        $this->assertEquals('Daily Wellbeing Check-in', $inputs[0]['questionnaireName']);

        // Check pages were created
        $pages = MartPage::where('project_id', $wellbeingProject->id)->get();
        $this->assertCount(2, $pages);

        // Check no cases/entries were created (ESM data = false)
        $this->assertEquals(0, Cases::count());
        $this->assertEquals(0, Entry::count());
    }

    /**
     * Test that MartProjectSeeder creates projects with ESM data
     *
     * @return void
     */
    public function test_mart_seeder_creates_projects_with_esm_data()
    {
        // Run seeder with ESM data
        $this->artisan('db:seed', ['--class' => 'MartProjectSeeder'])
            ->expectsQuestion('Do you want to include ESM (Experience Sampling Method) data in MART projects?', 'yes');

        // Assert 3 projects created
        $this->assertEquals(3, Project::count());

        // Assert cases were created (5 per project)
        $this->assertEquals(15, Cases::count());

        // Assert entries were created
        $this->assertGreaterThan(0, Entry::count());

        // Check that entries have proper structure
        $entry = Entry::first();
        $this->assertNotNull($entry);
        $inputs = json_decode($entry->inputs, true);
        $this->assertIsArray($inputs);

        // For wellbeing project, check entry structure
        $wellbeingProject = Project::where('name', 'Daily Wellbeing Tracker')->first();
        $wellbeingCase = Cases::where('project_id', $wellbeingProject->id)->first();
        $wellbeingEntry = Entry::where('case_id', $wellbeingCase->id)->first();

        $entryInputs = json_decode($wellbeingEntry->inputs, true);
        $this->assertArrayHasKey('How are you feeling right now?', $entryInputs);
        $this->assertArrayHasKey('Rate your current stress level', $entryInputs);
        $this->assertArrayHasKey('What are you doing right now?', $entryInputs);
    }

    /**
     * Test MART project structure is correct
     *
     * @return void
     */
    public function test_mart_project_structure_is_correct()
    {
        // Run seeder without ESM data
        $this->artisan('db:seed', ['--class' => 'MartProjectSeeder'])
            ->expectsQuestion('Do you want to include ESM (Experience Sampling Method) data in MART projects?', 'no');

        // Check Wellbeing project
        $wellbeing = Project::where('name', 'Daily Wellbeing Tracker')->first();
        $inputs = json_decode($wellbeing->inputs, true);

        // Should have MART config + 4 questions
        $this->assertCount(5, $inputs);

        // Check MART config
        $martConfig = $inputs[0];
        $this->assertEquals('mart', $martConfig['type']);
        $this->assertArrayHasKey('projectOptions', $martConfig);
        $this->assertArrayHasKey('pages', $martConfig['projectOptions']);

        // Check questions
        $questions = array_slice($inputs, 1);
        $this->assertEquals('How are you feeling right now?', $questions[0]['name']);
        $this->assertEquals('one choice', $questions[0]['type']);
        $this->assertCount(5, $questions[0]['answers']);

        // Check stress level question (scale type)
        $this->assertEquals('Rate your current stress level', $questions[1]['name']);
        $this->assertEquals('scale', $questions[1]['type']);
        $this->assertEquals('range', $questions[1]['martMetadata']['originalType']);
        $this->assertEquals(0, $questions[1]['martMetadata']['minValue']);
        $this->assertEquals(10, $questions[1]['martMetadata']['maxValue']);

        // Check pages
        $pages = MartPage::where('project_id', $wellbeing->id)->orderBy('sort_order')->get();
        $this->assertCount(2, $pages);
        $this->assertEquals('Welcome', $pages[0]->name);
        $this->assertEquals('Instructions', $pages[1]->name);
        $this->assertTrue($pages[0]->show_on_first_app_start);
    }

    /**
     * Test all three MART projects are created correctly
     *
     * @return void
     */
    public function test_all_mart_projects_created()
    {
        // Run seeder
        $this->artisan('db:seed', ['--class' => 'MartProjectSeeder'])
            ->expectsQuestion('Do you want to include ESM (Experience Sampling Method) data in MART projects?', 'no');

        // Check all projects exist
        $this->assertDatabaseHas('projects', ['name' => 'Daily Wellbeing Tracker']);
        $this->assertDatabaseHas('projects', ['name' => 'Workplace Stress Research']);
        $this->assertDatabaseHas('projects', ['name' => 'Social Media & Mood Study']);

        // Check all are MART projects
        $projects = Project::all();
        foreach ($projects as $project) {
            $this->assertTrue($project->isMartProject());
            $this->assertFalse($project->use_entity);

            // Check has pages
            $this->assertGreaterThan(0, MartPage::where('project_id', $project->id)->count());
        }
    }

    /**
     * Test ESM data generation creates realistic entries
     *
     * @return void
     */
    public function test_esm_data_is_realistic()
    {
        // Run seeder with ESM data
        $this->artisan('db:seed', ['--class' => 'MartProjectSeeder'])
            ->expectsQuestion('Do you want to include ESM (Experience Sampling Method) data in MART projects?', 'yes');

        // Check stress study entries
        $stressProject = Project::where('name', 'Workplace Stress Research')->first();
        $stressCase = Cases::where('project_id', $stressProject->id)->first();
        $stressEntries = Entry::where('case_id', $stressCase->id)->get();

        $this->assertGreaterThan(0, $stressEntries->count());

        foreach ($stressEntries as $entry) {
            $inputs = json_decode($entry->inputs, true);

            // Check stress level is in range
            $this->assertGreaterThanOrEqual(1, $inputs['Rate your current stress level']);
            $this->assertLessThanOrEqual(7, $inputs['Rate your current stress level']);

            // Check work activity is valid
            $validActivities = ['Email/communication', 'Meetings', 'Focused work', 'Planning/organizing', 'Problem-solving', 'Break/pause', 'Other'];
            $this->assertContains($inputs['What is your primary work activity right now?'], $validActivities);

            // Check stressors are valid (if present)
            if (isset($inputs['Which stressors are affecting you right now?'])) {
                $validStressors = ['Time pressure', 'Workload', 'Difficult colleagues', 'Technical issues', 'Unclear instructions', 'Interruptions', 'None'];
                foreach ($inputs['Which stressors are affecting you right now?'] as $stressor) {
                    $this->assertContains($stressor, $validStressors);
                }
            }
        }
    }
}
