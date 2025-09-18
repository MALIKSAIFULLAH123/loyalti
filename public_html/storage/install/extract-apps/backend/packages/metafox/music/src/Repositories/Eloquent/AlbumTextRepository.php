<?php

namespace MetaFox\Music\Repositories\Eloquent;

use MetaFox\Music\Models\AlbumText;
use MetaFox\Music\Repositories\AlbumTextRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class AlbumTextRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumTextRepository extends AbstractRepository implements AlbumTextRepositoryInterface
{
    public function model()
    {
        return AlbumText::class;
    }
}
