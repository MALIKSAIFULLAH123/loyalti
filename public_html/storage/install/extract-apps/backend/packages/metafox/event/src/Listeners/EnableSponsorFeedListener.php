<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Event::ENTITY_TYPE) {
            return;
        }

        resolve(EventRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
