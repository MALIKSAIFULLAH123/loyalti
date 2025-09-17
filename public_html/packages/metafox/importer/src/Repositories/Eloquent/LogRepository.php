<?php

namespace MetaFox\Importer\Repositories\Eloquent;

use MetaFox\Importer\Models\Log;
use MetaFox\Importer\Repositories\LogRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class LogRepository.
 */
class LogRepository extends AbstractRepository implements LogRepositoryInterface
{
    public function model()
    {
        return Log::class;
    }
}
