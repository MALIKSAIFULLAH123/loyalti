<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Video::ENTITY_TYPE) {
            return;
        }

        resolve(VideoRepositoryInterface::class)->disableFeedSponsor($content);
    }
}
