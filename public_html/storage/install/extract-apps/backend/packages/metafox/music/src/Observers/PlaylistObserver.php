<?php

namespace MetaFox\Music\Observers;

use MetaFox\Music\Models\Playlist;

class PlaylistObserver
{
    public function deleted(Playlist $playlist): void
    {
        if ($playlist->image_file_id) {
            app('storage')->deleteFile($playlist->image_file_id, null);
        }

        $playlist->songs()->sync([]);
    }
}
