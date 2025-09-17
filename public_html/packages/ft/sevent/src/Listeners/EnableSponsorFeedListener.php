<?php

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Sevent::ENTITY_TYPE) {
            return;
        }

        resolve(SeventRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
