<?php

namespace MetaFox\Group\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Group\Models\Request;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class RequestPolicy.
 * @ignore
 */
class RequestPolicy
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    protected string $type = Request::ENTITY_TYPE;

    public function approve(User $user, ?Entity $resource): bool
    {
        if (!$resource instanceof Request) {
            return false;
        }

        if ($resource->status_id != StatusScope::STATUS_PENDING) {
            return false;
        }

        return policy_check(GroupPolicy::class, 'managePendingRequestTab', $user, $resource->group);
    }
}
