<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'google' => [
        'client_id' => env('SSO_CLIENT_ID'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect' => env('SSO_REDIRECT_URL')
    ],
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],
    'cashfree' => [
        'key' => env('CASHFREE_API_KEY'),
        'secret' => env('CASHFREE_API_SECRET'),
        'url' => env('CASHFREE_MODE', 'https://sandbox.cashfree.com/pg'),
    ],
    'payu' => [
        'key' => env('PAYU_MERCHANT_KEY'),
        'salt' => env('PAYU_MERCHANT_SALT'),
        'mode' => env('PAYU_MODE', 'test'), // 'test' or 'production'
    ],
    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
    ],
    'mercadopago' => [
        'key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
];
