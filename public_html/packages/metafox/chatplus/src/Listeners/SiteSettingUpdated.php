<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class SiteSettingUpdated
{
    public function handle($module)
    {
        if (!in_array($module, ['chatplus', 'firebase'])) {
            return;
        }

        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->syncSettings(false, $module != 'firebase');
    }
}
