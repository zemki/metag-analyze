<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\Tests\Setup\ProjectFactory;
use App\CaseInput;
class ProjectTest extends TestCase
{
    use WithFaker, RefreshDatabase ;

    /** @test    */
    public function a_user_can_create_a_project()
    {
        //$this->withoutExceptionHandling();
        $this->signIn();

    //    $this->signIn();

        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'created_by' => auth()->user()->id,
            'is_locked' => 0
        ];

       //$attributes = factory('App\Project')->raw();

        $this->post('/projects',$attributes)->assertRedirect('/projects');

        $this->assertDatabaseHas('projects', $attributes);

        $this->get('/projects')->assertSee($attributes['name']);
    }

    /** @test    */
    public function a_project_needs_name(){

        $this->signIn();
        $attributes = factory('App\Project')->raw(['name' => '']);
        $this->post('/projects',$attributes)->assertSessionHasErrors('name');
    }

    /** @test    */
    public function a_project_needs_owner(){

        $this->signIn();

        $attributes = factory('App\Project')->raw(['created_by' => '']);

        $this->post('/projects',$attributes)->assertSessionHasErrors('created_by');
    }

    /** @test */
    public function a_user_can_view_a_project(){

        $this->withoutExceptionHandling();
        $project = ProjectFactory::createdBy($this->signIn())
        ->create();


        $this->actingAs(auth()->user())->get($project->path())
        ->assertSee($project->name)
        ->assertSee($project->description);

    }

    /** @test */
    public function a_user_auth_cannot_view_other_projects()
    {

        $this->signIn();

        $project = factory('App\Project')->create();

        $this->get($project->path())->assertStatus(403);
    }

    /** @test */
    public function a_user_auth_cannot_update_other_projects()
    {

        $this->signIn();

        $project = factory('App\Project')->create();

        $this->patch($project->path())->assertStatus(403);
    }

    /** @test */
    public function guests_cannot_manage_projects()
    {
        $project = factory('App\Project')->create();

        $this->get($project->path())->assertRedirect('login');
        $this->get('/projects')->assertRedirect('login');
        $this->get('/projects/create')->assertRedirect('login');
        $this->get('/projects',$project->toArray())->assertRedirect('login');
    }

    /** @test */
    public function project_inputs_can_be_edited_only_when_zero_entries()
    {

        $user = $this->signIn();
        $multiplec = "yes,no,magari";
        $inputs = new Caseinput();
        $inputs->multiplechoice("va?",$multiplec)->text("va?")->format();

        $project = ProjectFactory::createdBy($user)
        ->withInputs($inputs->content)
        ->create();

        $project2 = factory(\App\Project::class)->make(['created_by'=> $user]);

        $projectArray = ["name" =>  $project->name, "description" => $project2->description,"inputs" => 'no inputs'];

        $this->patch($project->path(),$projectArray);
        $this->assertDatabaseHas('projects',['inputs' =>'no inputs']);

    }


}
