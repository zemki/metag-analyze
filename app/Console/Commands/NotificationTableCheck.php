<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class NotificationTableCheck extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'notifications:table';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Check the notifications table';

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

        $notificationType = $this->choice(
            'Planned or all?',
            ['All', 'Planned'],
            0
        );
        $notificationTime = $this->choice(
            'Last 24h-36h-48h or anytime?',
            ['24', '48', '72', 'anytime'],
            0
        );
        $notificationTimeArray = ["24" => 'WHERE created_at >= NOW() - INTERVAL 1 DAY', "48" => 'WHERE created_at >= NOW() - INTERVAL 2 DAY', "72" => 'WHERE created_at >= NOW() - INTERVAL 3 DAY', "anytime" => ''];
        $headers = ['data', 'notifiable_id', 'created_at'];
        if ($notificationType === "All")
        {
            $mainNotifications = DB::select('SELECT data,notifiable_id,created_at FROM notifications ' . $notificationTimeArray[$notificationTime]);
        } elseif ($notificationType === "Planned")
        {
            $stringForSql = str_replace('WHERE','and',$notificationTimeArray[$notificationTime]);
            $mainNotifications = DB::select('SELECT data,notifiable_id,created_at FROM notifications WHERE data NOT LIKE ? and data LIKE ? ' . $stringForSql, ['%"planning":false%', '%planning%']);
        }
        $printNotifications = [];
        foreach ($mainNotifications as $notification)
        {
            array_push($printNotifications, (array)$notification);
        }
        $this->table($headers, $printNotifications);
        return 1;
    }
}
