<?php

namespace MetaFox\Platform\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\PrivacyPolicy as PolicyClass;
use MetaFox\Platform\Contracts\User;

/**
 * Class PrivacyPolicy.
 * @method static bool  checkPermission(?User $user, Content $content)
 * @method static bool  checkPermissionOwner(?User $user, User $owner)
 * @method static bool  checkCreateOnOwner(User $user, ?User $owner)
 * @method static bool  checkCreateResourceOnOwner(Content $content)
 * @method static bool  hasAbilityOnOwner(User $user, User $owner, int $privacy, string $privacyType = null)
 * @method static int[] getResourcePrivacyList(Content $content)
 * @method static array getPrivacyItem(Content $content)
 * @method static bool  checkItemPrivacy(User $user, User $owner, HasPrivacy $content, ?array $privacyIds = null)
 * @method static bool  checkBlockUser(User $user, User $owner)
 */
class PrivacyPolicy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PolicyClass::class;
    }
}
