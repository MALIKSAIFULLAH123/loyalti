<?php

namespace MetaFox\LiveStreaming\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PlaybackData.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PlaybackDataRepositoryInterface
{
    /**
     * @param  int   $id
     * @param  array $playback
     * @return bool
     */
    public function updatePlaybackData(int $id, array $playback): bool;
}
