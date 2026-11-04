<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Pins the MART app's URL surface.
 *
 * Everything the app talks to lives under `/api/mobile`, with `auth/` marking
 * the endpoints that do not need a Bearer token. The older `/api/mart/*` and
 * `/mart-api/*` URLs must keep answering until no app version in use calls them.
 */
class MobileApiRoutingTest extends TestCase
{
    private function registeredRoutes(): array
    {
        $registered = [];
        foreach (Route::getRoutes() as $route) {
            foreach ($route->methods() as $method) {
                $registered[] = $method . ' ' . $route->uri();
            }
        }

        return $registered;
    }

    /** @test */
    public function every_mobile_endpoint_lives_under_api_mobile()
    {
        $expected = [
            'POST api/mobile/auth/check-email',
            'POST api/mobile/auth/send-password-setup',
            'POST api/mobile/auth/check-password',
            'POST api/mobile/auth/check-access',
            'POST api/mobile/auth/refresh',
            'GET api/mobile/projects/{project}/structure',
            'POST api/mobile/cases/{case}/submit',
            'POST api/mobile/cases/{case}/files',
            'GET api/mobile/files/{martFileId}',
            'DELETE api/mobile/files/{martFileId}',
            'POST api/mobile/device-infos',
            'POST api/mobile/device-token',
            'POST api/mobile/stats',
        ];

        $registered = $this->registeredRoutes();

        foreach ($expected as $signature) {
            $this->assertContains($signature, $registered, "Missing mobile route: {$signature}");
        }
    }

    /** @test */
    public function the_old_urls_still_answer()
    {
        $expected = [
            'POST api/mart/check-email',
            'POST api/mart/check-password',
            'POST api/mart/check-access',
            'POST api/mart/refresh',
            'GET mart-api/projects/{project}/structure',
            'POST mart-api/cases/{case}/submit',
            'POST mart-api/device-infos',
            'POST mart-api/device-token',
            'POST mart-api/stats',
        ];

        $registered = $this->registeredRoutes();

        foreach ($expected as $signature) {
            $this->assertContains($signature, $registered, "Missing alias route: {$signature}");
        }
    }

    /** @test */
    public function auth_endpoints_are_unauthenticated_and_data_endpoints_are_not()
    {
        $middlewareFor = function (string $uri) {
            foreach (Route::getRoutes() as $route) {
                if ($route->uri() === $uri) {
                    return $route->gatherMiddleware();
                }
            }

            $this->fail("No route registered for {$uri}");
        };

        // The 3-screen flow proves who you are; it cannot require a token.
        $this->assertNotContains('auth:api', $middlewareFor('api/mobile/auth/check-email'));

        // Everything else must.
        foreach (['api/mobile/projects/{project}/structure', 'api/mobile/device-token', 'api/mobile/stats'] as $uri) {
            $this->assertContains('auth:api', $middlewareFor($uri));
        }
    }

    /** @test */
    public function the_mobile_file_route_has_a_name()
    {
        $this->assertSame(
            '/api/mobile/files/abc-123',
            route('mobile.files.show', 'abc-123', false)
        );
    }
}
