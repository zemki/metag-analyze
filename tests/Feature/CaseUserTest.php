<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\Cases;


class CaseUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_case_can_have_user()
    {
        $this->signIn();

        $project = auth()->user()->projects()->create(factory(Project::class)->raw());

        $case = $project->addCase("test case","test duration");

        $user = [
            'username' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => "test"
        ];

        $user = $case->addUser($user);

        $this->assertDatabaseHas('cases',[
            'user_id' => $user->id
        ]);

    }

    /** @test */
    public function mutliple_cases_can_have_one_user()
    {
        $user = $this->signIn();

        $project = auth()->user()->projects()->create(factory(Project::class)->raw());



      //  $user = $this->create_user();

        $this->create_multiple_cases_in_project($project,$user,10);

        $this->assertTrue(\App\Cases::where('user_id',$user->id)->get()->count() > 1);
    }


    public function create_multiple_cases_in_project($project,$user,$numberofcases = 3)
    {
        for ($i=0; $i < $numberofcases; $i++) {
           $case = $project->addCase("test case","test duration");
           $u = $case->addUser($user);
       }
   }

}
