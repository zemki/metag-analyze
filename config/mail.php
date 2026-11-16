<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | `MAIL_MAILER` is the current Laravel name for this setting. `MAIL_DRIVER`
    | is read as a fallback because that is what this application used before,
    | and what existing `.env` files on deployed servers still contain - so a
    | deploy of this file cannot break outgoing mail on a server whose `.env`
    | has not been updated yet.
    |
    | Prefer `MAIL_MAILER` in new environments; `MAIL_DRIVER` can be dropped
    | once every environment has been migrated.
    |
    | NOTE: this file previously used the pre-Laravel-7 layout - a top-level
    | `driver` key plus flat `host` / `port` / `username` / `password`. Laravel
    | still honours that through a backwards-compatibility branch in
    | MailManager, but it has a sharp edge: while a top-level `driver` key
    | exists, `MAIL_MAILER` is ignored entirely and the whole of this file is
    | handed to the transport as its config. Anyone following current Laravel
    | documentation would set `MAIL_MAILER`, see no error, and wonder why
    | nothing changed. That is why the layout below is the standard one.
    |
    */

    'default' => env('MAIL_MAILER', env('MAIL_DRIVER', 'smtp')),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Only the transports this application realistically uses are listed.
    | Laravel also supports ses / postmark / resend / roundrobin; add them here
    | if they are ever needed.
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',

            // Leave MAIL_SCHEME unset for the normal case. Laravel derives the
            // scheme from the port (465 => smtps, anything else => smtp) and
            // Symfony Mailer negotiates STARTTLS automatically when the server
            // advertises it, which is the usual setup on 587.
            //
            // MAIL_ENCRYPTION is deliberately absent: Laravel 11's
            // createSmtpTransport() does not read it. It had no effect under
            // the previous layout either, so nothing changes by dropping it.
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,

            // The name sent in the EHLO greeting. Some relays reject or
            // downgrade senders that announce something meaningless here, so
            // it defaults to the application's own host.
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        // Tries smtp first and falls back to writing the message to the log
        // rather than throwing, so a relay outage does not turn into a 500 for
        // the user. Opt in with MAIL_MAILER=failover.
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'mesoftware@uni-bremen.de'),
        'name' => env('MAIL_FROM_NAME', 'Metag Analyze'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | resources/views/vendor/mail holds this application's published theme
    | (html/themes/default.css), which the markdown mailables rely on.
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
