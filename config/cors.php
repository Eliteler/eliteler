<?php

$appUrl = rtrim(config('app.url'), '/');
$host   = parse_url($appUrl, PHP_URL_HOST);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

 
    'paths' => [
        // Profile & vCard
        '{id}',
        '{id}/product/*',
        '{id}/categories',
        'dynamic-card/*',
        'check-password/*',
        'download/*',
        'nfc/*',

        // Store policies
        '{id}/privacy-policy',
        '{id}/terms-and-conditions',
        '{id}/return-policy',
        '{id}/shipping-policy',
        '{id}/cookie-policy',
        '{id}/contact',

        // Store & orders
        'order/place',

        // Appointments & services
        'get-available-time-slots',
        'book-appointment',
        'book-service',

        // Enquiry & newsletter
        'sent-enquiry',
        'subscribe/newsletter',
        'subscribe/store/newsletter',

        // Locale
        'set-locale',

        // API
        'api/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://' . $host,
        'https://*.' . $host,
        '*'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => false,

    'max_age' => 0,

    'supports_credentials' => true,

];
