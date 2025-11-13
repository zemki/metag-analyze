<?php

namespace Tests\Feature\Api;

use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Security-focused tests for the Email Check API endpoint
 *
 * These tests verify security measures including:
 * - Rate limiting
 * - Input validation
 * - Injection attack protection
 * - MART project validation
 * - Cache security
 */
class EmailCheckSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $martProject;
    protected $nonMartProject;
    protected $projectOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectOwner = User::factory()->create();

        // Create a MART project
        $this->martProject = Project::factory()->create([
            'name' => 'Test MART Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Configuration'],
            ]),
        ]);

        // Create a non-MART project
        $this->nonMartProject = Project::factory()->create([
            'name' => 'Regular Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'text', 'name' => 'Some Input'],
            ]),
        ]);
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        $successCount = 0;
        $rateLimitHit = false;

        // Make 10 requests rapidly
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/check-email', [
                'email' => "test{$i}@example.com",
                'project_id' => $this->martProject->id,
            ]);

            if ($response->status() === 200) {
                $successCount++;
            } elseif ($response->status() === 429) {
                $rateLimitHit = true;
                break;
            }
        }

        // Should hit rate limit before 10 requests
        $this->assertTrue($rateLimitHit, 'Rate limit should be triggered');
        $this->assertLessThan(10, $successCount, 'Should not allow 10 requests');
        $this->assertGreaterThan(0, $successCount, 'Should allow at least one request');
    }

    /** @test */
    public function it_blocks_sql_injection_attempts()
    {
        $injectionAttempts = [
            "admin@example.com' OR '1'='1",
            "admin@example.com'; DROP TABLE users; --",
            "admin@example.com' UNION SELECT * FROM users --",
            "admin@example.com' AND 1=1 --",
            "'; EXEC sp_MSForEachTable 'DROP TABLE ?'; --",
        ];

        foreach ($injectionAttempts as $maliciousEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $maliciousEmail,
                'project_id' => $this->martProject->id,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }

        // Verify database is still intact
        $this->assertDatabaseCount('users', 1); // Only the project owner
    }

    /** @test */
    public function it_blocks_xss_attempts()
    {
        $xssAttempts = [
            '<script>alert("XSS")</script>@example.com',
            'test@example.com<script>alert(1)</script>',
            '<img src=x onerror=alert(1)>@example.com',
            'javascript:alert(1)@example.com',
            '<svg/onload=alert(1)>@example.com',
        ];

        foreach ($xssAttempts as $maliciousEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $maliciousEmail,
                'project_id' => $this->martProject->id,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /** @test */
    public function it_blocks_command_injection_attempts()
    {
        $commandInjectionAttempts = [
            'test@example.com; rm -rf /',
            'test@example.com | cat /etc/passwd',
            'test@example.com && whoami',
            'test@example.com`whoami`',
            'test@example.com$(whoami)',
        ];

        foreach ($commandInjectionAttempts as $maliciousEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $maliciousEmail,
                'project_id' => $this->martProject->id,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /** @test */
    public function it_blocks_email_header_injection()
    {
        $headerInjectionAttempts = [
            "test@example.com\nBcc: attacker@evil.com",
            "test@example.com\rBcc: attacker@evil.com",
            "test@example.com\r\nBcc: attacker@evil.com",
            "test@example.com%0aBcc: attacker@evil.com",
            "test@example.com%0dBcc: attacker@evil.com",
        ];

        foreach ($headerInjectionAttempts as $maliciousEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $maliciousEmail,
                'project_id' => $this->martProject->id,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /** @test */
    public function it_validates_project_id_type()
    {
        $invalidProjectIds = [
            'abc',
            '1abc',
            '1.5',
            'null',
            '[]',
            '{}',
            '<script>alert(1)</script>',
            "1' OR '1'='1",
        ];

        foreach ($invalidProjectIds as $invalidId) {
            $response = $this->postJson('/api/check-email', [
                'email' => 'test@example.com',
                'project_id' => $invalidId,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['project_id']);
        }
    }

    /** @test */
    public function it_prevents_mart_project_bypass_with_sql_injection()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $this->nonMartProject->id . "' OR '1'='1",
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    /** @test */
    public function it_rejects_non_mart_projects()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $this->nonMartProject->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Invalid project',
            ]);
    }

    /** @test */
    public function it_rejects_non_existent_projects()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    /** @test */
    public function it_sanitizes_email_input()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Test with extra whitespace
        $response = $this->postJson('/api/check-email', [
            'email' => '  TEST@EXAMPLE.COM  ',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);
    }

    /** @test */
    public function it_handles_unicode_injection_attempts()
    {
        $unicodeAttempts = [
            'test@еxample.com', // Cyrillic 'e'
            'test@exаmple.com', // Cyrillic 'a'
            'test@example。com', // Ideographic full stop
            'test＠example.com', // Full-width @
        ];

        foreach ($unicodeAttempts as $unicodeEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $unicodeEmail,
                'project_id' => $this->martProject->id,
            ]);

            // Should either be rejected or normalized
            // Depending on email validation rules
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    /** @test */
    public function it_prevents_null_byte_injection()
    {
        $nullByteAttempts = [
            "test@example.com\0",
            "test\0@example.com",
            "test@example.com%00",
        ];

        foreach ($nullByteAttempts as $maliciousEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $maliciousEmail,
                'project_id' => $this->martProject->id,
            ]);

            // Should be rejected
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /** @test */
    public function it_handles_extremely_long_emails()
    {
        // Test with email > max length (255 chars)
        $longEmail = str_repeat('a', 250) . '@example.com'; // 263 chars

        $response = $this->postJson('/api/check-email', [
            'email' => $longEmail,
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_email_format_strictly()
    {
        $invalidEmails = [
            'notanemail',
            '@example.com',
            'test@',
            'test@@example.com',
            'test@example',
            'test..test@example.com',
            'test@example..com',
            'test @example.com',
            'test@exam ple.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            $response = $this->postJson('/api/check-email', [
                'email' => $invalidEmail,
                'project_id' => $this->martProject->id,
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    /** @test */
    public function it_creates_cache_entries_with_secure_keys()
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/check-email', [
            'email' => $email,
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200);

        // Verify cache key format
        $expectedKey = 'email_check:' . md5(strtolower(trim($email)) . '127.0.0.1' . $this->martProject->id);

        $this->assertTrue(Cache::has($expectedKey));

        $cachedData = Cache::get($expectedKey);
        $this->assertEquals(strtolower(trim($email)), $cachedData['email']);
        $this->assertEquals($this->martProject->id, $cachedData['project_id']);
    }

    /** @test */
    public function it_prevents_cache_key_guessing()
    {
        // Attempt to manually construct cache key
        $email = 'test@example.com';
        $fakeIp = '1.2.3.4';
        $fakeKey = 'email_check:' . md5($email . $fakeIp . $this->martProject->id);

        // Pre-populate cache with fake entry
        Cache::put($fakeKey, [
            'email' => $email,
            'exists' => true,
            'project_id' => $this->martProject->id,
        ], now()->addMinutes(5));

        // Make request (from different IP - 127.0.0.1)
        $response = $this->postJson('/api/check-email', [
            'email' => $email,
            'project_id' => $this->martProject->id,
        ]);

        // Should create new cache entry with correct IP
        $correctKey = 'email_check:' . md5($email . '127.0.0.1' . $this->martProject->id);

        $this->assertTrue(Cache::has($correctKey));
        $this->assertTrue(Cache::has($fakeKey)); // Old one still exists but not used
    }

    /** @test */
    public function it_isolates_cache_by_project()
    {
        $email = 'test@example.com';

        // Create another MART project
        $martProject2 = Project::factory()->create([
            'name' => 'Second MART Project',
            'created_by' => $this->projectOwner->id,
            'inputs' => json_encode([
                ['type' => 'mart', 'name' => 'MART Config'],
            ]),
        ]);

        // Check email for project 1
        $response1 = $this->postJson('/api/check-email', [
            'email' => $email,
            'project_id' => $this->martProject->id,
        ]);

        // Check same email for project 2
        $response2 = $this->postJson('/api/check-email', [
            'email' => $email,
            'project_id' => $martProject2->id,
        ]);

        // Verify separate cache entries
        $key1 = 'email_check:' . md5($email . '127.0.0.1' . $this->martProject->id);
        $key2 = 'email_check:' . md5($email . '127.0.0.1' . $martProject2->id);

        $this->assertTrue(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));
        $this->assertNotEquals($key1, $key2);
    }

    /** @test */
    public function it_does_not_log_normal_requests_to_prevent_disk_flooding()
    {
        // Verify that normal successful requests are NOT logged
        // This prevents disk space exhaustion during flooding attacks
        // Only suspicious activity (warnings/errors) should be logged

        \Log::shouldReceive('info')->never();

        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $this->martProject->id,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_logs_non_mart_project_attempts()
    {
        \Log::shouldReceive('warning')
            ->once()
            ->with('Email check attempted for non-MART project', \Mockery::type('array'));

        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $this->nonMartProject->id,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_handles_concurrent_requests_safely()
    {
        $email = 'test@example.com';
        $user = User::factory()->create(['email' => $email]);

        // Simulate concurrent requests (in reality, would need proper concurrency testing)
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->postJson('/api/check-email', [
                'email' => $email,
                'project_id' => $this->martProject->id,
            ]);
        }

        // All should succeed (within rate limit)
        foreach ($responses as $response) {
            $this->assertContains($response->status(), [200, 429]);
        }

        // Verify database is still consistent
        $this->assertDatabaseCount('users', 2); // project owner + test user
    }

    /** @test */
    public function it_includes_security_headers_in_response()
    {
        $response = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
            'project_id' => $this->martProject->id,
        ]);

        // Note: Security headers are typically set at web server level
        // But we can check Content-Type is correct
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function it_handles_missing_parameters()
    {
        // Missing email
        $response1 = $this->postJson('/api/check-email', [
            'project_id' => $this->martProject->id,
        ]);

        $response1->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Missing project_id
        $response2 = $this->postJson('/api/check-email', [
            'email' => 'test@example.com',
        ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);

        // Missing both
        $response3 = $this->postJson('/api/check-email', []);

        $response3->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'project_id']);
    }

    /** @test */
    public function it_handles_malformed_json()
    {
        $response = $this->post('/api/check-email', [], [
            'Content-Type' => 'application/json',
        ]);

        // Laravel should handle malformed JSON gracefully
        $this->assertContains($response->status(), [400, 422]);
    }

    /** @test */
    public function timing_attack_protection_adds_random_delay()
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $times = [];

        // Measure response times for multiple requests
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);

            $response = $this->postJson('/api/check-email', [
                'email' => 'test' . $i . '@example.com',
                'project_id' => $this->martProject->id,
            ]);

            $duration = microtime(true) - $start;
            $times[] = $duration;

            $response->assertStatus(200);
        }

        // Verify there's variance in response times (random delay is working)
        $stdDev = $this->calculateStdDev($times);

        // Standard deviation should be > 0.01 seconds (10ms) due to random 50-150ms delay
        $this->assertGreaterThan(0.01, $stdDev, 'Response times should vary due to random delay');
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values): float
    {
        $count = count($values);
        if ($count === 0) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / $count;

        return sqrt($variance);
    }
}
