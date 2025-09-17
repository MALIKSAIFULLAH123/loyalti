<?php

namespace MetaFox\Story\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Story\Models\Story;

/**
 * Class StoryPolicy.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('story.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('story.view')) {
            return false;
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        if ($user->hasPermissionTo('story.moderate')) {
            return true;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }

        if ($user->hasPermissionTo('story.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('story.view')) {
            return false;
        }

        if ($resource->is_archive) {
            return $this->viewArchive($user, $resource->user);
        }

        if (!$this->viewOwner($user, $resource->user)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('story.create')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('story.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('story.update')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('story.moderate')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }
        if (!$user->hasPermissionTo('story.delete')) {
            return false;
        }

        if (!$resource->isUser($user)) {
            return false;
        }

        return true;
    }

    public function autoApprove(User $context, User $owner): bool
    {
        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return true;
    }

    public function viewViewer(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }

        if (!$user->hasPermissionTo('story.view')) {
            return false;
        }

        return $resource->isOwner($user);
    }

    public function viewArchive(User $user, User $owner): bool
    {
        if (!$user->hasPermissionTo('story.view')) {
            return false;
        }

        return $user->entityId() == $owner->entityId();
    }

    public function comment(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }

        if (!app('events')->dispatch('comment.can_comment', [$resource->entityType(), $user, $resource], true)) {
            return false;
        }

        if ($resource->isArchived()) {
            return false;
        }

        return true;
    }

    public function like(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }

        if (!app('events')->dispatch('like.can_like', [$resource->entityType(), $user, $resource], true)) {
            return false;
        }

        if ($resource->isArchived()) {
            return false;
        }

        return true;
    }

    public function mute(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Story) {
            return false;
        }

        if (!app('events')->dispatch('like.can_like', [$resource->entityType(), $user, $resource], true)) {
            return false;
        }

        if ($resource->isArchived()) {
            return false;
        }

        return true;
    }
}
