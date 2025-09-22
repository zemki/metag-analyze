<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\Media;
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
                'mandatory' => true,
                // NO numberofanswer property - this would cause the error
            ],
            [
                'name' => 'Scale Input',
                'type' => 'scale',
                'mandatory' => false,
                // NO numberofanswer property - this would cause the error
            ],
            [
                'name' => 'Choice Input',
                'type' => 'one choice',
                'mandatory' => true,
                'numberofanswer' => 3,  // This one HAS the property
                'answers' => ['Option A', 'Option B', 'Option C'],
            ],
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs),
        ]);

        // Add user to project
        $project->invited()->attach($user->id);

        // Create a case for the project
        $case = Cases::factory()->create([
            'project_id' => $project->id,
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
    public function get_number_of_answers_by_question_returns_null_for_missing_property()
    {
        $user = User::factory()->create();

        $inputs = [
            ['name' => 'No Property', 'type' => 'text'],
            ['name' => 'Has Property', 'type' => 'choice', 'numberofanswer' => 2],
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs),
        ]);

        // Should return null instead of throwing error
        $this->assertNull($project->getNumberOfAnswersByQuestion('No Property'));

        // Should return the value when property exists
        $this->assertEquals(2, $project->getNumberOfAnswersByQuestion('Has Property'));
    }

    /** @test */
    public function get_answers_by_question_returns_null_for_missing_property()
    {
        $user = User::factory()->create();

        $inputs = [
            ['name' => 'No Answers', 'type' => 'scale'],
            ['name' => 'Has Answers', 'type' => 'choice', 'answers' => ['A', 'B']],
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs),
        ]);

        // Should return null instead of throwing error
        $this->assertNull($project->getAnswersByQuestion('No Answers'));

        // Should return the array when property exists
        $this->assertEquals(['A', 'B'], $project->getAnswersByQuestion('Has Answers'));
    }

    /** @test */
    public function export_handles_missing_input_values_and_media()
    {
        $user = User::factory()->researcher()->create([
            'email_verified_at' => now(),
        ]);

        // Create a project with multiple input types
        $inputs = [
            ['name' => 'Text Question', 'type' => 'text', 'mandatory' => true],
            ['name' => 'Scale Question', 'type' => 'scale', 'mandatory' => false],
            ['name' => 'Choice Question', 'type' => 'one choice', 'mandatory' => true, 'numberofanswer' => 2, 'answers' => ['Yes', 'No']],
        ];

        $project = Project::factory()->create([
            'created_by' => $user->id,
            'inputs' => json_encode($inputs),
        ]);

        $project->invited()->attach($user->id);

        $case = Cases::factory()->create([
            'project_id' => $project->id,
        ]);

        // Create a media record
        $media = Media::factory()->create();

        // Create entries with missing values (testing column alignment)
        Entry::factory()->create([
            'case_id' => $case->id,
            'media_id' => $media->id,
            'begin' => now()->subHour()->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'inputs' => json_encode([
                'Text Question' => 'Some text',
                // Missing 'Scale Question' and 'Choice Question'
            ]),
        ]);

        Entry::factory()->create([
            'case_id' => $case->id,
            'media_id' => $media->id,
            'begin' => now()->subHour()->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
            'inputs' => json_encode([
                'Scale Question' => 5,
                'Choice Question' => ['Yes'],
                // Missing 'Text Question'
            ]),
        ]);

        // Export should work without column misalignment
        $response = $this->actingAs($user)
            ->get("/projects/{$project->id}/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
