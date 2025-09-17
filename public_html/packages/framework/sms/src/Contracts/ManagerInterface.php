<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Sms\Contracts;

/**
 * Interface ManagerInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ManagerInterface
{
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null      $name
     * @return ServiceInterface
     */
    public function service($name = null): ServiceInterface;
}
