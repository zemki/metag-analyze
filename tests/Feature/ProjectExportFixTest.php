<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectExportFixTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function project_export_handles_missing_numberofanswer_property()
    {
        // This test verifies the fix for the production error:
        // "Undefined property: stdClass::$numberofanswer"
        
        // Create a user
        $user = User::factory()->researcher()->create([
            'email_verified_at' => now(),
        ]);

        // Create a project with various input types
        // Some inputs intentionally missing 'numberofanswer' property
        $inputs = [
            [
                'name' => 'Text Input',
                'type' => 'text',
                'mandatory' => true
                // NO numberofanswer property - this would cause the error
            ],
            [
                'name' => 'Scale Input',
                'type' => 'scale',
                'mandatory' => false
                // NO numberofanswer property - this would cause the error
            ],
            [
                'name' => 'Choice Input',
                'type' => 'one choice',
                'mandatory' => true,
                'numberofanswer' => 3,  // This one HAS the property
                'answers' => ['Option A', 'Option B', 'Option C']
            ]
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs)
        ]);

        // Add user to project
        $project->invited()->attach($user->id);

        // Create a case for the project
        $case = Cases::factory()->create([
            'project_id' => $project->id
        ]);

        // Try to export - this should NOT throw an error
        $response = $this->actingAs($user)
            ->get("/projects/{$project->id}/export");

        // If we get a 200 response, the fix worked
        $response->assertStatus(200);
        
        // Verify it's returning Excel format
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function getNumberOfAnswersByQuestion_returns_null_for_missing_property()
    {
        $user = User::factory()->create();
        
        $inputs = [
            ['name' => 'No Property', 'type' => 'text'],
            ['name' => 'Has Property', 'type' => 'choice', 'numberofanswer' => 2]
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs)
        ]);

        // Should return null instead of throwing error
        $this->assertNull($project->getNumberOfAnswersByQuestion('No Property'));
        
        // Should return the value when property exists
        $this->assertEquals(2, $project->getNumberOfAnswersByQuestion('Has Property'));
    }

    /** @test */
    public function getAnswersByQuestion_returns_null_for_missing_property()
    {
        $user = User::factory()->create();
        
        $inputs = [
            ['name' => 'No Answers', 'type' => 'scale'],
            ['name' => 'Has Answers', 'type' => 'choice', 'answers' => ['A', 'B']]
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs)
        ]);

        // Should return null instead of throwing error
        $this->assertNull($project->getAnswersByQuestion('No Answers'));
        
        // Should return the array when property exists
        $this->assertEquals(['A', 'B'], $project->getAnswersByQuestion('Has Answers'));
    }
}