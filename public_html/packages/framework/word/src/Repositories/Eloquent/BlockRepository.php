<?php

namespace MetaFox\Word\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Word\Models\Block;
use MetaFox\Word\Repositories\BlockRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BlockRepository.
 */
class BlockRepository extends AbstractRepository implements BlockRepositoryInterface
{
    public function model()
    {
        return Block::class;
    }
}
