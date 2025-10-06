<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_case_can_be_updated()
    {

        $this->actingAs($this->user);

        $this->patch('/projects/' . $this->project->id . $this->project->cases[0]->path(), [
            'name' => 'changed',
        ]);

        $this->assertDatabaseHas('cases', [
            'name' => 'changed',
        ]);

    }

    /** @test */
    public function a_project_can_have_cases()
    {
        $this->actingAs($this->user);

        $this->post($this->project->path() . '/cases', [
            'name' => 'Test case',
            'duration' => 'test duration',
            'email' => $this->user->email,
        ]);

        $this->assertDatabaseHas('cases', [
            'name' => 'Test case',
            'project_id' => $this->project->id,
        ]);

        $this->get($this->project->path())->assertOk();
    }

    /** @test */
    public function only_the_owner_of_a_project_can_add_cases()
    {
        $user = User::factory()->researcher()->create();
        $this->actingAs($user);

        $this->assertNotEquals($user->id, $this->project->created_by);

        $this->post($this->project->path() . '/cases', [
            'name' => 'Test case',
            'duration' => 'test duration',
            'email' => $this->user->email,
        ]);

        $this->assertDatabaseMissing('cases', ['name' => 'Test case']);
    }

    /** @test */
    public function a_case_require_a_name()
    {
        $this->actingAs($this->user);

        $this->post($this->project->path() . '/cases', [
            'name' => '',
            'duration' => 'test duration',
            'email' => $this->user->email,
        ])->assertSessionHasErrors(['message' => 'The name field is required.']);

    }
}
