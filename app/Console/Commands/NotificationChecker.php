<?php

namespace App\Console\Commands;

use App;
use App\Cases;
use App\Notifications\researcherNotificationToUser;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Spatie\WebhookServer\WebhookCall;

class NotificationChecker extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'notifications:check';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'This command check the notification table for recurring notifications and send them.';

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
        $notifications = DB::select('SELECT * FROM notifications WHERE data NOT LIKE ? and data LIKE ?',  ['%"planning":false%', '%planning%']);
        $notificationSent = 0;
        $notificationCheckedButNotSent = 0;
        foreach ($notifications as $notification)
        {
            $notification->data = json_decode($notification->data);
            $case = Cases::where('id', $notification->data->case)->first();
            $caseClosed = ($case->lastDay() !== "" && strtotime($case->lastDay()) < strtotime('now'));
            if ($caseClosed)
            {
                $this->error("Notification NOT sent to " . $case->user->email . " because last day was " . $case->lastDay());
                // delete planned notification not valid anymore
                DB::table('notifications')->where('id', '=', $notification->id)->delete();
                continue;
            }
            $timeIsSameAsInDatabase = date('H:i', strtotime('now')) === date('H:i', strtotime(explode('at ', $notification->data->planning)[1]));
            if ($timeIsSameAsInDatabase)
            {
                if ((str_contains(explode('at ', $notification->data->planning)[0], "Every day")))
                {
                    $differenceBetweenLastNotification = 86400;
                } elseif ((str_contains(explode('at ', $notification->data->planning)[0], "Every two days")))
                {
                    $differenceBetweenLastNotification = 86400 * 2;
                } elseif ((str_contains(explode('at ', $notification->data->planning)[0], "Every three days")))
                {
                    $differenceBetweenLastNotification = 86400 * 3;
                } else
                {
                    $this->error("Something went wrong while parsing the frequency of Notification " . $notification->id);
                    return 0;
                }
                $timefromdatabase = strtotime($case->user->profile->last_notification_at);
                $dif = time() - $timefromdatabase;
                $enoughTimeHasPassed = $dif >= $differenceBetweenLastNotification;
                if ($enoughTimeHasPassed)
                {
                    $this->sendNotification($case, $notification, $notificationSent);
                } else
                {
                    $notificationCheckedButNotSent++;
                    $this->info("notification already sent last " . ($differenceBetweenLastNotification / 60 / 60) . "h");
                }
            }
        }

        if (count($notifications) === 0) $this->warn("0 planned notifications in the database");
        else $this->info($notificationSent . " notification(s) sent.");

        if(config('utilities.notificationcommandtochat') === 1){
            WebhookCall::create()
            ->url(config('utilities.url_rc_notifications'))
            ->payload(['text' => 'Command executed and  ' . $notificationSent . ' Notifications sent. "'])
            ->useSecret(config('utilities.secret_rc_notifications'))
            ->dispatch();
        }

        if($notificationCheckedButNotSent > 0)
        {
             if(!App::environment('local')){
            WebhookCall::create()
            ->url(config('utilities.url_rc_notifications'))
            ->payload(['text' => 'Command executed and  ' . $notificationCheckedButNotSent . ' Notifications checked but not sent because time was not passed.'])
            ->useSecret(config('utilities.secret_rc_notifications'))
            ->dispatch();
        }
        }



        return 0;
    }

    /**
     * @param object|Builder|Cases|null $case
     * @param mixed                     $notification
     * @param int                       $notificationSent
     */
    private function sendNotification(Cases|null $case, mixed $notification, int &$notificationSent): void
    {
        $user = $case->user;
        $user->profile->last_notification_at = date("Y-m-d H:i:s");
        $user->profile->save();
        $user->notify(new researcherNotificationToUser(['title' => $notification->data->title, 'message' => $notification->data->message, 'case' => $case]));
        $this->info("Notification sent to " . $user->email);
        $notificationSent++;
    }
}
