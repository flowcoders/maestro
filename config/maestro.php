<?php

// config for Flowcoders/Maestro
return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used
    | by the application. You may set this to any of the providers you
    | have configured below.
    |
    */

    'default' => env('MAESTRO_PAYMENT_PROVIDER', 'asaas'),

    /*
    |--------------------------------------------------------------------------
    | Payment Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the credentials for each payment provider that
    | your application supports. You should add the necessary credentials
    | for each provider you plan to use.
    |
    */

    'providers' => [
        'mercadopago' => [
            'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
            // Environment is determined by token prefix (TEST- or APP-)
        ],

        'asaas' => [
            // For production use: https://api.asaas.com/v3
            // For sandbox use: https://api-sandbox.asaas.com/v3
            'base_url' => env('ASAAS_BASE_URL', 'https://api-sandbox.asaas.com/v3'),
            'access_token' => env('ASAAS_ACCESS_TOKEN'),
        ],

        // Add more providers here as needed
        // 'adyen' => [
        //     'api_key' => env('ADYEN_API_KEY'),
        //     'merchant_account' => env('ADYEN_MERCHANT_ACCOUNT'),
        //     'sandbox' => env('ADYEN_SANDBOX', true),
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timezone Settings
    |--------------------------------------------------------------------------
    |
    | Configure the timezone for payment dates and expiration times.
    | If null, will use Laravel's app timezone or system timezone for standalone usage.
    |
    */

    'timezone' => env('MAESTRO_TIMEZONE', null),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configure the HTTP client behavior for API requests to payment providers.
    |
    */

    'http' => [
        'timeout' => env('MAESTRO_HTTP_TIMEOUT', 30),
        'retry_attempts' => env('MAESTRO_HTTP_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('MAESTRO_HTTP_RETRY_DELAY', 1000), // milliseconds
    ],
];
