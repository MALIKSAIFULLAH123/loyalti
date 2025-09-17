<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => env('MFOX_CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "apc", "array", "database", "file",
    |         "memcached", "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'null' => [
            'driver'     => \Illuminate\Cache\NullStore::class,
            'selectable' => true,
            'label'      => 'Disable Cache',
        ],
        'apc' => [
            'driver'     => 'apc',
            'apcu'       => true,
            'selectable' => extension_loaded('apc'),
            'label'      => 'APC',
        ],
        'array' => [
            'driver'    => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver'          => 'database',
            'table'           => 'cache',
            'connection'      => null,
            'lock_connection' => null,
            'selectable'      => true,
            'label'           => 'Database',
        ],

        'file' => [
            'driver'     => 'file',
            'path'       => storage_path('framework/cache/data'),
            'selectable' => true,
            'label'      => 'Filesystem',
        ],

        'memcached' => [
            'driver'        => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl'          => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host'   => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port'   => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
            'selectable' => env('MEMCACHED_HOST'),
            'label'      => 'Memcached:' . env('MEMCACHED_HOST'),
        ],

        'redis' => [
            'driver'     => 'redis',
            'connection' => 'cache',
            //            'lock_connection' => 'default',
            'selectable' => env('REDIS_HOST'),
            'label'      => 'Redis:' . env('REDIS_HOST'),
        ],

        'dynamodb' => [
            'driver'     => 'dynamodb',
            'key'        => env('AWS_ACCESS_KEY_ID'),
            'secret'     => env('AWS_SECRET_ACCESS_KEY'),
            'region'     => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table'      => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint'   => env('DYNAMODB_ENDPOINT'),
            'selectable' => env('DYNAMODB_CACHE_TABLE'),
            'label'      => 'Redis:' . env('DYNAMODB_ENDPOINT'),
        ],
        'octane' => [
            'driver' => 'octane',
        ],

        /*
         * The throttling store are for using in RateLimiter or any Custom Rate Limiter in the platform
         * In order to prevent flush all cache including throttling data
         */
        'throttling' => [
            'driver'     => env('THROTTLING_CACHE_DRIVER', 'file'),
            'path'       => storage_path('framework/cache/throttling'),
            'selectable' => false,
            'connection' => 'throttling',
            'label'      => 'Throttling: Redis',
        ],
        //----------------------------------------------------------------
    ],

    /*
     * This list includes the cache stores which are not configurable in ACP
     */
    'system_stores' => [
        'null', 'apc', 'array', 'octane', 'dynamodb',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, or DynamoDB cache
    | stores there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('MFOX_SITE_NAME', 'laravel'), '_') . '_cache'),

    /*
     * treat local cache driver for 02 layer cache.
     */
    'local_store' => !env('MFOX_APP_INSTALLED') ? false : ((env('MFOX_CACHE_DRIVER') == 'array') ? 'array' : 'file'),

    /*
     * Limiter Cache Driver
     */
    'limiter' => 'throttling',
];
