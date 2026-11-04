<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    private function isSecureBehindProxy(string $trustedProxies, string $remoteAddr = '10.0.0.5'): bool
    {
        config(['utilities.trustedProxies' => $trustedProxies]);

        $request = Request::create('http://example.test/', 'GET', [], [], [], [
            'REMOTE_ADDR' => $remoteAddr,
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        return (new TrustProxies)->handle($request, fn (Request $r) => response($r->isSecure() ? 'https' : 'http'))
            ->getContent() === 'https';
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies([], -1);

        parent::tearDown();
    }

    /** @test */
    public function no_proxy_is_trusted_when_the_setting_is_empty()
    {
        $this->assertFalse($this->isSecureBehindProxy(''));
    }

    /** @test */
    public function a_listed_proxy_is_trusted()
    {
        $this->assertTrue($this->isSecureBehindProxy('192.168.1.1, 10.0.0.5'));
    }

    /** @test */
    public function an_unlisted_proxy_is_not_trusted()
    {
        $this->assertFalse($this->isSecureBehindProxy('192.168.1.1'));
    }

    /** @test */
    public function the_wildcard_trusts_any_proxy()
    {
        $this->assertTrue($this->isSecureBehindProxy('*'));
    }
}
