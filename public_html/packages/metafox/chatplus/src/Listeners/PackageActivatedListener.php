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
class PackageActivatedListener
{
    public function handle(string $package)
    {
        // Disable ChatPlus when activate Chat
        match ($package) {
            'metafox/chat'        => $this->getChatServerRepository()->disableChatPlus($package, false),
            'metafox/chatgpt-bot' => $this->handleEnableChatGptBot(),
            default               => null
        };
    }

    public function getChatServerRepository(): ChatServerInterface
    {
        return resolve(ChatServerInterface::class);
    }

    public function handleEnableChatGptBot(): void
    {
        try {
            $resolver = app('chatgpt-bot.server');
            $resolver->syncSettings(true, false);
        } catch (\Exception $exception) {
            // Silent error
            return;
        }
    }
}
