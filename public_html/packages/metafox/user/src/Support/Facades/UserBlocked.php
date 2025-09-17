<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Contracts\UserBlockedSupportContract;
use MetaFox\User\Models\UserEntity;

/**
 * Class UserBlocked.
 *
 * @method static bool       isBlocked(ContractUser $user, ContractUser $owner)
 * @method static bool       blockUser(ContractUser $user, ContractUser $owner)
 * @method static bool       unBlockUser(ContractUser $user, ContractUser $owner)
 * @method static array      getBlockedUsers(ContractUser $user)
 * @method static array      getBlockedUserIds(ContractUser $user)
 * @method static Collection getBlockedUsersCollection(ContractUser $user, string $search)
 * @method static UserEntity getBlockUserDetail(ContractUser $user, ContractUser $owner)
 * @method static void       clearCache(int $userId)
 *
 * @see UserBlockedSupportContract
 */
class UserBlocked extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserBlockedSupportContract::class;
    }
}
