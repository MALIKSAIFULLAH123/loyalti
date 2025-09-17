<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['daily'],
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/metafox.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
        ],
        'daily' => [
            'driver'     => 'daily',
            'path'       => storage_path('logs/metafox.log'),
            'level'      => env('LOG_LEVEL', 'debug'),
            'days'       => 1,
            'label'      => 'Filesystems', // add label to allow admin choose from admincp.
            'selectable' => true,
        ],
        'slack' => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'metafox',
            'emoji'    => ':boom:',
            'level'    => env('LOG_LEVEL', 'critical'),
        ],
        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],
        'stderr' => [
            'driver'    => 'monolog',
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
        ],
        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],
        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],
        'emergency' => [
            'path' => storage_path('logs/emergency.log'),
        ],
        'dev' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/installation.log'),
            'level'  => env('APP_DEBUG') ? 'debug' : 'error',
            'days'   => 1,
        ],
        'profiler' => [
            'driver' => 'single',
            'path'   => storage_path('logs/profiler.log'),
            'level'  => env('MFOX_PROFILER', false) ? 'debug' : 'error',
            'days'   => 1,
        ],
        'installation' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/installation.log'),
            'level'  => env('APP_DEBUG') ? 'debug' : 'error',
            'days'   => 1,
        ],
        'payment' => [
            'driver' => 'custom',
            'via'    => \MetaFox\Log\Support\DatabaseLogger::class,
            'table'  => 'payment_logs',
        ],
        //        'importer'     => [
        //            'driver' => 'daily',
        //            'path'   => storage_path('logs/importer.log'),
        //            'level'  => 'debug',
        //            'days'   => 1,
        //        ],
        'importer' => [
            'driver' => 'custom',
            'via'    => \MetaFox\Log\Support\DatabaseLogger::class,
            'table'  => 'importer_logs',
        ],
        //        'mongodb'      => [
        //            'driver'  => 'monolog',
        //            'handler' => Monolog\Handler\MongoDBHandler::class,
        //            'with'    => [
        //                'database'   => env('MONGODB_LOG_DB'),
        //                'collection' => env('MONGODB_LOG_COLLECTION'),
        //            ],
        //        ],
        'database' => [
            'driver'     => 'custom',
            'via'        => \MetaFox\Log\Support\DatabaseLogger::class,
            'table'      => 'log_messages',
            'label'      => 'Database (log_messages)',
            'selectable' => true,
        ],
        'video' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/video.log'),
            'level'  => env('APP_DEBUG') ? 'debug' : 'error',
            'days'   => 1,
        ],
        'push' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/' . env('LOG_PUSH_NOTIFICATION_FILENAME', 'push') . '.log'),
            'level'  => env('APP_DEBUG') ? 'debug' : 'error',
            'days'   => 1,
        ],
    ],
];
