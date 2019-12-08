<?php
return [
    /*
     * //
     */
    'enabled' => env('SSR_ENABLED', env('APP_ENV') === 'production'),

    /*
     * //
     */
    'debug' => env('APP_DEBUG', false),

    /*
     * //
     */
    'local' => env('SSR_LOCAL', env('APP_ENV') === 'local'),

    /*
     * //
     */
    'node' => [
        'node_path' => env('NODE_PATH', '/usr/local/bin/node'),
        'temp_path' => storage_path('app/ssr'),
    ],

    /*
     * //
     */
    'mix' => true,

    /*
     * //
     */
    'context' => [],

    /*
     * //
     */
    'env' => [
        'NODE_ENV' => 'production',
        'VUE_ENV' => 'server',
    ],

    /**
     * //
     */
    'host' => 'https://ssraas.com',
];
