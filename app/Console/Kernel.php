<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Dispatched as console commands, not via ->job(). Both classes extend
        // Illuminate\Console\Command, and ->job() would push them through the bus,
        // invoking handle() on an instance whose $output was never set - every
        // $this->info()/warn()/error() call then dies with "Call to a member
        // function writeln() on null". NotificationChecker::handle() always
        // reaches one of those, so the every-minute run failed on every tick and
        // participant notifications were never delivered.
        $schedule->command('notifications:check')->everyMinute()->withoutOverlapping();
        $schedule->command('notification:cleanID')->dailyAt('01:30');
        $schedule->command('telescope:prune', ['--hours' => 168])->dailyAt('03:17');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
