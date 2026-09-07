<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Report of accounts that never verified their email address, sent to the
 * addresses in ADMINS by `php artisan remove:check-unverified`.
 *
 * The CSV is attached from a path under storage/app that the command creates
 * and deletes around the send. That only works while mail is dispatched
 * synchronously - if this is ever queued, the file will already be gone by the
 * time the job runs and the attachment must be inlined instead.
 */
class UnverifiedUsersReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected string $filePath) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ': ' . __('Unverified users report'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'email.removeusers',
            with: ['filename' => basename($this->filePath)],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! is_readable($this->filePath)) {
            return [];
        }

        return [
            Attachment::fromPath($this->filePath)
                ->as(basename($this->filePath))
                ->withMime('text/csv'),
        ];
    }
}
