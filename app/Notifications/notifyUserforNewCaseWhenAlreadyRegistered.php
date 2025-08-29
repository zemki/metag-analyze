<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class notifyUserforNewCaseWhenAlreadyRegistered extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->message = $details['message'];
        $this->subject = $details['subject'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from('noreply@' . strtolower(str_replace(' ', '', env('APP_NAME'))) . '.org', env('APP_NAME'))
            ->subject(strlen($this->subject) > 0 ? $this->subject : 'New Case on ' . env('APP_NAME'))
            ->lineIf(strlen($this->message) == 0, 'You have been added to a new case, please login in ' . env('APP_NAME') . ' to check it out.')
            ->lineIf(strlen($this->message) > 0, $this->message);

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
            //
        ];
    }
}
