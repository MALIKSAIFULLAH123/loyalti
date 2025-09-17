<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Repositories\ProfileRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class ProfileRepository.
 */
class ProfileRepository extends AbstractRepository implements ProfileRepositoryInterface
{
    public function model()
    {
        return Profile::class;
    }
}
