<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != LiveVideo::ENTITY_TYPE) {
            return;
        }

        resolve(LiveVideoRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
