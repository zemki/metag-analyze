<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use JetBrains\PhpStorm\ArrayShape;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;

class researcherNotificationToUser extends Notification implements ShouldQueue
{
    use Queueable;

    private mixed $title;

    private mixed $message;

    private mixed $cases;

    private mixed $planning = false;

    private mixed $questionnaireId = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->title = $details['title'];
        $this->message = $details['message'];
        $this->cases = $details['case'];
        $this->planning = $details['planning'] ?? false;
        $this->questionnaireId = $details['questionnaire_id'] ?? null;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->planning !== false) {
            return ['database'];
        } else {
            return [FcmChannel::class, 'database'];
        }

    }

    public function toFcm($notifiable): FcmMessage
    {

        return FcmMessage::create()
            ->setNotification(\NotificationChannels\Fcm\Resources\Notification::create()
                ->setTitle($this->title)
                ->setBody($this->message)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setFcmOptions(AndroidFcmOptions::create()->setAnalyticsLabel('analytics_android'))
                    ->setNotification(AndroidNotification::create()->setColor('#0A0A0A')
                        ->setSound('default'))
            )->setApns(
                ApnsConfig::create()
                    ->setFcmOptions(ApnsFcmOptions::create()->setAnalyticsLabel('analytics_ios'))->setPayload(['aps' => ['sound' => 'default']])
            );

    }

    /**
     * Get the array representation of the notification.
     */
    #[ArrayShape(['title' => 'string', 'message' => 'string', 'case' => 'mixed', 'planning' => 'mixed', 'questionnaire_id' => 'mixed'])]
    public function toArray(): array
    {
        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'case' => $this->cases['id'],
            'planning' => $this->planning,
        ];
        
        // Include questionnaire_id if available
        if ($this->questionnaireId !== null) {
            $data['questionnaire_id'] = $this->questionnaireId;
        }
        
        return $data;
    }
}
