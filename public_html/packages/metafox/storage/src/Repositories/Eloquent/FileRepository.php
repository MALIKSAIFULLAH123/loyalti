<?php

namespace MetaFox\Storage\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\FileRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class FileRepository.
 */
class FileRepository extends AbstractRepository implements FileRepositoryInterface
{
    public function model()
    {
        return StorageFile::class;
    }

    public function getByOriginalId(mixed $originalId): Collection
    {
        return $this->getModel()->newQuery()
            ->where('origin_id', '=', $originalId)
            ->get();
    }
}
