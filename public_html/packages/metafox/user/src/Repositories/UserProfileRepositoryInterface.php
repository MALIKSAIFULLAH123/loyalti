<?php

namespace MetaFox\User\Repositories;

use MetaFox\Platform\Contracts\User as ContractUser;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\User\Models\UserProfile;

/**
 * Interface UserProfileRepositoryInterface.
 * @mixin BaseRepository
 * @method UserProfile getModel()
 */
interface UserProfileRepositoryInterface
{
    /**
     * @param  ContractUser         $context
     * @param  ContractUser         $user
     * @param  array<string, mixed> $attributes
     * @return void
     */
    public function checkUpdatePermission(ContractUser $context, ContractUser $user, array $attributes): void;
}
