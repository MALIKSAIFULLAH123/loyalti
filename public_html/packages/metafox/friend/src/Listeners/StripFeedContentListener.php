<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Platform\Contracts\Entity;

/**
 * Class StripFeedContentListener.
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StripFeedContentListener
{
    /**
     * @param Entity $item
     * @param ?string $content
     *
     * @return void
     */
    public function handle(Entity $item, ?string &$content): void
    {
        $content = preg_replace_callback('/\[user=(\d+)\](.+?)\[\/user\]/u', function ($groups) {
            [,, $username] = $groups;

            return $username;
        }, $content);
    }
}
