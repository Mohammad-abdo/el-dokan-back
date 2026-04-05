<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter([
        env('CORS_ALLOWED_ORIGIN', 'http://localhost:5173'),
        env('CORS_ALLOWED_ORIGIN_FRONTEND', 'http://localhost:3000'),
        env('CORS_ALLOWED_ORIGIN_DASHBOARD', 'http://localhost:5173'),
    ]),

    'allowed_origins_patterns' => [
        '/^https?:\/\/(.*\.)?localhost(:[0-9]+)?$/',
        '/^https?:\/\/127\.0\.0\.1(:[0-9]+)?$/',
    ],

    'allowed_headers' => [
        'Accept',
        'Accept-Language',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-Request-ID',
        'X-CSRF-TOKEN',
        'X-Timezone',
    ],

    'exposed_headers' => [
        'X-Request-ID',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],

    'max_age' => 86400,

    'supports_credentials' => true,

];

