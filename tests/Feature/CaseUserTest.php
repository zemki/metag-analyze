<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_case_can_have_user()
    {
        $this->actingAs($this->user);

        $case = $this->project->addCase('test case', time());

        $user = User::factory()->researcher()->create();

        $user = $case->addUser($user);

        $this->assertDatabaseHas('cases', [
            'user_id' => $user->id,
        ]);

    }

    /** @test */
    public function mutliple_cases_can_have_one_user()
    {
        $this->actingAs($this->user);

        $this->create_multiple_cases_in_project($this->project, $this->user, 10);

        $this->assertTrue(\App\Cases::where('user_id', $this->user->id)->get()->count() > 1);
    }

    public function create_multiple_cases_in_project($project, $user, $numberofcases = 3)
    {
        for ($i = 0; $i < $numberofcases; $i++) {
            $case = $project->addCase($this->faker->name, time());
            $u = $case->addUser($user);
        }
    }
}
