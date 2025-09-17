<?php

namespace MetaFox\Activity\Policies;

use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Post as Model;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

class PostPolicy implements
    ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }
        if (!$user->hasPermissionTo('feed.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('feed.view')) {
            return false;
        }

        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
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

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('feed.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                // Check can view on owner.
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return $user->hasPermissionTo('feed.update');
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('feed.moderate')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('feed.delete')) {
            return false;
        }

        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function like(User $user, ?Content $resource = null): bool
    {
        if (!app_active('metafox/like')) {
            return false;
        }

        /**
         * @todo Need to refactor the like/comment permission checking process between like/comment app and other apps
         *
         * Don't use event like.can_like for checking like permission because of looping issue
         */
        if (!$user->hasPermissionTo('like.create')) {
            return false;
        }

        $typeManager = resolve(TypeManager::class);

        if (!$typeManager->hasFeature(Model::ENTITY_TYPE, 'can_like')) {
            return false;
        }

        if (!$resource instanceof HasTotalLike) {
            return false;
        }

        $owner = $resource->owner;

        if ($owner instanceof User) {
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }

            if (false === app('events')->dispatch('like.owner.can_like_item', [$user, $owner], true)) {
                return false;
            }
        }

        return true;
    }

    public function share(User $user, ?Content $resource = null): bool
    {
        if ($resource instanceof Model && $resource->privacy == MetaFoxPrivacy::EVERYONE) {
            $typeManager = resolve(TypeManager::class);

            if (!$typeManager->hasFeature(Model::ENTITY_TYPE, 'can_share')) {
                return false;
            }

            if (!$resource instanceof HasTotalShare) {
                return false;
            }

            $owner = $resource->owner;

            if ($owner instanceof User) {
                if (!$this->checkCreateOnOwner($user, $owner)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function comment(User $user, ?Content $resource = null): bool
    {
        if (!app_active('metafox/comment')) {
            return false;
        }

        if (!$user->hasPermissionTo('feed.comment')) {
            return false;
        }

        if ($resource instanceof Model) {
            $typeManager = resolve(TypeManager::class);

            if (!$typeManager->hasFeature(Model::ENTITY_TYPE, 'can_comment')) {
                return false;
            }

            if (!$resource instanceof HasTotalComment) {
                return false;
            }

            $owner = $resource->owner;

            if ($owner instanceof User) {
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private function checkCreateOnOwner(User $user, User $owner): bool
    {
        if ($owner->entityId() != $user->entityId()) {
            if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function approve(User $context, ?Content $content = null): bool
    {
        if (!$content instanceof Content) {
            return false;
        }

        if ($context->hasPermissionTo('feed.moderate')) {
            return true;
        }

        if ($context->hasPermissionTo($content->entityType() . '.approve')) {
            return true;
        }

        $owner = $content->owner;

        if ($owner instanceof User) {
            if ($owner instanceof HasPrivacyMember) {
                return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
            }

            if ($context->can('update', [$owner, $owner])) {
                return true;
            }
        }

        return false;
    }
}
