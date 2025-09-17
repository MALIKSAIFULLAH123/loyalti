<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Contact\Contracts;

/**
 * Interface Contact.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface Contact
{
    /**
     * send contact information to the configured recipients.
     *
     * @param  array<mixed> $params
     * @return void
     */
    public function send(array $params = []): void;
}
