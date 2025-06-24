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
    'azure' => [
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI'),
        'tenant' => env('AZURE_TENANT_ID'),

        // CRUCIAL: These parameters are needed for proper ID token with groups
        'parameters' => [
            'scope' => 'openid profile email',
            'response_type' => 'code',
            'response_mode' => 'query',
            'prompt' => 'select_account',
        ],

        // Alternative method - you can also specify scopes directly
        'scopes' => ['openid', 'profile', 'email'],

        // Group ID mappings for role assignment
        'group_managers' => env('AZURE_GROUP_MANAGERS'),
        'group_student_services' => env('AZURE_GROUP_STUDENT_SERVICES'),
        'group_teachers' => env('AZURE_GROUP_TEACHERS'),

        // Optional: Additional settings that can help with group claims
        'logout_uri' => env('AZURE_LOGOUT_URI', env('APP_URL')),
        'tenant_id' => env('AZURE_TENANT_ID'), // Some providers need this separately
    ],
];
