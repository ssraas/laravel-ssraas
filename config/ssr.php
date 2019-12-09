<?php
return [
    /*
     * Enables server side rendering. When disabled, the fallback html will be returned.
     */
    'enabled' => env('SSR_ENABLED', env('APP_ENV') === 'production'),

    /*
     * When enabled, server rendered Javscript errors will be thrown as a php exception.
     * When debug is disabled, server rendered JavaScript will fail silently, and the fallback will be returned.
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Significantly reduce load times by caching rendered responses.
     *
     * By default, results are cached for 24 hours, using a hash of the script filesize,
     * context and env.
     *
     * Disable cache by removing this, or use your own that implements \Ssr\LaravelSsraas\CacheHandlerInterface
     */
    'cache' => \Ssraas\LaravelSsraas\CacheHandler::class,

    /*
     * Context is used to pass data to the server script. Fill this array with
     * data you *always* want to send to the server script. Context can contain
     * anything that's json serializable.
     */
    'context' => [],

    /*
     * Env is used to fill `process.env` when the server script is executed.
     * Fill this array with data you *always* want to send to the server script.
     * The env array is only allowed to be a single level deep, and can only
     * contain primitive values like numbers, strings or booleans.
     *
     * By default, env is prefilled with some necessary values for server side
     * rendering Vue applications.
     */
    'env' => [
        'NODE_ENV' => 'production',
        'VUE_ENV' => 'server',
    ],

    /*
     * Set to true if you're using Laravel Mix and want to pass script identifiers
     * instead of full paths.
     */
    'mix' => true,

    /*
     * Enable local rendering using Node (config below). Useful for local development.
     */
    'local' => env('SSR_LOCAL', env('APP_ENV') === 'local'),

    /*
     * Setup for local Node rendering
     */
    'node' => [
        'node_path' => env('NODE_PATH', '/usr/local/bin/node'),
        'temp_path' => storage_path('app/ssr'),
    ],
];
