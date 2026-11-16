<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

/**
 * The Notification Center lists every participant of a project with their
 * user data, so only people who may see the project can open it.
 */
class NotificationCenterAccessTest extends TestCase
{
    /** @test */
    public function the_project_owner_can_open_the_notification_center()
    {
        $this->actingAs($this->user)
            ->get("/projects/{$this->project->id}/notifications")
            ->assertOk();
    }

    /** @test */
    public function an_invited_researcher_can_open_the_notification_center()
    {
        $invitee = $this->researcher();
        $invitee->invites()->attach($this->project->id);

        $this->actingAs($invitee)
            ->get("/projects/{$this->project->id}/notifications")
            ->assertOk();
    }

    /** @test */
    public function a_researcher_outside_the_project_cannot_open_the_notification_center()
    {
        $this->actingAs($this->researcher())
            ->get("/projects/{$this->project->id}/notifications")
            ->assertForbidden();
    }

    private function researcher(): User
    {
        return User::factory()->researcher()->create(['email_verified_at' => now()]);
    }
}
