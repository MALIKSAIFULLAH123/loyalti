<?php

namespace MetaFox\Music\Observers;

use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Repositories\GenreDataRepositoryInterface;

class SongObserver
{
    public function deleting(Song $song): void
    {
        $song->playlists()->sync([]);
    }

    public function deleted(Song $song): void
    {
        $duration = $song->duration;

        resolve(GenreDataRepositoryInterface::class)->deleteData($song);

        if (!$song->album instanceof Album) {
            return;
        }

        if (!$song->isApproved()) {
            return;
        }

        $song->album->decrementAmount('total_track');
        $song->album->decrementAmount('total_duration', $duration);
    }
}
