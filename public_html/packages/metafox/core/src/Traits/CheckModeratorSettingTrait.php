<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserValue;

/**
 * Trait CheckModeratorSettingTrait.
 */
trait CheckModeratorSettingTrait
{
    public function checkModeratorSetting(User $user, User $owner, string $settingName): bool
    {
        if ($owner instanceof HasPrivacyMember) {
            if ($user->hasSuperAdminRole()) {
                return true;
            }

            $isAdmin = $owner->isAdmin($user);

            $isModerator = $owner->isModerator($user);

            if ($isModerator && !$isAdmin) {
                if (false == UserValue::checkUserValueSettingByName($owner, $settingName)) {
                    return false;
                }

                return true;
            }

            return $isAdmin;
        }

        return true;
    }
}
