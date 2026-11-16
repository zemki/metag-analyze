<?php

namespace Tests\Feature;

use App\Project;
use App\User;
use Tests\TestCase;

/**
 * "Leave Project" removes the invitation to one project. A request that names
 * no project used to detach every invitation of that user.
 */
class LeaveProjectTest extends TestCase
{
    /** @test */
    public function leaving_a_project_keeps_the_invitations_to_other_projects()
    {
        [$first, $second] = [$this->ownedProject(), $this->ownedProject()];
        $member = $this->memberOf($first, $second);

        $this->actingAs($member)
            ->postJson("/projects/invite/{$member->id}", ['email' => $member->email, 'project' => $first->id])
            ->assertOk();

        $this->assertSame([$second->id], $member->invites()->pluck('project_id')->all());
    }

    /** @test */
    public function a_leave_request_without_a_project_removes_no_invitation()
    {
        [$first, $second] = [$this->ownedProject(), $this->ownedProject()];
        $member = $this->memberOf($first, $second);

        $this->actingAs($member)
            ->postJson("/projects/invite/{$member->id}", ['email' => $member->email, 'study' => $first->id])
            ->assertStatus(422);

        $this->assertSame(2, $member->invites()->count());
    }

    private function ownedProject(): Project
    {
        return Project::factory()->create(['created_by' => $this->user->id, 'inputs' => '[]']);
    }

    private function memberOf(Project ...$projects): User
    {
        $member = User::factory()->researcher()->create(['email_verified_at' => now()]);
        $member->invites()->attach(array_map(fn (Project $project) => $project->id, $projects));

        return $member;
    }
}
