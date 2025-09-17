<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\Storage\Models\Asset;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class AssetUpdatedListener
{
    public function handle(?Asset $asset): void
    {
        if (!$asset instanceof Asset) {
            return;
        }
        if ($asset->module_id != 'chatplus') {
            return;
        }
        /* ChatServerInterface */
        resolve(ChatServerInterface::class)->syncSettings(true);
    }
}
