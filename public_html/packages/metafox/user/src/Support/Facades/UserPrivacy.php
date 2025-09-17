<?php

namespace MetaFox\User\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserPrivacy.
 * @see     \MetaFox\User\Support\UserPrivacy
 * @method static bool hasAccess(User $user, User $owner, string $privacyName)
 * @method static array hasAccessProfileMenuSettings(User $user, User $owner)
 * @method static array hasAccessProfileSettings(User $user, User $owner)
 * @method static int|false getItemPrivacySetting(int $userId, string $privacyName)
 * @method static int|false getProfileSetting(int $userId, string $privacyName)
 * @method static int|false getProfileMenuSetting(int $userId, string $privacyName)
 * @method static getUserPrivacy(int $userId)
 * @method static bool validateProfileSettings(int $userId, array $params)
 * @method static bool validateProfileMenuSettings(int $userId, array $params)
 * @method static bool validateItemPrivacySettings(int $userId, array $params)
 * @mixin \MetaFox\User\Support\UserPrivacy
 */
class UserPrivacy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'UserPrivacy';
    }
}
