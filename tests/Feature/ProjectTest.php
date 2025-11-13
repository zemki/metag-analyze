<?php

namespace Tests\Feature;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use WithFaker;

    /** @test    */
    public function a_user_can_create_a_project()
    {
        $this->actingAs($this->user);

        $attributes = [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'created_by' => $this->user->id,
            'is_locked' => 0,
            'inputs' => [],
        ];

        $response = $this->post('/projects', $attributes);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'name' => $attributes['name'],
            'description' => $attributes['description'],
            'created_by' => $this->user->id,
        ]);

        $this->get('/projects')->assertSee($attributes['name']);
    }

    /** @test    */
    public function a_project_needs_name()
    {

        $this->actingAs($this->user);
        $attributes = Project::factory()->raw(['name' => '']);
        $this->post('/projects', $attributes)->assertSessionHasErrors('name');
    }

    /** @test    */
    public function a_project_needs_owner()
    {

        $this->actingAs($this->user);

        $attributes = Project::factory()->raw(['created_by' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('created_by');
    }

    /** @test */
    public function a_user_can_view_a_project()
    {

        $this->actingAs($this->user)->get($this->project->path())
            ->assertSee($this->project->name)
            ->assertSee($this->project->description);

    }

    /** @test */
    public function a_user_auth_cannot_view_other_projects()
    {
        $anotherUser = User::factory()->researcher()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $anotherProject = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => '[]',
        ]);

        $this->actingAs($anotherUser)->get($this->project->path())->assertStatus(403);
    }

    /** @test */
    public function a_user_auth_cannot_update_other_projects()
    {

        $this->actingAs($this->user);
        $anotherUser = User::factory()->researcher()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $project = Project::factory()->create(['created_by' => $anotherUser->id]);

        $this->patch($project->path())->assertStatus(403);
    }

    /** @test */
    public function guests_cannot_manage_projects()
    {
        $userNotResearcher = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($userNotResearcher);

        $this->get($this->project->path())->assertStatus(401);
        $this->get('/projects')->assertStatus(401);
        $this->get('/projects/new')->assertStatus(401);
        $this->get('/projects', $this->project->toArray())->assertStatus(401);
    }
}
