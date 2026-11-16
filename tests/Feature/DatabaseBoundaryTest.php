<?php

namespace Tests\Feature;

use App\Exceptions\DatabaseBoundaryViolation;
use App\Http\Middleware\DatabaseBoundary;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * MeTag API routes must not reach the MART database. MART data routes still
 * read `users`, `projects` and `cases`, which is accepted, so anything else
 * they touch in the main database gets reported.
 */
class DatabaseBoundaryTest extends TestCase
{
    private const TOKEN = 'database_boundary_test_token';

    /** MART data that sits under /api/v2 and has no known caller. */
    private const PAGES_ROUTES = [
        'GET|HEAD api/v2/projects/{project}/pages',
        'POST api/v2/projects/{project}/pages',
        'GET|HEAD api/v2/projects/{project}/pages/{page}',
        'PATCH api/v2/projects/{project}/pages/{page}',
        'DELETE api/v2/projects/{project}/pages/{page}',
        'PATCH api/v2/projects/{project}/pages/order',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->forceFill(['api_token' => hash('sha256', self::TOKEN)])->save();
    }

    private function routeLabel(RoutingRoute $route): string
    {
        return implode('|', $route->methods()) . ' ' . $route->uri();
    }

    /** @return RoutingRoute[] */
    private function routesMatching(string $pattern): array
    {
        return array_filter(
            Route::getRoutes()->getRoutes(),
            fn (RoutingRoute $route) => preg_match($pattern, $route->uri())
        );
    }

    /**
     * Auth and route model binding query the database, so the boundary has to
     * be up first. The throttles before it use the cache, not the database.
     */
    private function assertBoundaryRunsBeforeDatabaseWork(RoutingRoute $route, string $side): void
    {
        $middleware = app(Router::class)->gatherRouteMiddleware($route);
        $boundary = array_search(DatabaseBoundary::class . ":{$side}", $middleware, true);

        $this->assertNotFalse($boundary, "{$this->routeLabel($route)} has no boundary.");

        foreach ($middleware as $position => $name) {
            if (str_contains($name, 'Authenticate') || str_contains($name, 'SubstituteBindings')) {
                $this->assertLessThan($position, $boundary, "{$this->routeLabel($route)}: {$name} runs first.");
            }
        }
    }

    private function probe(string $side, \Closure $action): string
    {
        Route::get('/_boundary_probe', $action)->middleware("boundary:{$side}");

        return '/_boundary_probe';
    }

    /** @test */
    public function every_metag_api_route_has_the_metag_boundary()
    {
        $routes = $this->routesMatching('#^api/(login|qr-login|v1/|v2/)#');
        $routes = array_filter($routes, fn ($route) => ! in_array($this->routeLabel($route), self::PAGES_ROUTES, true));

        $this->assertNotEmpty($routes);
        foreach ($routes as $route) {
            $this->assertBoundaryRunsBeforeDatabaseWork($route, 'metag');
        }
    }

    /** @test */
    public function every_mart_route_has_the_mart_boundary()
    {
        $routes = $this->routesMatching('#^(api/mobile/|api/mart/|mart-api/)#');

        $this->assertNotEmpty($routes);
        foreach ($routes as $route) {
            $this->assertBoundaryRunsBeforeDatabaseWork($route, 'mart');
        }
    }

    /** @test */
    public function the_six_pages_routes_stay_outside_the_boundary()
    {
        $routes = $this->routesMatching('#^api/v2/projects/\{project\}/pages#');

        $this->assertEqualsCanonicalizing(self::PAGES_ROUTES, array_map(fn ($route) => $this->routeLabel($route), array_values($routes)));

        foreach ($routes as $route) {
            $this->assertStringNotContainsString(DatabaseBoundary::class, implode(' ', app(Router::class)->gatherRouteMiddleware($route)));
        }
    }

    /** @test */
    public function a_metag_route_refuses_a_mart_query()
    {
        $url = $this->probe('metag', fn () => MartProject::count());

        $this->withoutExceptionHandling();
        $this->expectException(DatabaseBoundaryViolation::class);

        $this->get($url);
    }

    /** @test */
    public function a_metag_route_refuses_a_mart_transaction_before_it_starts()
    {
        $url = $this->probe('metag', fn () => DB::connection('mart')->beginTransaction());

        $this->withoutExceptionHandling();

        try {
            $this->get($url);
            $this->fail('The transaction was not refused.');
        } catch (DatabaseBoundaryViolation) {
            $this->assertSame(0, DB::connection('mart')->transactionLevel());
        }
    }

