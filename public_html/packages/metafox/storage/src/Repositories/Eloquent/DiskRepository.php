<?php

namespace MetaFox\Storage\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Storage\Models\Disk;
use MetaFox\Storage\Repositories\DiskRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class DiskRepository.
 */
class DiskRepository extends AbstractRepository implements DiskRepositoryInterface
{
    public function model()
    {
        return Disk::class;
    }
}
