<?php

namespace MetaFox\Saved\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

/**
 * Class CanSaveItem.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class CanSaveItem implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof HasSavedItem) {
            return false;
        }

        if (!$user->hasPermissionTo('saved.create')) {
            return false;
        }

        if ($resource instanceof Content) {
            if (!$resource->isApproved()) {
                return false;
            }

            if ($resource->force_save_item) {
                return true;
            }
        }

        return $user->hasPermissionTo("$entityType.save");
    }
}
