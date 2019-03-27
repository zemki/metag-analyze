<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use Facades\Tests\Setup\ProjectFactory;
use App\CaseInput;

class ProjectCaseTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    function a_case_can_be_updated()
    {

        $project = ProjectFactory::createdBy($this->signIn())
        ->withCases(1)
        ->create();

        $this->patch($project->cases[0]->path(),[
            'name' => 'changed'
        ]);

        $this->assertDatabaseHas('cases',[
            'name' => 'changed'
        ]);
    }


    /** @test */
    public function a_project_can_have_cases()
    {

        $project = ProjectFactory::create();

        $this->actingAs($project->created_by())->post($project->path().'/cases',[
            'name' => 'Test case'
        ]);

        $this->get($project->path())->assertSee('Test case');

    }

    /** @test */
    public function a_case_has_inputs()
    {
        $this->withoutExceptionHandling();

        $multiplec = "yes,no,magari";
        $inputs = new Caseinput();
        $inputs->multiplechoice("va?",$multiplec)->text("va?")->format();

        $project = ProjectFactory::createdBy($this->signIn())
        ->create();



        $this->actingAs($project->created_by())->post($project->path().'/cases',[
            'name' => 'Test case',
            'inputs' => $inputs->content
        ]);


   //     $this->seeJson($inputs->content);
        $this->assertDatabaseHas('cases',['inputs' => $inputs->content]);

    }

    /** @test */
    public function only_the_owner_of_a_project_can_add_cases()
    {
        $this->signIn();
        $project = ProjectFactory::create();

        $this->post($project->path().'/cases',['name' => 'Test case'])->assertStatus(403);

        $this->assertDatabaseMissing('cases',['name' => 'Test case']);
    }

    /** @test */
    public function a_case_require_a_name()
    {
      $project = ProjectFactory::createdBy($this->signIn())
        ->withCases(1)
        ->create();

        $attributes = factory('App\Cases')->raw(['name' => '']);

        $this->post($project->path().'/cases',$attributes)->assertSessionHasErrors('name');

    }

}
