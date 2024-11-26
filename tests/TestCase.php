<?php

namespace Tests;

use App\Cases;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,RefreshDatabase,WithFaker;

    public User $user;

    public Project $project;

    public Cases $case;

    public function setUp(): void
    {
        parent::setUp();

        // Create test user with a case
        $this->user = User::factory()->researcher()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->project = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => '[]',
        ]);

        $this->case = Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'duration' => 'value:24|days:1',
        ]);
    }

    protected function create_user()
    {

        $user = [
            'username' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'test',
        ];
        $this->post('/users', $user);

        $user = \App\User::where('email', $user['email'])->first();

        return $user;
    }
}
