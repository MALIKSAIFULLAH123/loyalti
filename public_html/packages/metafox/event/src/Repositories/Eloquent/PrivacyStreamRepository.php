<?php

namespace MetaFox\Event\Repositories\Eloquent;

use MetaFox\Event\Models\PrivacyStream;
use MetaFox\Event\Repositories\PrivacyStreamRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

class PrivacyStreamRepository extends AbstractRepository implements PrivacyStreamRepositoryInterface
{
    public function model()
    {
        return PrivacyStream::class;
    }
}
