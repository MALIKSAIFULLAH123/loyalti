<?php

namespace MetaFox\Log\Repositories\Eloquent;

use MetaFox\Log\Models\LogMessage;
use MetaFox\Log\Repositories\LogMessageRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class LogMessageRepository.
 */
class LogMessageRepository extends AbstractRepository implements LogMessageRepositoryInterface
{
    public function model()
    {
        return LogMessage::class;
    }
}
