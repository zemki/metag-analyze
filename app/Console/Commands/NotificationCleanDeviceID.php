<?php

namespace App\Console\Commands;

use App;
use App\User;
use Illuminate\Console\Command;
use Spatie\WebhookServer\WebhookCall;

class NotificationCleanDeviceID extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'notification:cleanID';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Remove device ID for users that are not in active cases';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $usersCleaned = 0;
        foreach (User::all() as $user)
        {
            if ($user->latestCase && $user->latestCase->isConsultable())
            {
                if ($user->deviceID != []) $usersCleaned++;
                $user->deviceID = [];
                $user->save();
            }
        }
        $this->info($usersCleaned . " deviceID cleaned because users where in an expired case.");
        if (!App::environment('local'))
        {
            if ($usersCleaned > 0)
            {
                WebhookCall::create()
                    ->url(config('utilities.url_rc_registration'))
                    ->payload(['text' => $usersCleaned . " deviceID cleaned because users where in an expired case."])
                    ->useSecret(config('utilities.secret_rc_notifications'))
                    ->dispatch();
            } else
            {
                WebhookCall::create()
                    ->url(config('utilities.url_rc_registration'))
                    ->payload(['text' => "No deviceID cleaned."])
                    ->useSecret(config('utilities.secret_rc_notifications'))
                    ->dispatch();
            }
        }
        return 0;
    }
}
