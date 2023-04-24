<?php

namespace Tests\Feature;

use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCaseTest extends TestCase
{
 use RefreshDatabase;

 /** @test */
 public function a_case_can_be_updated()
 {

  $project = ProjectFactory::createdBy($this->signIn())
      ->withCases(1)
      ->create();

  $this->patch('/projects/' . $project->id . $project->cases[0]->path(), [
      'name' => 'changed',
  ]);

  $this->assertDatabaseHas('cases', [
      'name' => 'changed',
  ]);

}

/** @test */
public function a_project_can_have_cases()
{
    $this->withoutExceptionHandling();
  $project = ProjectFactory::createdBy($this->signIn())->create();

  $this->actingAs($project->created_by())->post($project->path() . '/cases', [
      'name' => 'Test case',
      'duration' => 'test duration',
      'email' => $project->created_by()->email,
  ]);

  $this->get($project->path())->assertSee('Test case');

}

/** @test */
public function only_the_owner_of_a_project_can_add_cases()
{
  $this->signIn();
  $project = ProjectFactory::create();

  $this->post($project->path() . '/cases', ['name' => 'Test case'])->assertStatus(403);

  $this->assertDatabaseMissing('cases', ['name' => 'Test case']);
}

/** @test */
public function a_case_require_a_name()
{
    $this->withoutExceptionHandling();
    $user = $this->signIn();
  $project = ProjectFactory::createdBy($this->signIn())
      ->withCases(1)
      ->create();

  $attributes = factory('App\Cases')->raw(['name' => '']);

  $this->post($project->path() . '/cases', $attributes)->assertSessionHasErrors('name');

}

/** @test */
public function a_case_can_have_entries()
{
    $this->withoutExceptionHandling();
   $user = $this->signIn();

  $project = ProjectFactory::createdBy($user)
      ->withCases(1)
      ->create();

  $case = $project->cases()->first();

  $newentry = factory('App\Entry')->make(['case_id' => $case->id])->toArray();

    // @TODO build a test with api authentication
  $this->actingAs($case->user()->first())->post('/api/v1' . $case->path() . '/entries', $newentry);
  $this->assertDatabaseHas('entries', ['begin' => $newentry['begin']]);

}
}
