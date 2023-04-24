<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('testing')) {
            return;
        }

        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if (config('telescope.key', false) || $this->app->environment('local')) { // Updated line
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

       /**
        * Prevent sensitive request details from being logged by Telescope.
        *
        * @return void
        */
       protected function hideSensitiveRequestDetails()
       {
           if ($this->app->environment('local')) {
               return;
           }

           Telescope::hideRequestParameters(['_token']);

           Telescope::hideRequestHeaders([
               'cookie',
               'x-csrf-token',
               'x-xsrf-token',
           ]);
       }

       /**
        * Register the Telescope gate.
        *
        * This gate determines who can access Telescope in non-local environments.
        *
        * @return void
        */
       protected function gate()
       {
           Gate::define('viewTelescope', function ($user) {
               return in_array($user->email, [
                   'belli@uni-bremen.de',
                   'fhohmann@uni-bremen.de',
                   'alessandrobelli90@gmail.com',
               ]);
           });
       }

       /**
        * Configure the Telescope authorization services.
        *
        * @return void
        */
       protected function authorization()
       {
           $this->gate();

           Telescope::auth(function ($request) {
               return Gate::check('viewTelescope', [$request->user()]);
           });
       }
}
