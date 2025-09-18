<?php

namespace MetaFox\Poll\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Poll\Models\PrivacyStream;
use MetaFox\Poll\Repositories\PrivacyStreamRepositoryInterface;

class PrivacyStreamRepository extends AbstractRepository implements PrivacyStreamRepositoryInterface
{
    public function model()
    {
        return PrivacyStream::class;
    }
}
