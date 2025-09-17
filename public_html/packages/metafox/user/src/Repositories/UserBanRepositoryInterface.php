<?php

namespace MetaFox\User\Repositories;

use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Interface UserBanRepositoryInterface.
 * @mixin AbstractRepository
 */
interface UserBanRepositoryInterface
{
    /**
     * @param  int  $oldRoleId
     * @param  int  $alternativeRoleId
     * @return bool
     */
    public function updateAlternativeRoleId(int $oldRoleId, int $alternativeRoleId): bool;
}
