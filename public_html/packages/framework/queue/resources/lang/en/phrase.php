<?php

/* this is auto generated file */
return [
    'app_name'                                 => 'Queue',
    'block_for_desc'                           => 'Block job for a delay time in seconds (optional)',
    'block_for_label'                          => 'Block for',
    'connections'                              => 'Connections',
    'default_desc'                             => 'Default queue connection name.',
    'default_label'                            => 'Default Queue',
    'edit_connection'                          => '',
    'exception'                                => 'Exception',
    'failed_jobs'                              => 'Failed Jobs',
    'manage_queue'                             => 'Manage Queue',
    'message_queue'                            => 'Message Queue',
    'queue_connection_beanstalkd_driver_guide' => 'Beanstalk is a simple, fast work queue. Its interface is generic, but was originally designed for reducing the latency of page views in high-volume web.',
    'queue_connection_database_driver_guide'   => 'The jobs are stored in database table and in an "unprocessed" state. After jobs are processed, it will be updated into the "completed" state and removed from the table.',
    'queue_connection_rabbitmq_driver_guide'   => 'RabbitMQ is the most widely deployed open source message broker. Check more details at https://www.rabbitmq.com/.',
    'queue_connection_redis_driver_guide'      => 'Redis supports transactions, which means that commands can be executed as a queue instead of executing one at a time ...',
    'queue_connection_sqs_driver_guide'        => 'Amazon Simple Queue Service (SQS) is a fully managed message queuing service that enables you to decouple and scale microservices, distributed systems. Check more details at https://aws.amazon.com/sqs/.',
    'queue_connection_sync_driver_guide'       => 'The sync driver executes the process on the main execution thread instead of a background worker. Which is useful for debugging.',
    'retry_after_desc'                         => 'Specify delay time (in seconds) to retry',
    'retry_after_label'                        => 'Retry After',
    'settings'                                 => 'Settings',
    'site_settings'                            => 'Queue Settings',
    'time_retry_after_queue_timeout'           => 'Time to retry after a queue connection timeout.',
];
