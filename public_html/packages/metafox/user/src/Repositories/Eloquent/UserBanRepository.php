<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserBan;
use MetaFox\User\Repositories\UserBanRepositoryInterface;

/**
 * Class UserBanRepository.
 *
 * @property UserBan $model
 */
class UserBanRepository extends AbstractRepository implements UserBanRepositoryInterface
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return UserBan::class;
    }

    public function updateAlternativeRoleId(int $oldRoleId, int $alternativeRoleId): bool
    {
        $total = $this->getModel()->newQuery()
            ->where(['return_user_group' => $oldRoleId])
            ->update(['return_user_group' => $alternativeRoleId]);

        return $total > 0;
    }
}
