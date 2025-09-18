<?php

namespace MetaFox\Page\Listeners;

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
        return ['/\[page=(\d+)\](.+?)\[\/page\]/u'];
    }
}
