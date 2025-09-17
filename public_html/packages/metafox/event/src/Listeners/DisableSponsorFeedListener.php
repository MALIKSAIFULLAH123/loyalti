<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Event::ENTITY_TYPE) {
            return;
        }

        resolve(EventRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
