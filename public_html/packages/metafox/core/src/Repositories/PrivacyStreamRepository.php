<?php

namespace MetaFox\Core\Repositories;

use MetaFox\Core\Models\PrivacyStream;
use MetaFox\Core\Repositories\Contracts\PrivacyStreamRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class PrivacyStreamRepository.
 * @method   PrivacyStream getModel()
 * @property PrivacyStream $model
 */
class PrivacyStreamRepository extends AbstractRepository implements PrivacyStreamRepositoryInterface
{
    public function model()
    {
        return PrivacyStream::class;
    }
}
