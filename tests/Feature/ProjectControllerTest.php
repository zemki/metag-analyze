<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\Project;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = [
            ['name' => 'admin', 'description' => 'Admin'],
            ['name' => 'researcher', 'description' => 'Researcher'],
            ['name' => 'user', 'description' => 'User'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }

    public function testIndex()
    {
        $user = User::factory()->researcher()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);
        $user->projects()->save($project);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('projects.index');
        $response->assertViewHas('projects');
    }

public function testShow()
{
    $user = User::factory()->researcher()->create();
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    $response = $this->actingAs($user)->get('/projects/' . $project->id);
    $response->assertStatus(200);
    $response->assertViewIs('projects.show');
    $response->assertViewHas('project');
}

public function testDestroyProject()
{
    $user = User::factory()->researcher()->create();
    $otherUser = User::factory()->researcher()->create();
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    // Test if an authorized user can delete the project
    $this->actingAs($user);
    $response = $this->delete(route('projects.destroy', $project));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);

    // Recreate the project for the next test
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    // Test if an unauthorized user cannot delete the project
    $otherUser->invites()->attach($project->id);
    $this->actingAs($otherUser);
    $response = $this->delete(route('projects.destroy', $project));
    $response->assertStatus(401);
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
}

public function testDestroyCaseWithMultipleCases()
{
    $x = 5; // Define x number of cases here.

    $user = User::factory()->researcher()->create();
    $otherUser = User::factory()->researcher()->create();
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    // Create x cases
    $cases = Cases::factory($x)->create(['project_id' => $project->id]);

    // Test if an authorized user can delete the cases
    $this->actingAs($user);
    foreach ($cases as $case) {
        $response = $this->delete(route('cases.destroy', $case));
        $response->assertStatus(200);
        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    // Recreate x cases for the next test
    $cases = Cases::factory($x)->create(['project_id' => $project->id]);

    // Invite otherUser to the project
    $otherUser->invites()->attach($project->id);

    // Test if an invited user cannot delete the cases
    $this->actingAs($otherUser);
    foreach ($cases as $case) {
        $response = $this->delete(route('cases.destroy', $case));
        $response->assertStatus(401); // Assuming unauthorized users receive a 401 status.
        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }
}

public function testDestroyProjectWithMultipleInvitedUsers()
{
    $x = 5; // Define x number of users here.

    $user = User::factory()->researcher()->create();
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    // Create x invited users
    $invitedUsers = User::factory($x)->researcher()->create();

    // Invite the users to the project
    foreach ($invitedUsers as $invitedUser) {
        $invitedUser->invites()->attach($project->id);
    }

    // Test if an authorized user can delete the project
    $this->actingAs($user);
    $response = $this->delete(route('projects.destroy', $project));
    $response->assertStatus(200);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
}

public function testCreateProjectWithCasesAndEntries()
{

    $user = User::factory()->researcher()->create();
    $project = Project::factory()->create(['created_by' => $user->id]);
    $user->projects()->save($project);

    // Create 5 cases
    $cases = Cases::factory(5)->create(['project_id' => $project->id]);

    // Create 100 entries for each case
    foreach ($cases as $case) {
        $entries = Entry::factory(100)->create(['case_id' => $case->id]);
        $this->assertCount(100, $entries);
    }

}
}
