<?php

namespace MetaFox\Group\Listeners;

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
        return ['/\[group=(\d+)\](.+?)\[\/group\]/u'];
    }
}
