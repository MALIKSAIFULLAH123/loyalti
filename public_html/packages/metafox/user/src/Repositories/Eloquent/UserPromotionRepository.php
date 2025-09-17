<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserPromotion;
use MetaFox\User\Repositories\UserPromotionRepositoryInterface;

class UserPromotionRepository extends AbstractRepository implements UserPromotionRepositoryInterface
{
    public function model()
    {
        return UserPromotion::class;
    }
}
