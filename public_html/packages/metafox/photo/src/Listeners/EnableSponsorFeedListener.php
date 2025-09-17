<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() == Photo::ENTITY_TYPE) {
            resolve(PhotoRepositoryInterface::class)->enableFeedSponsor($content);

            return;
        }

        if ($content->entityType() == Album::ENTITY_TYPE) {
            resolve(AlbumRepositoryInterface::class)->enableFeedSponsor($content);
        }
    }
}
