<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class SendTestMailCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'mail:sendtest
                            {email : Address to send the test message to}';

    /**
     * @var string
     */
    protected $description = 'Send a test email to verify the outgoing mail configuration';

    /**
     * Send one plain-text message and report exactly what happened.
     *
     * Deliberately uses Mail::raw() rather than a Mailable: a diagnostic must
     * not be able to fail because of a Blade template, so the only thing under
     * test here is the transport itself.
     */
    public function handle(): int
    {
        $email = trim($this->argument('email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Not a valid email address: {$email}");

            return self::FAILURE;
        }

        $appName = config('app.name');
        $sentAt = now();
        $subject = $appName . ': Testmail sent at ' . $sentAt->format('Ymd H:i:s');

        // config/mail.php resolves MAIL_MAILER, falling back to the legacy
        // MAIL_DRIVER, so this is the mailer that will actually be used.
        $mailer = config('mail.default');

        try {
            $transport = get_class(Mail::mailer()->getSymfonyTransport());
        } catch (Throwable $e) {
            $this->error('Could not build the mail transport: ' . $e->getMessage());

            return self::FAILURE;
        }

        $from = config('mail.from.address');
        $body = implode(PHP_EOL, [
            'This is a test message from ' . $appName . '.',
            '',
            'Sent at : ' . $sentAt->toDateTimeString() . ' (' . config('app.timezone') . ')',
            'To      : ' . $email,
            'From    : ' . $from,
            'App URL : ' . config('app.url'),
            'Env     : ' . config('app.env'),
            'Mailer  : ' . $mailer,
            'Host    : ' . (config("mail.mailers.{$mailer}.host") ?? '-')
                . ':' . (config("mail.mailers.{$mailer}.port") ?? '-'),
            '',
            'If you are reading this, outgoing mail works.',
        ]);

        $this->line('Sending to <info>' . $email . '</info> via mailer <info>' . $mailer . '</info>...');

        try {
            Mail::raw($body, function ($message) use ($email, $subject, $from) {
                $message->to($email)->subject($subject);

                // Only set an explicit From when one is configured; otherwise
                // let the global mail.from apply.
                if ($from) {
                    $message->from($from, config('mail.from.name'));
                }
            });
        } catch (TransportExceptionInterface $e) {
            // The transport reached the server (or tried to) and it refused.
            $this->error('The mail server rejected the message.');
            $this->line('  ' . $e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Sending failed: ' . get_class($e));
            $this->line('  ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Message handed to the transport.');
        $this->line('  Subject   : ' . $subject);
        $this->line('  Transport : ' . $transport);

        // "Sent" means something different for these two, and saying so here
        // avoids a green success line being mistaken for real delivery.
        if ($mailer === 'log') {
            $this->newLine();
            $this->warn('MAILER IS "log" - nothing was actually sent.');
            $this->line('The message was written to the log channel instead. Set MAIL_MAILER'
                . ' to a real transport (e.g. smtp) to deliver it.');
        } elseif ($mailer === 'array') {
            $this->newLine();
            $this->warn('MAILER IS "array" - nothing was actually sent (messages are kept in memory).');
        } else {
            $this->newLine();
            $this->line('Handing off without an error means the transport accepted it. Delivery to'
                . ' the inbox can still fail later (relay rejection, spam filtering) - check the'
                . ' mail server logs if it does not arrive.');
        }

        return self::SUCCESS;
    }
}
