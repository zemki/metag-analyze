<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class NotificationCleanDeviceID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:cleanID';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove device ID for users that are not in active cases';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $usersCleaned = 0;
        foreach (User::all() as $user) {
            if ($user->latestCase && $user->latestCase->isConsultable()) {
                if ($user->deviceID != []) {
                    $usersCleaned++;
                }
                $user->deviceID = [];
                $user->save();
            }
        }

        return 0;
    }
}
