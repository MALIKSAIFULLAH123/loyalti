<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserVerifyError;
use MetaFox\User\Repositories\UserVerifyErrorRepositoryInterface;

class UserVerifyErrorRepository extends AbstractRepository implements UserVerifyErrorRepositoryInterface
{
    public function model()
    {
        return UserVerifyError::class;
    }
}
