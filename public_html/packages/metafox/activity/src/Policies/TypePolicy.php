<?php

namespace MetaFox\Activity\Policies;

use MetaFox\Activity\Models\Type;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class TypePolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TypePolicy
{
    use HasPolicyTrait;

    protected string $type = 'activity_type';

    public function update(User $user, Type $type): bool
    {
        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Type $type): bool
    {
        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        if ($type->is_system === true) {
            return false;
        }

        return true;
    }
}
