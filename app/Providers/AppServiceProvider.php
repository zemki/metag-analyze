<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        View::composer(['telescope::layout'], function ($view) {
            $view->with('telescopeScriptVariables', ['path' => 'metag/telescope', 'timezone' => config('app.timezone'), 'recording' => !cache('telescope:pause-recording')]);
        });
    }

    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }
}
