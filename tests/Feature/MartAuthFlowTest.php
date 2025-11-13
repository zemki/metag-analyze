<?php

namespace Tests\Feature\Api;

use App\Cases;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test MART 3-Screen Authentication Flow
 *
 * Tests the complete flow:
 * - Screen 1: Email check (+ optional password setup)
 * - Screen 2: Password check (returns tokens)
 * - Screen 3: Project access check (auto-creates case)
 *
 * Also tests flow validation (must complete screens in order)
 */
class MartAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $martProject;
    protected $nonMartProject;
    protected $projectOwner;
    protected $testUser;
    protected $testPassword = 'test-password-123';

    protected function setUp(): void
    {
        parent::setUp();

        // Create project owner
        $this->projectOwner = User::factory()->create();

        // Create MART project
        $this->martProject = Project::factory()->create([
            'name' => 'Test MART Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Configuration'],
            ]),
        ]);

        // Create non-MART project
        $this->nonMartProject = Project::factory()->create([
            'name' => 'Regular Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'text', 'name' => 'Some Input'],
            ]),
        ]);

        // Create test user with verified email
        $this->testUser = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt($this->testPassword),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * ====================
     * SCREEN 1: EMAIL CHECK TESTS
     * ====================
     */

    /** @test */
    public function it_checks_existing_email_in_screen_1()
    {
        $response = $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'testuser@example.com',
                'emailExists' => true,
            ]);

        // Verify cache was set
        $cacheKey = 'email_check:'.md5('testuser@example.com'.'127.0.0.1');
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_checks_non_existing_email_in_screen_1()
    {
        $response = $this->postJson('/api/mart/check-email', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'newuser@example.com',
                'emailExists' => false,
            ]);
    }

    /** @test */
    public function it_validates_email_format_in_screen_1()
    {
        $response = $this->postJson('/api/mart/check-email', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_sends_password_setup_email_for_new_user()
    {
        Mail::fake();

        // First, check email
        $this->postJson('/api/mart/check-email', [
            'email' => 'newuser@example.com',
        ]);

        // Then send password setup
        $response = $this->postJson('/api/mart/send-password-setup', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);

        // Verify email was sent
        Mail::assertSent(\App\Mail\VerificationEmail::class);
    }

    /** @test */
    public function it_rejects_password_setup_without_email_check()
    {
        $response = $this->postJson('/api/mart/send-password-setup', [
            'email' => 'newuser@example.com',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Flow validation failed',
                'step' => 'email_check_required',
            ]);
    }

    /** @test */
    public function it_rejects_password_setup_for_verified_user()
    {
        // Check email first
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        // Try to send password setup for verified user
        $response = $this->postJson('/api/mart/send-password-setup', [
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Email already registered',
            ]);
    }

    /**
     * ====================
     * SCREEN 2: PASSWORD CHECK TESTS
     * ====================
     */

    /** @test */
    public function it_authenticates_user_and_returns_tokens_in_screen_2()
    {
        // Screen 1: Check email
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        // Screen 2: Check password
        $response = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'email',
                'bearerToken',
                'refreshToken',
            ])
            ->assertJson([
                'email' => 'testuser@example.com',
            ]);

        // Verify tokens were stored in database
        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user->api_token);
        $this->assertNotNull($user->refresh_token);
        $this->assertNotNull($user->token_expires_at);
        $this->assertNotNull($user->refresh_token_expires_at);

        // Verify password check cache was set
        $cacheKey = 'password_check:'.md5('testuser@example.com'.'127.0.0.1');
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function it_rejects_password_check_without_email_check()
    {
        $response = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Flow validation failed',
                'step' => 'email_check_required',
            ]);
    }

    /** @test */
    public function it_rejects_invalid_password_in_screen_2()
    {
        // Screen 1: Check email
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        // Screen 2: Try with wrong password
        $response = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Authentication failed',
            ]);
    }

    /**
     * ====================
     * SCREEN 3: PROJECT ACCESS TESTS
     * ====================
     */

    /** @test */
    public function it_validates_project_access_and_auto_creates_case_in_screen_3()
    {
        // Complete Screen 1 and Screen 2
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        // Verify user has no case in this project yet
        $this->assertDatabaseMissing('cases', [
            'user_id' => $this->testUser->id,
            'project_id' => $this->martProject->id,
        ]);

        // Screen 3: Check project access
        $response = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'projectId' => $this->martProject->id,
                'participantIsAllowed' => true,
            ]);

        // Verify case was auto-created
        $this->assertDatabaseHas('cases', [
            'user_id' => $this->testUser->id,
            'project_id' => $this->martProject->id,
        ]);
    }

    /** @test */
    public function it_allows_access_when_case_already_exists()
    {
        // Create case first
        Cases::create([
            'name' => 'EXISTING_CASE',
            'user_id' => $this->testUser->id,
            'project_id' => $this->martProject->id,
            'duration' => 'startDay:'.now()->format('d.m.Y').'|',
        ]);

        // Complete Screen 1 and Screen 2
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        // Screen 3: Check project access
        $response = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'participantIsAllowed' => true,
            ]);

        // Verify only one case exists (no duplicate created)
        $caseCount = Cases::where('user_id', $this->testUser->id)
            ->where('project_id', $this->martProject->id)
            ->count();
        $this->assertEquals(1, $caseCount);
    }

    /** @test */
    public function it_rejects_project_access_check_without_password_check()
    {
        $response = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->martProject->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Flow validation failed',
                'step' => 'password_check_required',
            ]);
    }

    /** @test */
    public function it_rejects_non_mart_project_in_screen_3()
    {
        // Complete Screen 1 and Screen 2
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        // Screen 3: Try with non-MART project
        $response = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->nonMartProject->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Invalid project',
            ]);
    }

    /**
     * ====================
     * REFRESH TOKEN TESTS
     * ====================
     */

    /** @test */
    public function it_refreshes_tokens_with_valid_refresh_token()
    {
        // Complete Screen 1 and Screen 2 to get tokens
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $loginResponse = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        $refreshToken = $loginResponse->json('refreshToken');

        // Wait a moment to ensure new tokens are different
        sleep(1);

        // Refresh tokens
        $response = $this->postJson('/api/mart/refresh', [
            'refreshToken' => $refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'bearerToken',
                'refreshToken',
            ]);

        // Verify new tokens are different
        $this->assertNotEquals($refreshToken, $response->json('refreshToken'));

        // Verify old refresh token no longer works (token rotation)
        $secondRefresh = $this->postJson('/api/mart/refresh', [
            'refreshToken' => $refreshToken,
        ]);

        $secondRefresh->assertStatus(401);
    }

    /** @test */
    public function it_rejects_invalid_refresh_token()
    {
        $response = $this->postJson('/api/mart/refresh', [
            'refreshToken' => 'invalid-token-123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid refresh token',
            ]);
    }

    /**
     * ====================
     * COMPLETE FLOW TEST
     * ====================
     */

    /** @test */
    public function it_completes_entire_3_screen_flow_successfully()
    {
        // === SCREEN 1: Email Check ===
        $screen1 = $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $screen1->assertStatus(200)
            ->assertJson([
                'emailExists' => true,
            ]);

        // === SCREEN 2: Password Check ===
        $screen2 = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        $screen2->assertStatus(200)
            ->assertJsonStructure([
                'email',
                'bearerToken',
                'refreshToken',
            ]);

        $bearerToken = $screen2->json('bearerToken');

        // === SCREEN 3: Project Access ===
        $screen3 = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->martProject->id,
        ]);

        $screen3->assertStatus(200)
            ->assertJson([
                'participantIsAllowed' => true,
            ]);

        // Verify case was auto-created
        $this->assertDatabaseHas('cases', [
            'user_id' => $this->testUser->id,
            'project_id' => $this->martProject->id,
        ]);

        // Verify user can now use the bearer token for authenticated requests
        $this->assertNotEmpty($bearerToken);
    }

    /**
     * ====================
     * CACHE EXPIRATION TESTS
     * ====================
     */

    /** @test */
    public function it_rejects_password_check_if_email_check_expired()
    {
        // Check email
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        // Clear cache to simulate expiration
        $cacheKey = 'email_check:'.md5('testuser@example.com'.'127.0.0.1');
        Cache::forget($cacheKey);

        // Try password check
        $response = $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'step' => 'email_check_required',
            ]);
    }

    /** @test */
    public function it_rejects_project_access_if_password_check_expired()
    {
        // Complete Screen 1 and Screen 2
        $this->postJson('/api/mart/check-email', [
            'email' => 'testuser@example.com',
        ]);

        $this->postJson('/api/mart/check-password', [
            'email' => 'testuser@example.com',
            'password' => $this->testPassword,
        ]);

        // Clear password check cache to simulate expiration
        $cacheKey = 'password_check:'.md5('testuser@example.com'.'127.0.0.1');
        Cache::forget($cacheKey);

        // Try project access
        $response = $this->postJson('/api/mart/check-access', [
            'email' => 'testuser@example.com',
            'projectId' => $this->martProject->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'step' => 'password_check_required',
            ]);
    }
}
