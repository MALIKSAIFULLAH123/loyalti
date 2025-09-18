<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Models\Poll;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Poll::ENTITY_TYPE) {
            return;
        }

        resolve(PollRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
