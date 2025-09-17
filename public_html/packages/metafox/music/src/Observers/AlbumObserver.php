<?php

namespace MetaFox\Music\Observers;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;

class AlbumObserver
{
    public function deleted(Album $album)
    {
        $album->songs()->each(function ($song) {
            $song->delete();
        });

        resolve(GenreDataRepositoryInterface::class)->deleteData($album);
    }
}
