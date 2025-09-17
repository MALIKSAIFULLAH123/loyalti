<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Support\Facade\Chat;

/**
 * handle package installed.
 *
 * Class PackageInstalledListener
 */
class PackageActivatedListener
{
    public function handle(string $package)
    {
        // Disable Chat when activate ChatPlus
        if ($package != 'metafox/chatplus') {
            return;
        }

        Chat::disableChat($package, false);
    }
}
