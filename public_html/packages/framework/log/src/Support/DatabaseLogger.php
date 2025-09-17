<?php

namespace MetaFox\Log\Support;

use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return Logger
     */
    public function __invoke(array $config): Logger
    {
        return new Logger('Database', [
            new DatabaseHandler($config['table']),
        ]);
    }
}
