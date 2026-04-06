<?php

namespace Tests\Feature;

use App\Cases;
use App\Project;
use App\User;
use Tests\TestCase;

/**
 * Test that ForceJsonResponse middleware prevents 302 redirects
 * on all MART API routes.
 *
 * Uses $this->post() (NOT postJson()) to simulate requests without
 * the Accept: application/json header — the middleware should force JSON anyway.
 */
class ForceJsonResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /** @test */
    public function it_returns_json_not_redirect_for_unauthenticated_device_infos()
    {
        $response = $this->post('/mart-api/device-infos', []);

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_json_not_redirect_for_unauthenticated_stats()
    {
        $response = $this->post('/mart-api/stats', []);

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_json_not_redirect_for_unauthenticated_file_upload()
    {
        $response = $this->post('/mart-api/cases/1/files', []);

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_json_not_redirect_for_unauthenticated_submit()
    {
        $response = $this->post('/mart-api/cases/1/submit', []);

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_json_not_redirect_for_unauthenticated_structure()
    {
        $response = $this->get('/mart-api/projects/1/structure');

        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /** @test */
    public function it_returns_json_for_check_password_without_email_check()
    {
        $response = $this->post('/api/mart/check-password', [
            'email' => 'test@example.com',
            'password' => 'somepassword',
        ]);

        // Should get 403 (flow validation) not 302 redirect
        $response->assertStatus(403);
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_returns_json_for_check_access_without_password_check()
    {
        $response = $this->post('/api/mart/check-access', [
            'email' => 'test@example.com',
            'projectId' => $this->project->id,
        ]);

        // Should get 403 (flow validation) not 302 redirect
        $response->assertStatus(403);
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_returns_json_for_refresh_with_invalid_token()
    {
        $response = $this->post('/api/mart/refresh', [
            'refreshToken' => 'invalid-token',
        ]);

        // Should get 401 (invalid token) not 302 redirect
        $response->assertStatus(401);
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonStructure(['error', 'message']);
    }

    /** @test */
    public function it_returns_json_for_check_email_validation_error()
    {
        $response = $this->post('/api/mart/check-email', []);

        // Should get 422 (validation) not 302 redirect
        $response->assertStatus(422);
        $response->assertHeader('content-type', 'application/json');
    }
}
