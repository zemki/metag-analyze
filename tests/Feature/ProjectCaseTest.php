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
public function a_project_has_inputs()
{
  $this->withoutExceptionHandling();

  $multiplec = "yes,no,magari";
  $inputs = new Caseinput();
  $inputs->multiplechoice("va?",$multiplec)->text("va?")->format();

  $project = ProjectFactory::createdBy($this->signIn())
  ->withInputs($inputs->content)
  ->create();



   //     $this->seeJson($inputs->content);
  $this->assertDatabaseHas('projects',['inputs' => $inputs->content]);

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

/** @test */
public function a_case_can_have_entries()
{
    $this->withoutExceptionHandling();

  $project = ProjectFactory::createdBy($this->signIn())
  ->withCases(1)
  ->create();

  $case = $project->cases()->first();

  $newentry = factory('App\Entry')->make(['case_id' => $case->id])->toArray();

  $this->actingAs($case->user()->first())->post($case->path().'/entries',$newentry);
  $this->assertDatabaseHas('entries',['begin' => $newentry['begin']]);

}

}
