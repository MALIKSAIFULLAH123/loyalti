<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != ForumThread::ENTITY_TYPE) {
            return;
        }

        resolve(ForumThreadRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
