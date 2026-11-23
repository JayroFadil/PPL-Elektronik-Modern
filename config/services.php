<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    'midtrans' => [
    	'server_key' => env('MIDTRANS_SERVER_KEY'),
    	'client_key' => env('MIDTRANS_CLIENT_KEY'),
    	'signature_key' => env('MIDTRANS_SIGNATURE_KEY'),
    	'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'rajaongkir' => [
        'api_key' => env('RAJAONGKIR_API_KEY'),
        'base_url' => env(
            'RAJAONGKIR_BASE_URL',
            'https://rajaongkir.komerce.id/api/v1'
        ),
    ],

];
