<?php

namespace MetaFox\Video\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Video\Models\Video;

/**
 * Class VideoPolicy.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 */
class VideoPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('video.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('video.view')) {
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
        if (!$resource instanceof Video) {
            return false;
        }

        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        /*
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('video.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('video.view')) {
            return false;
        }

        $owner = $resource->owner;

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (!PrivacyPolicy::checkPermission($user, $resource)) {
            return false;
        }

        if ($isApproved) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            if ($this->checkModeratorSetting($user, $owner, 'approve_or_deny_post')) {
                return true;
            }

            if (!$resource->isUser($user)) {
                return false;
            }
        }

        if ($user->hasPermissionTo('video.approve')) {
            return true;
        }

        return $resource->isUser($user);
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

        if (UserPrivacy::hasAccess($user, $owner, 'video.view_browse_videos') == false) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('video.create')) {
            return false;
        }

        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                // Check can view on owner.
                if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'video.share_videos')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function uploadVideoFile(User $user, ?User $owner = null): bool
    {
        if (!Settings::get('video.enable_video_uploads', true)) {
            return false;
        }

        return $user->hasPermissionTo('video.upload_video_file');
    }

    public function shareVideoUrl(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('video.share_video_url');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('video.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Content $resource = null): bool
    {
        if (!$user->hasPermissionTo('video.update')) {
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
        if ($user->hasPermissionTo('video.moderate')) {
            return true;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('video.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('video.delete')) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        $owner = $resource->owner;

        if (!$owner instanceof HasPrivacyMember) {
            return $user->entityId() == $resource->userId();
        }

        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    public function uploadToAlbum(User $context, ?User $owner): bool
    {
        if (null === $owner) {
            return false;
        }

        if (!$this->create($context, $owner)) {
            return false;
        }

        if (!$this->uploadVideoFile($context, $owner)) {
            return false;
        }

        if ($owner instanceof HasPrivacyMember) {
            return UserPrivacy::hasAccess($context, $owner, 'video.share_videos');
        }

        return true;
    }

    public function uploadWithPhoto(User $context, User $owner): bool
    {
        if (!$this->create($context, $owner)) {
            return false;
        }

        if (!$this->uploadVideoFile($context, $owner)) {
            return false;
        }

        if ($owner instanceof HasPrivacyMember) {
            return UserPrivacy::hasAccess($context, $owner, 'video.share_videos');
        }

        return true;
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('video.auto_approved');
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

        if (!UserPrivacy::hasAccess($user, $owner, 'video.view_browse_videos')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }

    public function approveByOwner(User $context, User $owner): bool
    {
        if ($context->hasPermissionTo('video.moderate')) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        if ($context->entityId() == $owner->entityId()) {
            return true;
        }

        return $context->hasPermissionTo('video.approve');
    }

    public function viewMatureContent(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Video) {
            return false;
        }

        if ($user->hasPermissionTo('video.moderate')) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if (!$resource->isMatureContent()) {
            return true;
        }

        $age      = UserFacade::getUserAge($user->profile?->birthday);
        $ageLimit = $user->getPermissionValue('video.mature_video_age_limit');

        return $age >= $ageLimit;
    }

    public function updateMature(User $user, ?Content $resource = null): bool
    {
        if (!$this->update($user, $resource)) {
            return false;
        }

        return true;
    }

    public function updateAlbum(User $user, ?Content $resource = null): bool
    {
        if (!$resource instanceof Video) {
            return false;
        }

        if (!$this->update($user, $resource)) {
            return false;
        }

        if (!isset($resource->module_id) || $resource->module_id != 'photo') {
            return false;
        }

        return true;
    }
}
