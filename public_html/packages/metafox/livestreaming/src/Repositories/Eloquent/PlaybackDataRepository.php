<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use MetaFox\LiveStreaming\Models\PlaybackData;
use MetaFox\LiveStreaming\Repositories\PlaybackDataRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class PlaybackDataRepository.
 */
class PlaybackDataRepository extends AbstractRepository implements PlaybackDataRepositoryInterface
{
    public function model()
    {
        return PlaybackData::class;
    }

    /**
     * @param  int                                              $id
     * @param  array                                            $playback
     * @return bool
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function updatePlaybackData(int $id, array $playback): bool
    {
        if (!isset($playback['playback_id'])) {
            return false;
        }
        $this->updateOrCreate([
            'live_id'     => $id,
            'playback_id' => $playback['playback_id'],
        ], [
            'live_id'     => $id,
            'playback_id' => $playback['playback_id'],
            'privacy'     => $playback['privacy'] ?? 0,
        ]);

        return true;
    }
}
