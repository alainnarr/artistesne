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

    'turnstile' => [
        'enabled' => env('TURNSTILE_ENABLED', false),
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Canton AD FS — OpenID Connect
    |--------------------------------------------------------------------------
    |
    | On-premise AD FS instance used to authenticate administrators.
    | Configure ADFS_ALLOWED_GROUP to restrict access to a specific AD group;
    | leave empty to rely solely on the role column in the users table.
    |
    */
    'adfs' => [
        'base_url' => env('ADFS_BASE_URL'),
        'client_id' => env('ADFS_CLIENT_ID'),
        'client_secret' => env('ADFS_CLIENT_SECRET'),
        'redirect' => env('ADFS_REDIRECT_URI'),
        'allowed_group' => env('ADFS_ALLOWED_GROUP'),
        'jit_provisioning' => env('ADFS_JIT_PROVISIONING', false),
    ],

];
