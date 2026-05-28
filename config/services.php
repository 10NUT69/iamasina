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
/*
    |--------------------------------------------------------------------------
    | GOOGLE ANALYTICS
    |--------------------------------------------------------------------------
    */
    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'indexnow' => [
        'enabled' => env('INDEXNOW_ENABLED', true),
        'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
        'key' => env('INDEXNOW_KEY', 'fd033900e96a4eec85f1fa46216589fd'),
        'key_location' => env('INDEXNOW_KEY_LOCATION'),
        'timeout' => env('INDEXNOW_TIMEOUT', 5),
    ],

    'geoip' => [
        'external_lookup_enabled' => env('GEOIP_EXTERNAL_LOOKUP_ENABLED', false),
    ],
];
