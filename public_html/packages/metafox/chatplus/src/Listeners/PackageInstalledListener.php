<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Repositories\ChatServerInterface;

/**
 * handle package installed.
 *
 * Class PackageInstalledListener
 */
class PackageInstalledListener
{
    public function handle(string $package)
    {
        // Disable Chatplus when install Chat
        if ($package == 'metafox/chat') {
            $this->getChatServerRepository()->disableChatPlus($package);

            return;
        }

        if ($package !== 'metafox/chatplus') {
            return;
        }

        /* ChatServerInterface */
        $this->getChatServerRepository()->syncSettings(false, false);
    }

    public function getChatServerRepository(): ChatServerInterface
    {
        return resolve(ChatServerInterface::class);
    }
}
