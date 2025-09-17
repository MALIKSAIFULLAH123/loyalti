<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sms\Contracts;

use MetaFox\Sms\Support\Message;

/**
 * Class ServiceInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ServiceInterface
{
    /**
     * send.
     *
     * @param  Message $message
     * @return void
     */
    public function send(Message $message);

    /**
     * Get the value of config.
     *
     * @return string|array<string>
     */
    public function getConfig(?string $key);

    /**
     * Set the value of config.
     *
     * @param array<string> $config
     *
     * @return self
     */
    public function setConfig(array $config);

    /**
     * @param string $phoneNumber
     *
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool;
}
