<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The MeTag routes in routes/api.php sit in the `api` group, whose
 * throttle:120,1 shared one counter with each route's own throttle (an unnamed
 * throttle keys by user or IP only). Every request counted twice, and the
 * group's one-minute expiry also cut longer windows down to a minute.
 */
class MetagRateLimitTest extends TestCase
{
    private function statusAfter(int $requests, string $uri, array $body = []): int
    {
        $status = 0;
        for ($i = 0; $i < $requests; $i++) {
            $status = $this->postJson($uri, $body)->getStatusCode();
        }

        return $status;
    }

    private function wrongLogin(): array
    {
        return ['email' => 'nobody@example.com', 'password' => 'wrong', 'datetime' => time()];
    }

    /** @test */
    public function login_allows_its_documented_30_attempts()
    {
        $this->assertNotSame(429, $this->statusAfter(30, '/api/login', $this->wrongLogin()));
    }

    /** @test */
    public function login_stops_at_the_31st_attempt()
    {
        $this->assertSame(429, $this->statusAfter(31, '/api/login', $this->wrongLogin()));
    }

    /** @test */
    public function the_password_setup_limit_lasts_ten_minutes()
    {
        $this->statusAfter(10, '/api/send-password-setup');

        $this->travel(2)->minutes();

        $this->assertSame(429, $this->postJson('/api/send-password-setup', [])->getStatusCode());
    }

    /** @test */
    public function qr_login_allows_its_documented_30_requests()
    {
        $this->assertNotSame(429, $this->statusAfter(30, '/api/qr-login'));
    }

    /** @test */
    public function check_email_allows_its_documented_30_requests()
    {
        $this->assertNotSame(429, $this->statusAfter(30, '/api/check-email'));
    }

    /** @test */
    public function send_password_setup_allows_its_documented_10_requests()
    {
        $this->assertNotSame(429, $this->statusAfter(10, '/api/send-password-setup'));
    }
}
