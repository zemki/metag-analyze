<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Tests\CreatesApplication;

/**
 * Laravel 11's default `email` rule accepts a quoted local part with a line break
 * (GHSA-5vg9-5847-vvmq, only fixed in Laravel 12). symfony/mime >= 7.4.12 refuses
 * such an address (GHSA-qpmx-3rfj-7rhv), so it can't inject mail headers or SMTP commands.
 *
 * Extends the framework TestCase on purpose: no database needed.
 */
class MailAddressLineBreakTest extends BaseTestCase
{
    use CreatesApplication;

    public function test_mail_to_an_address_with_a_line_break_is_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Mail::raw('test', fn ($message) => $message->to("\"x\r\nBcc: evil@example.com\"@example.com"));
    }
}
