<?php

namespace MetaFox\Music\Repositories\Eloquent;

use MetaFox\Music\Models\PlaylistPrivacyStream;
use MetaFox\Music\Repositories\PlaylistPrivacyStreamRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class PlaylistPrivacyStreamRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistPrivacyStreamRepository extends AbstractRepository implements PlaylistPrivacyStreamRepositoryInterface
{
    public function model()
    {
        return PlaylistPrivacyStream::class;
    }
}
