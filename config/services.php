<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |----------------------------------------------------------------------
    | Payment gateways (Ethiopian providers)
    |----------------------------------------------------------------------
    |
    | Inbound callbacks are authenticated with an HMAC-SHA256 signature over
    | `<timestamp>.<raw request body>`, sent as `X-Provider-Signature:
    | sha256=<hex>` alongside `X-Provider-Timestamp: <unix seconds>`. The
    | timestamp is part of the signed payload and must sit inside
    | `tolerance_seconds`, so a captured request cannot be replayed. No secret
    | -> the provider rejects every call.
    |
    */
    'payments' => [
        // Forged-webhook flood guard for POST /payments/callback, keyed by IP.
        'callback_rate_limit' => env('PAYMENT_CALLBACK_RATE_LIMIT', 60),
        'providers' => [
            'telebirr' => [
                'secret' => env('TELEBIRR_WEBHOOK_SECRET'),
                'tolerance_seconds' => env('TELEBIRR_WEBHOOK_TOLERANCE', 300),
            ],
            'cbe_birr' => [
                'secret' => env('CBE_BIRR_WEBHOOK_SECRET'),
                'tolerance_seconds' => env('CBE_BIRR_WEBHOOK_TOLERANCE', 300),
            ],
            'chapa' => [
                'secret' => env('CHAPA_WEBHOOK_SECRET'),
                'tolerance_seconds' => env('CHAPA_WEBHOOK_TOLERANCE', 300),
            ],
        ],
    ],

];
