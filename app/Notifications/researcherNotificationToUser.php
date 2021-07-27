<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use JetBrains\PhpStorm\ArrayShape;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidFcmOptions;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\ApnsConfig;
use NotificationChannels\Fcm\Resources\ApnsFcmOptions;
use Spatie\WebhookServer\WebhookCall;

class researcherNotificationToUser extends Notification implements ShouldQueue
{
    use Queueable;

    private mixed $title;
    private mixed $message;
    private mixed $cases;
    private mixed $planning = false;

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
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if($this->planning !== false) return ['database'];
        else return [FcmChannel::class,'database'];

    }


    public function toFcm($notifiable): FcmMessage
    {
        if (!App::environment('local'))
        {
            WebhookCall::create()
                ->url('https://chat.zemki.uni-bremen.de/hooks/pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
                ->payload(['text' => 'User ' . auth()->user()->email . ' sent a Notification to ' . $this->cases['user']['email'] . ' for the case "' . $this->cases['name'] . '". Poor human being!'])
                ->useSecret('pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
                ->dispatch();
        }

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
                //->setImage('https://www.uni-bremen.de/typo3conf/ext/package/Resources/Public/Images/Platzhalterbild_UniBremen.jpg'));
    }

    /**
     * Get the array representation of the notification.
     * @return array
     */
    #[ArrayShape(['title' => "string", 'message' => "string", "case" => "mixed","planning" => "mixed"])] public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'case' => $this->cases['id'],
            'planning' => $this->planning
        ];
    }

}
