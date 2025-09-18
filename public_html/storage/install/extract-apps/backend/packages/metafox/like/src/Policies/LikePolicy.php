<?php

namespace MetaFox\Like\Policies;

use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\ActionOnResourcePolicyInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class LikePolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class LikePolicy implements ActionOnResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Like::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Like::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('like.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, ?Entity $resource): bool
    {
        // check user role permission
        if (!$user->hasPermissionTo('like.view')) {
            return false;
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof HasTotalLike) {
            return false;
        }

        return $this->likeItem($resource->entityType(), $user, $resource);
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if ($resource instanceof ActionEntity) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function likeItem(string $entityType, User $user, $resource, $newValue = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof HasTotalLike) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        // Check permission on Like app before checking with entity
        if (!$user->hasPermissionTo('like.create')) {
            return false;
        }

        if (!$this->checkPermissionOnItem($user, $resource)) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        if (!$owner->isApproved()) {
            return false;
        }

        if (app('events')->dispatch('like.owner.can_like_item', [$user, $owner], true)) {
            return true;
        }

        if ($owner->entityId() != $user->entityId()) {
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    protected function checkPermissionOnItem(User $user, Content $resource): bool
    {
        $resourceEntityType = $resource->entityType();
        $permissionName     = "$resourceEntityType.like";

        try {
            Permission::findByName($permissionName);

            return $user->hasPermissionTo($permissionName);
        } catch (\Exception $e) {
            $modelClass = Relation::getMorphedModel($resourceEntityType);

            if ($modelClass && class_exists($modelClass)) {
                $policy = PolicyGate::getPolicyFor($modelClass);

                if (is_object($policy) && method_exists($policy, 'like')) {
                    return $policy->like($user, $resource);
                }
            }
        }

        return true;
    }
}
