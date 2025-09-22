<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapMartApiRoutes();
        //
    }

    /**
     * Define the "api" routes for the application.
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));

    }

    /**
     * Define the "web" routes for the application.
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "mart-api" routes for the application.
     * These routes are for the MART mobile application integration.
     *
     * @return void
     */
    protected function mapMartApiRoutes()
    {
        Route::prefix('mart-api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/mart_api.php'));
    }

    protected function configureRateLimiting()
    {
        $this->rateLimiter()->for('login', function ($request) {
            return Limit::perMinutes(2, 1)->by($request->ip())->response(function () {
                return response('Too many attempts. Try again after ' . $this->rateLimiter()->availableIn, 429);
            });
        });
    }
}
