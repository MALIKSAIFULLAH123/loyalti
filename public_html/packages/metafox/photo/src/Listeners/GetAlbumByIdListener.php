<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;

class GetAlbumByIdListener
{
    /**
     * @param  int        $albumId
     * @return Album|null
     */
    public function handle(int $albumId): ?Album
    {
        return resolve(AlbumRepositoryInterface::class)->getAlbumById($albumId);
    }
}
