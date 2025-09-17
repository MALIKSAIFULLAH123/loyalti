<?php

namespace MetaFox\User\Repositories\Contracts;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * AccountSettingRepositoryInterface.
 * @mixin AbstractRepository
 */
interface AccountSettingRepositoryInterface
{
    /**
     * @param  User  $user
     * @return array
     */
    public function getAccountSettings(User $user): array;
}
