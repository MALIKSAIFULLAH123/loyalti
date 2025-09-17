<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Contracts;

/**
 * Interface HasSupportWebhook.
 * payment gateway interface.
 */
interface HasSupportWebhook
{
    /**
     * getWebhookUrl.
     *
     * @return string
     */
    public function getWebhookUrl(): string;

    /**
     * verifyWebhook.
     *
     * @param  array<mixed> $payload
     * @return bool
     */
    public function verifyWebhook(array $payload): bool;

    /**
     * handleWebhook.
     *
     * @param  array<mixed> $payload
     * @return bool
     */
    public function handleWebhook(array $payload): bool;
}
