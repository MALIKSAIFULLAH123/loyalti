<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as SystemRequest;
use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\SEO\ActionMeta;
use MetaFox\User\Contracts\UserContract;
use MetaFox\User\Models\User as ModelUser;
use MetaFox\User\Models\UserGender;
use MetaFox\User\Models\UserProfile;

/**
 * Class User.
 * @see \MetaFox\User\Support\User
 * @method static bool                             isFollowing(ContractUser $context, ContractUser $user)
 * @method static string                           totalFollowers(ContractUser $user)
 * @method static bool                             isBan(int $userId)
 * @method static int                              getFriendship(ContractUser $user, ContractUser $targetUser)
 * @method static string|null                      getGender(UserProfile $profile)
 * @method static string                           getLastName(?string $name)
 * @method static string                           getFirstName(?string $name)
 * @method static string                           getShortName(?string $name)
 * @method static string                           getSummary(ContractUser $context, ?ContractUser $user)
 * @method static string                           getAddress(ContractUser $context, ContractUser $user)
 * @method static Authenticatable                  getGuestUser()
 * @method static array                            getTimeZoneForForm()
 * @method static string|null                      getTimeZoneNameById(int $id)
 * @method static Builder[]|Collection             getUsersByRoleId(int $roleId)
 * @method static int[]                            getMentions(string $content)
 * @method static string                           getPossessiveGender(?UserGender $gender)
 * @method static bool                             updateLastLogin(ContractUser $context)
 * @method static bool                             updateLastActivity(ContractUser $context)
 * @method static ModelUser                        updateInvisibleMode(ContractUser $context, int $isInvisible)
 * @method static array           getNotificationSettingsByChannel(ContractUser $context, string $channel)
 * @method static bool            updateNotificationSettingsByChannel(ContractUser $context, array $attributes)
 * @method static array           hasPendingSubscription(SystemRequest $request, ContractUser $user, bool $isMobile)
 * @method static int|null                         getUserAge(?string $birthday)
 * @method static array<string, mixed>             getVideoSettings(ContractUser $user)
 * @method static array<int, mixed>                getRoleOptionsForSearchMembers()
 * @method static array                            getAllowApiRules()
 * @method static array                            getLogoutOptions()
 * @method static Model|null                       getPointStatistic(ContractUser $user)
 * @method static ActionMeta                       getActionMetaLogoutOtherDevices()
 * @method static array<int, array<string, mixed>> getThemeTypeOptions()
 * @method static mixed                getReferenceValueByName(ContractUser $user, string $name)
 * @method static array                getReferenceValueByNames(ContractUser $user, array $names)
 * @method static array                allowedPropertiesExport(ContractUser $user)
 * @method static array                getPropertiesCustomField(ContractUser $user)
 */
class User extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserContract::class;
    }
}
