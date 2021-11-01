<?php

namespace App\Console;

use App\Console\Commands\NotificationChecker;
use App\Console\Commands\NotificationCleanDeviceID;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new NotificationChecker())->everyMinute();
        $schedule->job(new NotificationCleanDeviceID())->dailyAt('01:30');
    }

    /**
     * Register the commands for the application.
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
