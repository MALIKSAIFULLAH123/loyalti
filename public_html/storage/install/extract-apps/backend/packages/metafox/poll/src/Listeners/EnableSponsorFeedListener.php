<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Poll::ENTITY_TYPE) {
            return;
        }

        resolve(PollRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
