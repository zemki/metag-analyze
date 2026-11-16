<?php

namespace Tests\Feature\Api;

use App\Cases;
use App\Project;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    private function martProject(): Project
    {
        return Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => json_encode([['type' => 'mart']]),
        ]);
    }

    private function addNewerMartCase(): Cases
    {
        return Cases::factory()->create([
            'project_id' => $this->martProject()->id,
            'user_id' => $this->user->id,
            'duration' => 'value:24|days:1',
            'created_at' => now()->addDay(),
        ]);
    }

    private function login()
    {
        return $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ]);
    }

    /** @test */
    public function a_newer_mart_case_does_not_replace_the_metag_case()
    {
        $this->addNewerMartCase();

        $this->login()->assertStatus(200)->assertJsonPath('case.id', $this->case->id);
    }

    /** @test */
    public function the_newest_of_several_metag_cases_is_the_one_used()
    {
        $newer = Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'duration' => 'value:24|days:1',
            'created_at' => now()->addHour(),
        ]);
        $this->addNewerMartCase();

        $this->login()->assertStatus(200)->assertJsonPath('case.id', $newer->id);
    }

    /** @test */
    public function the_response_still_includes_the_project_of_the_case()
    {
        $this->login()->assertJsonPath('case.project.id', $this->project->id);
    }

    /** @test */
    public function a_person_with_only_a_mart_case_gets_no_cases()
    {
        $this->case->update(['project_id' => $this->martProject()->id]);

        $this->login()->assertStatus(499)->assertJson(['case' => 'No cases']);
    }

    /** @test */
    public function a_refused_login_leaves_the_mart_case_untouched()
    {
        $this->case->update(['project_id' => $this->martProject()->id]);

        $this->login();

        $this->assertNull($this->case->fresh()->first_login_at);
    }

    /** @test */
    public function a_refused_login_does_not_query_mart()
    {
        $this->case->update(['project_id' => $this->martProject()->id]);
        $mart = DB::connection('mart');
        $mart->flushQueryLog();
        $mart->enableQueryLog();

        $this->login();

        $this->assertSame([], array_column($mart->getQueryLog(), 'query'));
    }

    /** @test */
    public function a_newest_metag_case_that_is_backend_only_still_gets_no_cases()
    {
        Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'duration' => 'value:0|days:0',
            'created_at' => now()->addHour(),
        ]);

        $this->login()->assertStatus(499)->assertJson(['case' => 'No cases']);
    }

    /** @test */
    public function it_returns_token_on_successful_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'deviceID' => 'test-device-id-123',
            'datetime' => time(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'inputs',
                'case',
                'token',
                'duration',
                'custominputs',
                'notstarted',
            ]);

        $this->assertNotNull($response->json('token'));
    }

    /** @test */
    public function force_api_v2_is_read_from_config()
    {
        // A project from before the v2 cutoff would get v1 unless forced.
        $this->project->forceFill(['created_at' => '2024-01-01 00:00:00'])->save();
        config(['app.force_api_v2' => true]);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ])->assertStatus(200)->assertJson(['api_version' => 'v2']);
    }

    /** @test */
    public function it_stores_the_device_id_on_login()
    {
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'deviceID' => 'test-device-id-123',
            'datetime' => time(),
        ])->assertStatus(200);

        $this->assertContains('test-device-id-123', $this->user->fresh()->deviceID);
    }

    /** @test */
    public function it_returns_error_for_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'datetime' => time(),
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'invalid credentials']);
    }

    /** @test */
    public function it_returns_no_cases_for_user_without_cases()
    {

        $this->user = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
        ]);
        $response = $this->postJson('/api/login', [
            'email' => 'test2@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ]);

        $response->assertStatus(499)
            ->assertJson(['case' => 'No cases']);
    }

    /** @test */
    public function it_tracks_failed_login_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test3@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Disable rate limiting for this test
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        for ($i = 0; $i < 11; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test3@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $user->refresh();

        $this->assertEquals(11, $user->failed_login_attempts);
        $this->assertNotNull($user->lockout_until);
    }

    /** @test */
    public function it_creates_profile_if_not_exists()
    {
        $this->user->profile()->delete();

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ]);

        $this->assertNotNull($this->user->fresh()->profile);
    }
}
