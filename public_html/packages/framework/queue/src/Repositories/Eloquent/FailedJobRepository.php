<?php

namespace MetaFox\Queue\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Queue\Models\FailedJob;
use MetaFox\Queue\Repositories\FailedJobRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class FailedJobRepository.
 */
class FailedJobRepository extends AbstractRepository implements FailedJobRepositoryInterface
{
    public function model()
    {
        return FailedJob::class;
    }
}
