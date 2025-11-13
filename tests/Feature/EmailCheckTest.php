<?php

namespace Tests\Feature\Api;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCheckTest extends TestCase
{
    use RefreshDatabase;

    protected $martProject;
    protected $projectOwner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user first (required for project creation)
        $this->projectOwner = User::factory()->create();

        // Create a MART project for testing
        $this->martProject = Project::factory()->create([
            'name' => 'Test MART Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Configuration'],
            ]),
        ]);
    }

    /** @test */
    public function it_returns_true_for_existing_email()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/check-email', [
            'email' => 'existing@example.com',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
                'message' => 'Email is registered in the system',
            ]);
    }

    /** @test */
    public function it_returns_false_for_non_existing_email()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'nonexistent@example.com',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => false,
                'message' => 'Email is not registered',
            ]);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'invalid-email',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_requires_email_field()
    {
        $response = $this->postJson('/api/check-email', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_handles_email_case_insensitively()
    {
        // Create a user with lowercase email
        $user = User::factory()->create([
            'email' => 'casetest@example.com',
        ]);

        // Check with uppercase
        $response = $this->postJson('/api/check-email', [
            'email' => 'CASETEST@EXAMPLE.COM',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);
    }

    /** @test */
    public function it_trims_whitespace_from_email()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'trimtest@example.com',
        ]);

        // Check with whitespace
        $response = $this->postJson('/api/check-email', [
            'email' => '  trimtest@example.com  ',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);
    }

    /** @test */
    public function it_is_rate_limited()
    {
        // Make multiple requests until we hit rate limit
        // This test verifies the rate limiter is active
        $hitRateLimit = false;
        $successCount = 0;

        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/check-email', [
                'email' => "ratelimit{$i}@example.com",
                'project_id' => $this->martProject->id,
            ]);

            if ($response->status() === 429) {
                $hitRateLimit = true;
                break;
            } elseif ($response->status() === 200) {
                $successCount++;
            }
        }

        // Assert that we hit the rate limit at some point
        $this->assertTrue(
            $hitRateLimit,
            'Expected to hit rate limit (429) but got ' . $successCount . ' successful requests'
        );

        // Assert we had at least some successful requests before hitting limit
        $this->assertGreaterThan(0, $successCount, 'Should have at least one successful request before rate limit');
    }

    /** @test */
    public function it_requires_project_id()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    /** @test */
    public function it_rejects_non_mart_projects()
    {
        // Create a non-MART project
        $nonMartProject = Project::factory()->create([
            'name' => 'Regular Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'text', 'name' => 'Some Input'],
            ]),
        ]);

        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $nonMartProject->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Invalid project',
                'message' => 'This endpoint is only available for MART projects. Please contact your researcher for access.',
            ]);
    }

    /** @test */
    public function it_rejects_invalid_project_id()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => 99999, // Non-existent project
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }
}
