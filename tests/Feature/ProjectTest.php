<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use WithFaker, RefreshDatabase ;


    public function create_user(){
        $this->withoutExceptionHandling();
        $user = [
            'id' => 1,
            'username' => 'alebelli',
            'email' => 'belli@uni-bremen.de',
            'password' => bcrypt("test")
        ];
        $this->post('/users',$user);

        $this->assertDatabaseHas('users', $user);
    }

    /** @test    */
    public function a_user_can_create_a_project()
    {
                //$this->withoutExceptionHandling();
                $this->signIn();

    //    $this->signIn();

        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'duration' => 'a lot',
            'is_locked' => 0
        ];

       //$attributes = factory('App\Project')->raw();
        $this->post('/projects',$attributes)->assertRedirect('/projects');

        $this->assertDatabaseHas('projects', $attributes);

        $this->get('/projects')->assertSee($attributes['name']);
    }





    /** @test    */
    public function a_project_needs_title(){
                $this->withoutExceptionHandling();

        $this->signIn();
        $attributes = factory('App\Project')->raw(['name' => '']);

        $this->post('/projects',$attributes)->assertSessionHasErrors('name');
    }

    /** @test    */
    public function a_project_needs_owner(){

        $this->withoutExceptionHandling();

        $this->signIn();

        $attributes = factory('App\Project')->raw(['created_by' => '']);

        $this->post('/projects',$attributes)->assertSessionHasErrors('created_by');
    }

    /** @test */
    public function a_user_can_view_a_project(){

        $this->withoutExceptionHandling();
        $this->signIn();

        $project = factory('App\Project')->create(['created_by' => auth()->id()]);


        $this->get($project->path())
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
    public function guests_cannot_manage_projects()
    {
        $project = factory('App\Project')->create();

        $this->get($project->path())->assertRedirect('login');
        $this->get('/projects')->assertRedirect('login');
        $this->get('/projects/create')->assertRedirect('login');
        $this->get('/projects',$project->toArray())->assertRedirect('login');
    }


}
