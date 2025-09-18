<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\User\Models\UserEntity;

/**
 * Class PageRouteListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PageRouteListener
{
    /**
     * @param  string                   $url
     * @return array<string,mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $url): ?array
    {
        //@todo: Current business rule not using this. Shall update to use it later
        return null;
    }
}
