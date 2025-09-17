<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sms\Contracts;

/**
 * Interface SmsSupportContracts.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface SmsSupportContracts
{
    /**
     * Get a mailer instance by name.
     *
     * @return bool
     */
    public function validateConfiguration(): bool;
}
