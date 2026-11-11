<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services & Backend API
    |--------------------------------------------------------------------------
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

    'backend' => [
        'url' => env('BACKEND_API_URL', 'http://127.0.0.1:8001'),
    ],

    'backend_api' => [
        'url' => rtrim(env('BACKEND_API_URL', 'http://127.0.0.1:8001'), '/'),
    ],
];
