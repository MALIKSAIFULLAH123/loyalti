<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Group::ENTITY_TYPE) {
            return;
        }

        resolve(GroupRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