    /** @test */
    public function a_metag_route_only_logs_when_enforcement_is_off()
    {
        config(['database.metag_boundary_enforced' => false]);
        Log::spy();
        $url = $this->probe('metag', fn () => MartProject::count());

        $this->get($url)->assertOk();

        Log::shouldHaveReceived('error')->once()->withArgs(function (string $message, array $context) {
            return $context['tables'] === ['mart_projects'];
        });
    }

    /** @test */
    public function enforcement_is_off_in_production_until_it_is_switched_on()
    {
        $default = fn (string $environment) => $this->enforcementDefault($environment);

        $this->assertFalse($default('production'));
        $this->assertTrue($default('local'));
        $this->assertTrue($default('testing'));
    }

    private function enforcementDefault(string $environment): bool
    {
        $previous = $_ENV['APP_ENV'] ?? null;
        $serverPrevious = $_SERVER['APP_ENV'] ?? null;
        $_ENV['APP_ENV'] = $environment;
        $_SERVER['APP_ENV'] = $environment;

        try {
            return (require config_path('database.php'))['metag_boundary_enforced'];
        } finally {
            $_ENV['APP_ENV'] = $previous;
            $_SERVER['APP_ENV'] = $serverPrevious;
        }
    }

    /** @test */
    public function a_mart_route_reports_main_tables_beyond_the_expected_ones()
    {
        Log::spy();
        $url = $this->probe('mart', fn () => DB::table('settings')->count());

        $this->get($url)->assertOk();

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context) {
            return $context['tables'] === ['settings'];
        });
    }

    /** @test */
    public function a_mart_route_reports_a_main_transaction()
    {
        Log::spy();
        $url = $this->probe('mart', function () {
            DB::connection('mysql')->beginTransaction();
            DB::connection('mysql')->rollBack();
        });

        $this->get($url)->assertOk();

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context) {
            return $context['tables'] === ['(transaction)'];
        });
    }

    /** @test */
    public function a_mart_route_stays_quiet_on_the_expected_tables()
    {
        Log::spy();
        $url = $this->probe('mart', function () {
            DB::table('users')->count();
            DB::table('projects')->count();
            DB::table('cases')->count();
        });

        $this->get($url)->assertOk();

        Log::shouldNotHaveReceived('warning');
    }

    /** @test */
    public function the_report_holds_no_sql_values()
    {
        Log::spy();
        $url = $this->probe('mart', fn () => DB::table('settings')->where('key', 'participant@example.com')->count());

        $this->get($url)->assertOk();

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context) {
            return ! str_contains($message . json_encode($context), 'participant@example.com');
        });
    }

    /** @test */
    public function the_boundary_ends_with_its_request()
    {
        Route::get('/_boundary_open', fn () => MartProject::count());
        $url = $this->probe('metag', fn () => 'no MART here');

        $this->get($url)->assertOk();

        $this->get('/_boundary_open')->assertOk();
    }

    /** @test */
    public function metag_api_requests_pass_the_boundary()
    {
        $token = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'datetime' => time(),
        ])->assertOk()->json('token');

        foreach (['/api/v1', '/api/v2'] as $prefix) {
            $this->withToken($token)->getJson("{$prefix}/entry/{$this->case->id}")->assertOk();
            $this->withToken($token)->getJson("{$prefix}/inputs/{$this->project->id}")->assertOk();
        }
    }

    /** @test */
    public function a_mart_submit_touches_no_unexpected_main_table()
    {
        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
        $schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + 1,
            'name' => 'Daily check-in',
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);
        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 1,
            'text' => 'How was your day?',
            'type' => 'text',
            'is_mandatory' => false,
            'version' => 1,
        ]);
        Log::spy();

        $this->withToken(self::TOKEN)
            ->postJson("/api/mobile/cases/{$this->case->id}/submit", [
                'projectId' => $this->project->id,
                'questionnaireId' => $schedule->questionnaire_id,
                'userId' => $this->user->email,
                'participantId' => $this->case->name,
                'questionnaireStarted' => now()->timestamp * 1000,
                'questionnaireDuration' => 1000,
                'answers' => ['1' => 'fine'],
                'timestamp' => now()->timestamp * 1000,
                'timezone' => 'Europe/Berlin',
            ])
            ->assertOk();

        Log::shouldNotHaveReceived('warning');
    }
}
