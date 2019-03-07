<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;

class ProjectCaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_project_can_have_cases()
    {
        $this->withoutExceptionHandling();

        $this->signIn();

        $project = factory(Project::class)->create(['created_by' => auth()->id()]);

        $this->post($project->path().'/cases',['name' => 'Test case']);

        $this->get($project->path())->assertSee('Test case');

    }

    /** @test */

    public function only_the_owner_of_a_project_can_add_cases()
    {

        $this->signIn();

        $project = factory(Project::class)->create();
        $this->post($project->path().'/cases',['name' => 'Test case'])->assertStatus(403);

        $this->assertDatabaseMissing('cases',['name' => 'Test case']);
    }

    /** @test */
    public function a_case_require_a_name()
    {
        $this->signIn();


        $project = auth()->user()->projects()->create(factory(Project::class)->raw());

        $attributes = factory('App\Cases')->raw(['name' => '']);

        $this->post($project->path().'/cases',$attributes)->assertSessionHasErrors('name');

    }

}
