<?php

namespace Tests\Feature;

use App\Http\Middleware\DocsBasicAuth;
use Illuminate\Http\Request;
use Tests\TestCase;

class DocsBasicAuthTest extends TestCase
{
    private function handle(array $server = []): int
    {
        $request = Request::create('/docs/api', 'GET', [], [], [], $server);

        return (new DocsBasicAuth)
            ->handle($request, fn () => response('docs'))
            ->getStatusCode();
    }

    /** @test */
    public function docs_ask_for_credentials_by_default()
    {
        config(['scramble.docs_password' => 'secret']);

        $this->assertSame(401, $this->handle());
    }

    /** @test */
    public function docs_open_with_the_right_credentials()
    {
        config(['scramble.docs_user' => 'docs', 'scramble.docs_password' => 'secret']);

        $this->assertSame(200, $this->handle(['PHP_AUTH_USER' => 'docs', 'PHP_AUTH_PW' => 'secret']));
    }

    /** @test */
    public function docs_fail_closed_without_a_password()
    {
        config(['scramble.docs_password' => null]);

        $this->assertSame(503, $this->handle());
    }

    /** @test */
    public function docs_are_open_when_protection_is_turned_off()
    {
        config(['scramble.docs_protect_access' => false, 'scramble.docs_password' => null]);

        $this->assertSame(200, $this->handle());
    }
}
