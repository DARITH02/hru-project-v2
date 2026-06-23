<?php

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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_filter([
        env('FRONTEND_URL'),
        env('APP_URL'),
        'http://localhost',
        'http://localhost:80',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:8080',
        'http://127.0.0.1',
        'http://127.0.0.1:80',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8080',
        'https://hru-ats.vercel.app',
        'http://192.168.18.2:3000',
        'http://192.168.18.2:3001',
        'http://192.168.18.2:5173',
        'http://192.168.18.2:8080',
    ]))),

    'allowed_origins_patterns' => [
        '/^http:\/\/192\.168\.\d{1,3}\.\d{1,3}:(3000|3001|5173|8080)$/',
        '/^http:\/\/10\.\d{1,3}\.\d{1,3}\.\d{1,3}:(3000|3001|5173|8080)$/',
        '/^http:\/\/172\.(1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3}:(3000|3001|5173|8080)$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
