<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $user;

    /**
     * Create a new message instance.
     * @return void
     */
    public function __construct(User $user, $emailtext)
    {
        $this->user = $user;
        $this->emailtext = $emailtext;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->from('no_reply@kommunikative-figurationen.de')->markdown('email.setpassword')->with(['user' => $this->user, 'text' => $this->emailtext]);
    }
}
