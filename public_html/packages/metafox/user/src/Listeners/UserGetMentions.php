<?php

namespace MetaFox\User\Listeners;

use MetaFox\User\Support\Facades\User;

class UserGetMentions
{
    /**
     * @param  string|null  $content
     *
     * @return int[]
     */
    public function handle(?string $content)
    {
        if ($content == null) {
            return [];
        }
        return User::getMentions($content);
    }
}
