<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Collection;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;

class DefaultUserAlbumListener
{
    public function handle(int $ownerId): Collection
    {
        return resolve(AlbumRepositoryInterface::class)->getDefaultUserAlbums($ownerId);
    }
}
