<?php

namespace MetaFox\LiveStreaming\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class LiveVideoPolicy.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class LiveVideoPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('live_video.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('live_video.view')) {
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
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        if ($resource->is_streaming && $resource->isUser($user)) {
            return true;
        }

        /*
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('live_video.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('live_video.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (PrivacyPolicy::checkPermission($user, $resource) == false) {
            return false;
        }

        if (!$resource instanceof HasApprove) {
            return true;
        }

        if ($resource->isApproved()) {
            return true;
        }

        if ($user->hasPermissionTo('live_video.approve')) {
            return true;
        }

        if ($resource->isUser($user)) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            if ($this->checkModeratorSetting($user, $owner, 'approve_or_deny_post')) {
                return true;
            }
        }

        return false;
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

        if (UserPrivacy::hasAccess($user, $owner, 'live_video.view_browse_live_videos') == false) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('live_video.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if ($owner->entityType() == 'user') {
                    return false;
                }

                // Check can view on owner.
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'live_video.share_live_video')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('live_video.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function manageLiveVideo(User $user, ?Entity $resource = null): bool
    {
        if (!$resource) {
            return false;
        }

        if (!$resource->is_streaming) {
            return false;
        }

        if ($resource->userId() != $user->entityId()) {
            return false;
        }

        return true; // Owner always has permission to access dashboard
    }

    public function updateOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('live_video.update')) {
            return false;
        }

        if (null === $resource) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('live_video.moderate')) {
            return true;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('live_video.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('live_video.delete')) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        $owner = $resource->owner;

        if ($owner instanceof HasPrivacyMember) {
            if (!$resource->isApproved()) {
                return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
            }

            return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
        }

        return false;
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('live_video.auto_approved');
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'live_video.view_browse_live_videos')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }
}
