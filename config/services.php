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
        'url' => env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL', 'http://127.0.0.1:8001'),
    ],

    'backend_api' => [
        'url' => rtrim(env('SENAT_BACKEND_API_URL') ?: env('BACKEND_API_URL', 'http://127.0.0.1:8001'), '/'),
    ],

    'voting' => [
        // Full API base for voting endpoints (already includes /api when set).
        // Falls back to backend_api.url / backend.url + /api when null.
        'url' => env('SENAT_BACKEND_API_URL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'sso_secret' => env('GOOGLE_SSO_SECRET'),
    ],
];
