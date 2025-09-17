<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\MultiFactorToken;
use MetaFox\User\Repositories\MultiFactorTokenRepositoryInterface;

class MultiFactorTokenRepository extends AbstractRepository implements MultiFactorTokenRepositoryInterface
{
    public function model()
    {
        return MultiFactorToken::class;
    }
}
