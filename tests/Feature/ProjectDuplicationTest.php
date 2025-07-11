<?php

namespace Tests\Feature;

use App\Cases;
use App\Entry;
use App\Media;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDuplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function duplicating_then_deleting_original_project_does_not_affect_duplicated_project_data()
    {
        $user = User::factory()->researcher()->create([
            'email_verified_at' => now(),
        ]);

        // Create original project
        $originalProject = Project::factory()->create([
            'created_by' => $user->id,
            'name' => 'Original Project',
            'inputs' => json_encode([
                ['name' => 'Test Question', 'type' => 'text', 'mandatory' => true]
            ])
        ]);

        // Create case and entries for original project
        $originalCase = Cases::factory()->create([
            'project_id' => $originalProject->id,
            'name' => 'Original Case'
        ]);

        $media = Media::factory()->create(['name' => 'Test Media']);

        $originalEntry = Entry::factory()->create([
            'case_id' => $originalCase->id,
            'media_id' => $media->id,
            'inputs' => json_encode(['Test Question' => 'Original Answer']),
            'begin' => now()->subHour()->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u')
        ]);

        // Count initial data (parent TestCase might create some)
        $initialProjectCount = Project::count();
        $initialCaseCount = Cases::count();
        $initialEntryCount = Entry::count();

        // Duplicate the project
        $response = $this->actingAs($user)
            ->get("/projects/{$originalProject->id}/duplicate");

        $response->assertStatus(200);

        // Verify duplication created a new project but NO new cases or entries
        $this->assertEquals($initialProjectCount + 1, Project::count()); // One more project
        $this->assertEquals($initialCaseCount, Cases::count());   // Same number of cases
        $this->assertEquals($initialEntryCount, Entry::count());   // Same number of entries

        $duplicatedProject = Project::where('id', '!=', $originalProject->id)
            ->where('name', 'Original Project')
            ->first();
        $this->assertNotNull($duplicatedProject);
        $this->assertEquals('Original Project', $duplicatedProject->name); // Same name
        $this->assertNotEquals($originalProject->id, $duplicatedProject->id); // Different ID

        // Verify duplicated project has NO cases or entries
        $this->assertEquals(0, $duplicatedProject->cases()->count());

        // Delete the original project
        $response = $this->actingAs($user)
            ->delete("/projects/{$originalProject->id}");

        $response->assertStatus(200);

        // Verify original project and its data are deleted
        $this->assertEquals($initialProjectCount, Project::count());  // Back to initial count
        $this->assertEquals($initialCaseCount - 1, Cases::count());    // Original case deleted via cascade
        $this->assertEquals($initialEntryCount - 1, Entry::count());    // Original entry deleted via cascade

        // Verify duplicated project still exists and is unaffected
        $duplicatedProject->refresh();
        $this->assertNotNull($duplicatedProject);
        $this->assertEquals('Original Project', $duplicatedProject->name);
        $this->assertEquals(0, $duplicatedProject->cases()->count()); // Still no cases/entries
    }

    /** @test */
    public function duplication_only_copies_project_and_media_relationships()
    {
        $user = User::factory()->researcher()->create([
            'email_verified_at' => now(),
        ]);

        // Create project with media
        $project = Project::factory()->create([
            'created_by' => $user->id,
            'name' => 'Test Project'
        ]);

        $media1 = Media::factory()->create(['name' => 'Media 1']);
        $media2 = Media::factory()->create(['name' => 'Media 2']);
        
        // Attach media to project
        $project->media()->attach([$media1->id, $media2->id]);

        // Create case with entries
        $case = Cases::factory()->create([
            'project_id' => $project->id
        ]);

        Entry::factory()->create([
            'case_id' => $case->id,
            'media_id' => $media1->id
        ]);

        // Duplicate project
        $response = $this->actingAs($user)
            ->get("/projects/{$project->id}/duplicate");

        $response->assertStatus(200);

        $duplicatedProject = Project::where('id', '!=', $project->id)
            ->where('name', 'Test Project')
            ->first();

        // Verify media relationships are copied
        $this->assertEquals(2, $duplicatedProject->media()->count());
        $this->assertTrue($duplicatedProject->media->contains($media1));
        $this->assertTrue($duplicatedProject->media->contains($media2));

        // Verify cases and entries are NOT copied
        $this->assertEquals(0, $duplicatedProject->cases()->count());
    }
}