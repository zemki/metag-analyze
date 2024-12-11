<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 120,
        ],
        'verification' => [
            'expire' => 1,
        ],
    ],
    'verification' => [
        'expire' => 525600,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Token Settings
    |--------------------------------------------------------------------------
    */

    // Token expiration time in minutes (24 hours default)
    'token_expiration' => env('API_TOKEN_EXPIRATION', 2880),

    // Maximum failed login attempts before lockout
    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),

    // Lockout duration in minutes
    'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 10),

    // Token cache duration in minutes
    'token_cache_duration' => env('TOKEN_CACHE_DURATION', 1440),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Settings
    |--------------------------------------------------------------------------
    */

    // Login attempts per minute
    'login_rate_limit' => env('LOGIN_RATE_LIMIT', '7,1'),

    // Token creation rate limit
    'token_creation_rate_limit' => env('TOKEN_CREATION_RATE_LIMIT', '7,1'),
    'token_expiration_days' => env('API_TOKEN_EXPIRATION_DAYS', 30),

];
