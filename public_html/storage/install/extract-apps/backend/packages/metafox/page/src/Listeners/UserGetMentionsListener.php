<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Support\Facade\Page;

class UserGetMentionsListener
{
    /**
     * @param string $content
     *
     * @return int[]
     */
    public function handle(string $content)
    {
        return Page::getMentions($content);
    }
}
