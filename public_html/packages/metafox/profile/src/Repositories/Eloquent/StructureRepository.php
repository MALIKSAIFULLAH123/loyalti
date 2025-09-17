<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Repositories\StructureRepositoryInterface;
use MetaFox\Profile\Models\Structure;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class StructureRepository.
 */
class StructureRepository extends AbstractRepository implements StructureRepositoryInterface
{
    public function model()
    {
        return Structure::class;
    }
}
