<?php

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

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
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $this->user->refresh();

        $this->assertEquals(6, $this->user->failed_login_attempts);
        $this->assertNotNull($this->user->lockout_until);
    }

    /** @test */
    public function it_saves_device_id_on_login()
    {
        $deviceId = 'test-device-id-123';

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'deviceID' => $deviceId,
            'datetime' => time(),
        ]);

        $this->user->refresh();
        $this->assertContains($deviceId, $this->user->deviceID);
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

        $this->assertNotNull($this->user->profile);
    }
}
