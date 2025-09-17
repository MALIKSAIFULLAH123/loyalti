<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Contracts;

/**
 * Interface ServiceManager.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ServiceManagerInterface
{
    public function get(string $name): ServiceInterface;
}
