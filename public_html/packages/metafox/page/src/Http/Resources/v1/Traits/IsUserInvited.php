<?php

namespace MetaFox\Page\Http\Resources\v1\Traits;

use MetaFox\Page\Models\Page as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Trait IsUserInvited.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
trait IsUserInvited
{
    /**
     * @param  User $context
     * @return bool
     */
    protected function isUserInvited(User $context): bool
    {
        return (bool) LoadReduce::get(
            sprintf('user::isInvited(user:%s,owner:%s)', $context->id, $this->resource->entityId()),
            function () use ($context) {
                if ($this->resource->invites->isEmpty()) {
                    return false;
                }

                $invitedUsers = $this->resource->invites->pluck('owner_id')->toArray();

                return in_array($context->entityId(), $invitedUsers);
            }
        );
    }
}
