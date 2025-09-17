<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Support\Facades\Group;

class UserGetMentionsListener
{
    /**
     * @param string $content
     *
     * @return int[]
     */
    public function handle(string $content)
    {
        return Group::getMentions($content);
    }
}
