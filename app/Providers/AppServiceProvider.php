<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultStringLength(191);

        // Provide central config values to all views
        View::composer('*', function ($view) {
            $view->with([
                'appVersion' => config('app.version'),
                'appBuild'   => config('app.build'),
                'appName'   => config('app.name'),
                'appOwner' => config('app.owner'),
            ]);
        });

        // Scramble: document the MART app routes. The /api/mart and /mart-api
        // aliases point at the same controllers, so they would only repeat them.
        Scramble::configure()
            ->routes(function (\Illuminate\Routing\Route $route) {
                return Str::startsWith($route->uri, 'api/mobile/');
            })
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
            });

        // Configure Livewire routes for subdirectory deployment (production only)
        if (App::environment('production')) {
            Livewire::setScriptRoute(function ($handle) {
                return Route::get('/metag/livewire/livewire.min.js', $handle)
                    ->middleware(['web']);
            });

            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/metag/livewire/update', $handle)
                    ->middleware(['web']);
            });
        }

        if (App::environment('local')) {
            // The environment is local
            View::composer(['telescope::layout'], function ($view) {
                $view->with('telescopeScriptVariables', ['path' => 'telescope', 'timezone' => config('app.timezone'), 'recording' => ! cache('telescope:pause-recording')]);
            });
        } else {
            View::composer(['telescope::layout'], function ($view) {
                $view->with('telescopeScriptVariables', ['path' => 'metag/telescope', 'timezone' => config('app.timezone'), 'recording' => ! cache('telescope:pause-recording')]);
            });
        }

        // Have an app version/build and make it visible in `php artisan about`
        AboutCommand::add('Environment', [
            'App Version' => config('app.version'),
            'App Build' => config('app.build'),
        ]);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }
}
