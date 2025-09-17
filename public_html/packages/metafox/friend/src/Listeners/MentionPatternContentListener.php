<?php

namespace MetaFox\Friend\Listeners;

/**
 * Class ParseFeedContentListener.
 * @ignore
 * @codeCoverageIgnore
 */
class MentionPatternContentListener
{
    /**
     * @return array
     */
    public function handle(): array
    {
        return ['/\[user=(\d+)\](.+?)\[\/user\]/u'];
    }
}
