<?php

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanViewApproveListing implements PolicyRuleInterface
{
    use CheckModeratorSettingTrait;

    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        /**
         * @var User $resource
         */
        if ($resource instanceof HasPrivacyMember) {
            if (!$resource instanceof User) {
                return false;
            }

            if (!$resource->hasPendingMode()) {
                return false;
            }

            if ($user->entityId() == $resource->entityId()) {
                return true;
            }

            return $this->checkModeratorSetting($user, $resource, 'approve_or_deny_post');
        }

        if ($user->isGuest()) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.approve")) {
            return false;
        }

        return true;
    }
}
