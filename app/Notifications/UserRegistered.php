<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Notification;
use App\User;

class UserRegistered extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {

        $this->email = $details['email'];
        $this->users =  User::all()->count();
    }



    public function toRocketChat(): RocketChatMessage
    {
        WebhookCall::create()
            ->url('https://chat.zemki.uni-bremen.de/hooks/pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
            ->payload(['text' => 'User '.$this->email.' has registered. We have a total of '.$this->users])
            ->useSecret('pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
            ->dispatch();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'email' => $this->email,
            'users' => $this->users
        ];
    }
}
