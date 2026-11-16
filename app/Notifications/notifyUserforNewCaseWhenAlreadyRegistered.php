<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class notifyUserforNewCaseWhenAlreadyRegistered extends Notification
{
    use Queueable;

    protected string $message;

    protected string $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->message = (string) ($details['message'] ?? '');
        $this->subject = (string) ($details['subject'] ?? '');
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
            // config(), not env(): after `php artisan config:cache` the .env
            // file is not loaded and env() returns null, which would leave the
            // application name blank in the subject and body.
            ->subject(strlen($this->subject) > 0 ? $this->subject : 'New Case on ' . config('app.name'))
            ->lineIf(strlen($this->message) == 0, 'You have been added to a new case, please login in ' . config('app.name') . ' to check it out.')
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
