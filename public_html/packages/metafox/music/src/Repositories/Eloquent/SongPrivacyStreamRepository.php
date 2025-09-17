<?php

namespace MetaFox\Music\Repositories\Eloquent;

use MetaFox\Music\Models\SongPrivacyStream;
use MetaFox\Music\Repositories\SongPrivacyStreamRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class SongPrivacyStreamRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class SongPrivacyStreamRepository extends AbstractRepository implements SongPrivacyStreamRepositoryInterface
{
    public function model()
    {
        return SongPrivacyStream::class;
    }
}
