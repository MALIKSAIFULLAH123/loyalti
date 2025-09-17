<?php

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Blog::ENTITY_TYPE) {
            return;
        }

        resolve(BlogRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
