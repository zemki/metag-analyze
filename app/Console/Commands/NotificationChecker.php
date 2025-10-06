<?php

namespace App\Console\Commands;

use App\Cases;
use App\Entry;
use App\Mart\MartProject;
use App\Mart\MartSchedule;
use App\MartQuestionnaireSchedule;
use App\Notifications\researcherNotificationToUser;
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
        $notifications = DB::select('SELECT * FROM notifications WHERE data NOT LIKE ? and data LIKE ?', ['%"planning":false%', '%planning%']);
        $notificationSent = 0;
        $notificationCheckedButNotSent = 0;
        foreach ($notifications as $notification) {
            $notification->data = json_decode($notification->data);
            $case = Cases::where('id', $notification->data->case)->first();
            $caseClosed = ($case->lastDay() !== '' && strtotime($case->lastDay()) < strtotime('now'));
            if ($caseClosed) {
                $this->error('Notification NOT sent to ' . $case->user->email . ' because last day was ' . $case->lastDay());
                // delete planned notification not valid anymore
                DB::table('notifications')->where('id', '=', $notification->id)->delete();

                continue;
            }
            $timeIsSameAsInDatabase = date('H:i', strtotime('now')) === date('H:i', strtotime(explode('at ', $notification->data->planning)[1]));
            if ($timeIsSameAsInDatabase) {
                if ((str_contains(explode('at ', $notification->data->planning)[0], 'Every day'))) {
                    $differenceBetweenLastNotification = 86400;
                } elseif ((str_contains(explode('at ', $notification->data->planning)[0], 'Every two days'))) {
                    $differenceBetweenLastNotification = 86400 * 2;
                } elseif ((str_contains(explode('at ', $notification->data->planning)[0], 'Every three days'))) {
                    $differenceBetweenLastNotification = 86400 * 3;
                } else {
                    $this->error('Something went wrong while parsing the frequency of Notification ' . $notification->id);

                    return 0;
                }
                $timeFromDatabase = strtotime($case->user->profile->last_notification_at);
                $nowMinusTimeFromDatabase = time() - $timeFromDatabase;
                $acceptanceWindow = 10;
                // Enhanced logic for questionnaire-aware scheduling
                if (isset($notification->data->questionnaire_id)) {
                    $shouldSend = $this->shouldSendQuestionnaireNotification($case, $notification);
                    if ($shouldSend) {
                        $this->sendNotification($case, $notification, $notificationSent);
                    } else {
                        $notificationCheckedButNotSent++;
                    }
                } else {
                    // Legacy logic for backward compatibility
                    $enoughTimeHasPassed = $nowMinusTimeFromDatabase + $acceptanceWindow >= $differenceBetweenLastNotification;
                    if ($enoughTimeHasPassed) {
                        $this->sendNotification($case, $notification, $notificationSent);
                    } else {
                        $notificationCheckedButNotSent++;
                        $this->info('notification already sent last ' . ($differenceBetweenLastNotification / 60 / 60) . 'h');
                    }
                }
            }
        }

        if (count($notifications) === 0) {
            $this->warn('0 planned notifications in the database');
        } else {
            $this->info($notificationSent . ' notification(s) sent.');
        }

        return 0;
    }

    /**
     * Check if questionnaire notification should be sent based on schedule rules
     */
    private function shouldSendQuestionnaireNotification(Cases $case, $notification): bool
    {
        $questionnaireId = $notification->data->questionnaire_id;

        // Get MART project and schedule from MART database
        $martProject = $case->project->martProject();
        if (! $martProject) {
            return false; // No MART project, don't send
        }

        $schedule = MartSchedule::forProject($martProject->id)
            ->where('questionnaire_id', $questionnaireId)
            ->first();

        if (! $schedule) {
            return false; // No schedule found, don't send
        }

        // Check if we're within the daily time window
        $currentTime = date('H:i');
        $startTime = $schedule->timing_config['daily_start_time'] ?? '00:00';
        $endTime = $schedule->timing_config['daily_end_time'] ?? '23:59';

        if ($currentTime < $startTime || $currentTime > $endTime) {
            return false; // Outside daily window
        }

        // Check daily submission limits for repeating questionnaires
        $maxDailySubmits = $schedule->timing_config['max_daily_submits'] ?? null;
        if ($schedule->type === 'repeating' && $maxDailySubmits) {
            $todayStart = date('Y-m-d 00:00:00');
            $todayEntries = Entry::where('case_id', $case->id)
                ->where('created_at', '>=', $todayStart)
                ->whereJsonContains('inputs->_mart_metadata->questionnaire_id', $questionnaireId)
                ->count();

            if ($todayEntries >= $maxDailySubmits) {
                return false; // Daily limit reached
            }
        }

        // Check minimum break between questionnaires
        $minBreakBetween = $schedule->timing_config['min_break_between'] ?? null;
        if ($minBreakBetween && $case->user->profile->last_notification_at) {
            $lastNotificationTime = strtotime($case->user->profile->last_notification_at);
            $minBreakSeconds = $minBreakBetween * 60; // Convert minutes to seconds
            $timeSinceLastNotification = time() - $lastNotificationTime;

            if ($timeSinceLastNotification < $minBreakSeconds) {
                return false; // Not enough time passed since last notification
            }
        }

        return true; // All checks passed
    }

    /**
     * @param  object|Builder|Cases|null  $case
     */
    private function sendNotification(?Cases $case, mixed $notification, int &$notificationSent): void
    {
        $user = $case->user;
        $user->profile->last_notification_at = date('Y-m-d H:i:s');
        $user->profile->save();

        // Include questionnaire_id in notification if available
        $notificationData = [
            'title' => $notification->data->title,
            'message' => $notification->data->message,
            'case' => $case,
        ];

        if (isset($notification->data->questionnaire_id)) {
            $notificationData['questionnaire_id'] = $notification->data->questionnaire_id;
        }

        $user->notify(new researcherNotificationToUser($notificationData));
        $this->info('Notification sent to ' . $user->email . (isset($notification->data->questionnaire_id) ? " (questionnaire: {$notification->data->questionnaire_id})" : ''));
        $notificationSent++;
    }
}
