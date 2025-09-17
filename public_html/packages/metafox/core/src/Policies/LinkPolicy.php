<?php

namespace MetaFox\Core\Policies;

use MetaFox\Core\Models\Link as Model;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

class LinkPolicy implements
    ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
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

        if ($this->viewOwner($user, $owner) == false) {
            return false;
        }

        // Check can view on resource.
        if (PrivacyPolicy::checkPermission($user, $resource) == false) {
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
        if ($resource instanceof Model) {
            if (false === app('events')->dispatch('activity.has_feature', [$resource->entityType(), 'can_like'], true)) {
                return false;
            }

            if (!$resource instanceof HasTotalLike) {
                return false;
            }

            $owner = $resource->owner;

            if ($owner->entityId() != $user->entityId()) {
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function share(User $user, ?Content $resource = null): bool
    {
        if (false === app('events')->dispatch('activity.has_feature', [$resource->entityType(), 'can_share'], true)) {
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

    public function comment(User $user, ?Content $resource = null): bool
    {
        if (app('events')->dispatch('activity.has_feature', [$resource->entityType(), 'action_on_feed'], true)) {
            $activityFeed = $resource->activity_feed;

            if ($activityFeed instanceof Content && !$user->hasPermissionTo("{$activityFeed->entityId()}.comment")) {
                return false;
            }
        }

        if ($resource instanceof Model) {
            if (false === app('events')->dispatch('activity.has_feature', [$resource->entityType(), 'can_comment'], true)) {
                return false;
            }

            if (!$resource instanceof HasTotalComment) {
                return false;
            }

            $owner = $resource->owner;

            if ($owner->entityId() != $user->entityId()) {
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

    public function hasCreateFeed(User $owner, string $postType): bool
    {
        $allowSetting = !$owner instanceof HasPrivacyMember || !$owner->hasPendingMode();

        $setting = app('events')->dispatch('activity.has_feature', [$postType, 'can_create_feed'], true);

        if ($allowSetting && !$setting) {
            return false;
        }

        return true;
    }
}
