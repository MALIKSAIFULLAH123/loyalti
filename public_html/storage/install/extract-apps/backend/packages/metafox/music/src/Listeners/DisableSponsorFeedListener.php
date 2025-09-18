<?php

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class DisableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() == Song::ENTITY_TYPE) {
            resolve(SongRepositoryInterface::class)->disableFeedSponsor($content);

            return;
        }

        if ($content->entityType() == Album::ENTITY_TYPE) {
            resolve(AlbumRepositoryInterface::class)->disableFeedSponsor($content);

            return;
        }

        if ($content->entityType() == Playlist::ENTITY_TYPE) {
            resolve(PlaylistRepositoryInterface::class)->disableFeedSponsor($content);
        }
    }
}
