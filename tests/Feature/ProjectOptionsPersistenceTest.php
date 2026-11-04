<?php

namespace Tests\Feature;

use App\Http\Controllers\MartApiController;
use App\Mart\MartProject;
use App\Project;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Pins MART project options, both ends: the controller must persist what the
 * form submits, an edit that omits the settings must not blank them, and the
 * mobile API must serve what is stored.
 *
 * Adapted from Helge's mart-web tests. There the options are project columns;
 * here they live in the 'mart' entry of projects.inputs.
 */
class ProjectOptionsPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);
    }

    private function martConfig(Project $project): array
    {
        return collect(json_decode($project->fresh()->inputs, true))
            ->firstWhere('type', 'mart');
    }

    private function makeMartProject(array $projectOptions): void
    {
        $this->project->update(['inputs' => json_encode([[
            'type' => 'mart',
            'name' => 'MART Configuration',
            'answers' => [],
            'projectOptions' => $projectOptions,
        ]])]);
        MartProject::firstOrCreate(['main_project_id' => $this->project->id]);
    }

    /**
     * Build the mobile-facing structure payload for the project.
     */
    private function structureOptions(Project $project): array
    {
        $resource = (new MartApiController)->getProjectStructure(Request::create('/test', 'GET'), $project->fresh());

        return json_decode(json_encode($resource->toArray(null)), true)['projectOptions']['options'];
    }

    /** @test */
    public function creating_a_mart_project_persists_its_availability_window()
    {
        $this->postJson('/projects', [
            'name' => 'New MART Study',
            'description' => 'created through the UI',
            'isMart' => true,
            'startDate' => '2026-03-01',
            'startTime' => '08:30',
            'endDate' => '2026-09-30',
            'endTime' => '20:15',
        ])->assertCreated();

        $project = Project::where('name', 'New MART Study')->firstOrFail();

        $this->assertSame([
            'startDateAndTime' => ['date' => '2026-03-01', 'time' => '08:30'],
            'endDateAndTime' => ['date' => '2026-09-30', 'time' => '20:15'],
        ], $this->martConfig($project)['projectOptions']);
    }

    /** @test */
    public function a_mart_project_created_without_options_serves_the_mart_defaults()
    {
        $this->postJson('/projects', [
            'name' => 'Bare Study',
            'description' => 'no options submitted',
            'isMart' => true,
        ])->assertCreated();

        $options = $this->structureOptions(Project::where('name', 'Bare Study')->firstOrFail());

        // The MART shape with its documented defaults, not the MeTag one.
        $this->assertArrayHasKey('singleQuestionnaires', $options);
        $this->assertTrue($options['collectDeviceInfos']);
        $this->assertFalse($options['collectAndroidStats']);
        $this->assertSame(24, $options['initialHoursOfAndroidStats']);
        $this->assertSame(2, $options['overlapAndroidStatsHours']);
    }

    /** @test */
    public function editing_the_options_reaches_the_project()
    {
        $this->makeMartProject([
            'startDateAndTime' => ['date' => '2020-01-01', 'time' => '01:00'],
        ]);

        $this->patchJson('/projects/' . $this->project->id, [
            'name' => $this->project->name,
            'description' => $this->project->description,
            'inputs' => json_encode([[
                'type' => 'mart',
                'name' => 'MART Configuration',
                'projectOptions' => [
                    'startDateAndTime' => ['date' => '2027-05-05', 'time' => '07:45'],
                    'endDateAndTime' => ['date' => '2027-06-06', 'time' => '19:30'],
                    'collectAndroidStats' => true,
                    'initialHoursOfAndroidStats' => 480,
                ],
            ]]),
        ])->assertSuccessful();

        $options = $this->martConfig($this->project)['projectOptions'];

        $this->assertSame(['date' => '2027-05-05', 'time' => '07:45'], $options['startDateAndTime']);
        $this->assertSame(['date' => '2027-06-06', 'time' => '19:30'], $options['endDateAndTime']);
        $this->assertTrue($options['collectAndroidStats']);
        $this->assertSame(480, $options['initialHoursOfAndroidStats']);
    }

    /** @test */
    public function an_edit_without_inputs_leaves_the_mart_config_intact()
    {
        $this->makeMartProject([
            'startDateAndTime' => ['date' => '2026-03-01', 'time' => '08:30'],
            'initialHoursOfAndroidStats' => 72,
        ]);

        $this->patchJson('/projects/' . $this->project->id, [
            'name' => 'Renamed',
            'description' => $this->project->description,
        ])->assertSuccessful();

        $this->assertSame('Renamed', $this->project->fresh()->name);
        $this->assertTrue($this->project->fresh()->isMartProject());
        $this->assertSame(72, $this->martConfig($this->project)['projectOptions']['initialHoursOfAndroidStats']);
    }

    /** @test */
    public function the_mobile_api_serves_the_stored_options()
    {
        $this->makeMartProject([
            'startDateAndTime' => ['date' => '2025-03-20', 'time' => '06:00'],
            'endDateAndTime' => ['date' => '2026-12-21', 'time' => '22:00'],
            'collectDeviceInfos' => true,
            'collectAndroidStats' => true,
            'initialHoursOfAndroidStats' => 720,
            'overlapAndroidStatsHours' => 3,
        ]);

        $options = $this->structureOptions($this->project);

        $this->assertSame('20.03.2025', $options['startDateAndTime']['date']);
        $this->assertSame('06:00', $options['startDateAndTime']['time']);
        $this->assertSame('21.12.2026', $options['endDateAndTime']['date']);
        $this->assertSame('22:00', $options['endDateAndTime']['time']);
        $this->assertTrue($options['collectDeviceInfos']);
        $this->assertTrue($options['collectAndroidStats']);
        $this->assertSame(720, $options['initialHoursOfAndroidStats']);
        $this->assertSame(3, $options['overlapAndroidStatsHours']);
    }
}
