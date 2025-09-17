<?php

namespace MetaFox\Platform\Traits\Policy\Actions;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User as User;

/**
 * Trait HidePolicyTrait
 * @package MetaFox\Platform\Traits\Policy\Actions
 */
trait HidePolicyTrait
{
    public function hide(User $user, ?Content $resource = null): bool
    {
        if ($resource instanceof Content) {
            if ($resource->userId() == $user->entityId()) {
                return false;
            }
        }

        return $user->hasPermissionTo("{$this->getEntityType()}.hide");
    }
}
