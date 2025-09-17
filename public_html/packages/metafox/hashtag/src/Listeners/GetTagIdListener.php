<?php

namespace MetaFox\Hashtag\Listeners;

use MetaFox\Hashtag\Repositories\TagRepositoryInterface;

class GetTagIdListener
{
    public function handle(?string $tag): int
    {
        if (null === $tag) {
            return 0;
        }

        return resolve(TagRepositoryInterface::class)->getTagId($tag);
    }
}
