<?php

namespace MetaFox\Sms\Support\Services;

use MetaFox\Sms\Support\AbstractService;
use MetaFox\Sms\Support\Message;
use Psr\Log\LoggerInterface;

class LogService extends AbstractService
{
    /**
     * The Logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Create a new log service instance.
     *
     * @param  \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function send(Message $message)
    {
        $this->logger->debug($message);
    }

    /**
     * Get the logger for the LogService instance.
     *
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }
}
