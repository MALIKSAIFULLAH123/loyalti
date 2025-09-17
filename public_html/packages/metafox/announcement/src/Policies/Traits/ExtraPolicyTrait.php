<?php

namespace MetaFox\Announcement\Policies\Traits;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Trait ExtraPolicyTrait.
 * @ignore
 * @codeCoverageIgnore
 */
trait ExtraPolicyTrait
{
    /**
     * @param  User    $user
     * @param  Content $resource
     * @return bool
     */
    public function hide(User $user, Content $resource): bool
    {
        if (!$user->hasPermissionTo('announcement.hide')) {
            return false;
        }

        if ($resource instanceof Announcement) {
            if (!$resource->can_be_closed) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  User         $user
     * @param  Content|null $resource
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function moderate(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('announcement.moderate')) {
            return false;
        }

        return true;
    }
}
