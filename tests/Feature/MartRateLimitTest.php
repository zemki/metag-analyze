<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * An unnamed `throttle:N,M` keys its counter by user or IP only, so the `api`
 * group's throttle and a route's own throttle used to share one counter and
 * every request counted twice: MART data routes were capped at 60/min instead
 * of the documented 300, and the /api/mart auth routes at 15/min instead of 30.
 */
class MartRateLimitTest extends TestCase
{
    private string $rawToken = 'rate_limit_test_1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->forceFill(['api_token' => hash('sha256', $this->rawToken)])->save();
    }

    private function statusAfter(int $requests, callable $send): int
    {
        $status = 0;
        for ($i = 0; $i < $requests; $i++) {
            $status = $send()->getStatusCode();
        }

        return $status;
    }

    /** @test */
    public function data_routes_allow_more_than_120_requests_a_minute()
    {
        $status = $this->statusAfter(121, fn () => $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-token', ['token' => 'fcm-token']));

        $this->assertSame(200, $status);
    }

    /** @test */
    public function the_old_data_url_allows_more_than_120_requests_a_minute()
    {
        $status = $this->statusAfter(121, fn () => $this->withToken($this->rawToken)
            ->postJson('/mart-api/device-token', ['token' => 'fcm-token']));

        $this->assertSame(200, $status);
    }

    /** @test */
    public function the_old_auth_urls_allow_their_documented_30_requests()
    {
        // Empty bodies fail validation (422); only the throttle answers 429.
        $status = $this->statusAfter(30, fn () => $this->postJson('/api/mart/check-email', []));

        $this->assertSame(422, $status);
    }

    /** @test */
    public function the_old_auth_urls_still_stop_at_31()
    {
        $status = $this->statusAfter(31, fn () => $this->postJson('/api/mart/check-email', []));

        $this->assertSame(429, $status);
    }

    /**
     * The flow calls check-email first. If both routes shared a counter, its
     * one-minute expiry would also end password-setup's ten-minute window.
     *
     * @test
     */
    public function the_password_setup_limit_lasts_ten_minutes_after_check_email()
    {
        $this->postJson('/api/mobile/auth/check-email', []);
        $this->statusAfter(30, fn () => $this->postJson('/api/mobile/auth/send-password-setup', []));

        $this->travel(2)->minutes();

        $this->assertSame(429, $this->postJson('/api/mobile/auth/send-password-setup', [])->getStatusCode());
    }

    /** @test */
    public function the_mobile_auth_routes_allow_their_documented_30_requests()
    {
        $this->postJson('/api/mobile/auth/send-password-setup', []);

        $status = $this->statusAfter(30, fn () => $this->postJson('/api/mobile/auth/check-email', []));

        $this->assertSame(422, $status);
    }
}
