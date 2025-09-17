<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserVerify;
use MetaFox\User\Repositories\UserVerifyAdminRepositoryInterface;

class UserVerifyAdminRepository extends AbstractRepository implements UserVerifyAdminRepositoryInterface
{
    public function model()
    {
        return UserVerify::class;
    }
}
