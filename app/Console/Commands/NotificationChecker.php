<?php

namespace App\Console\Commands;

use App\Cases;
use App\Notifications\researcherNotificationToUser;
use App\User;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class NotificationChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command check the notification table for recurring notifications and send them.';

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
        $notifications = DB::select('SELECT *  FROM notifications WHERE data NOT LIKE ? and data LIKE ?', ['%"planning":false%','%planning%']);
        $notificationSent = 0;
        foreach ($notifications as $notification)
        {
            $notification->data = json_decode($notification->data);
            $case = Cases::where('id',$notification->data->case)->first();

            $caseClosed = ($case->lastDay() !== "" && strtotime($case->lastDay()) < strtotime('now'));
            if($caseClosed){
                $this->error("Notification NOT sent to ".$case->user->email." because last day was ".$case->lastDay());
                // delete planned notification not valid anymore
                DB::table('notifications')->where('id', '=', $notification->id)->delete();
                continue;
            }

            $timeIsSameAsInDatabase = date('H:i', strtotime('now')) === date('H:i', strtotime(explode('at ', $notification->data->planning)[1]));
            if($timeIsSameAsInDatabase)
            {
                if((str_contains(explode('at ', $notification->data->planning)[0],"Every day"))){
                    $time = 86400;
                }elseif ((str_contains(explode('at ', $notification->data->planning)[0],"Every two days"))){
                    $time = 86400 * 2;
                }elseif ((str_contains(explode('at ', $notification->data->planning)[0],"Every three days"))){
                    $time = 86400 * 3;
                }else{
                    $this->error("Something went wrong while parsing the frequency of Notification ".$notification->id);
                    return 0;
                }


                // I'm using a deltaTime because I'm not sure if checking exactly X hours before returns an error.
                // probably is worse though. It will send notification 10 minutes earlier every day.
                $notificationAlreadySentRecently = $case->user->profile->last_notification_at >= (time() - $time);
                if($notificationAlreadySentRecently){
                    $this->info("notification already sent last ".($time/60/60)."h");
                }else $this->sendNotification($case, $notification, $notificationSent);


            }
        }

        if(count($notifications) === 0) $this->warn("0 planned notifications in the database");
        else $this->info($notificationSent." notification(s) sent.");

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
