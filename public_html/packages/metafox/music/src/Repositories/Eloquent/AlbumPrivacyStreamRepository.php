<?php

namespace MetaFox\Music\Repositories\Eloquent;

use MetaFox\Music\Models\AlbumPrivacyStream;
use MetaFox\Music\Repositories\AlbumPrivacyStreamRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class AlbumPrivacyStreamRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumPrivacyStreamRepository extends AbstractRepository implements AlbumPrivacyStreamRepositoryInterface
{
    public function model()
    {
        return AlbumPrivacyStream::class;
    }
}
