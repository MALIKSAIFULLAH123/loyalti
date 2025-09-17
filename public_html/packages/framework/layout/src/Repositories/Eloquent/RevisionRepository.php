<?php

namespace MetaFox\Layout\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Layout\Repositories\RevisionRepositoryInterface;
use MetaFox\Layout\Models\Revision;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class RevisionRepository
 *
 */
class RevisionRepository extends AbstractRepository implements RevisionRepositoryInterface
{
    public function model()
    {
        return Revision::class;
    }
}
