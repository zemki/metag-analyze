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
    protected $emailtext;
    protected $qrCodeData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $emailtext, $qrCodeData = null)
    {
        $this->user = $user;
        $this->emailtext = $emailtext;
        $this->qrCodeData = $qrCodeData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('mesoftware@uni-bremen.de')
            ->markdown('email.setpassword')
            ->with([
                'user' => $this->user,
                'text' => $this->emailtext,
                'qrCodeData' => $this->qrCodeData
            ]);
    }
}
