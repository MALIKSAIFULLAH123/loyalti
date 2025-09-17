<?php

namespace MetaFox\Like\Policies;

use MetaFox\Like\Models\Reaction as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class ReactionPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class ReactionPolicy
{
    use HasPolicyTrait;

    protected string $type = Model::ENTITY_TYPE;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return policy_check(LikePolicy::class, 'viewAny', $user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user, $resource =  null): bool
    {
        return policy_check(LikePolicy::class, 'view', $user, $resource);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Model $model): bool
    {
        if (null == $model) {
            return false;
        }

        if ($model->is_default) {
            return false;
        }

        if (!$user->hasPermissionTo('admincp.has_admin_access')) {
            return false;
        }

        return true;
    }
}
