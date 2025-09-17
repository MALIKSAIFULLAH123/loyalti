<?php

namespace MetaFox\Photo\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Policies\Contracts\PhotoPolicyInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Content as Resource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * @SuppressWarnings(PHPMD)
 */
class PhotoPolicy implements
    ResourcePolicyInterface,
    PhotoPolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('photo.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('photo.view')) {
            return false;
        }

        if ($owner) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$resource instanceof Photo) {
            return false;
        }
        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        $owner = $resource->owner;

        /**
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('photo.moderate')) {
            return true;
        }

        // Check user role + permission.
        if (!$user->hasPermissionTo('photo.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

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

        // Check setting view on resource.
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

        if ($user->hasPermissionTo('photo.approve')) {
            return true;
        }

        return $user->entityId() == $resource->userId();
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

        if (!UserPrivacy::hasAccess($user, $owner, 'photo.view_browse_photos')) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('photo.create')) {
            return false;
        }

        if ($owner) {
            // Check can view on owner.
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }

            if (!UserPrivacy::hasAccess($user, $owner, 'photo.share_photos')) {
                return false;
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('photo.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Resource $resource = null): bool
    {
        if (!$user->hasPermissionTo('photo.update')) {
            return false;
        }

        if (!$resource instanceof Resource) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('photo.moderate')) {
            return true;
        }

        if (!$resource instanceof Resource) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('photo.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('photo.delete')) {
            return false;
        }

        if (!$resource instanceof Resource) {
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

    public function setProfileAvatar(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Photo) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        $owner = $resource->owner;

        $isResourceUser = $user->entityId() == $resource->userId();
        if ($user->entityId() != $owner?->userId()) {
            return $isResourceUser;
        }

        if (!$isResourceUser) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.set_profile_avatar')) {
            return false;
        }

        $policy = PolicyGate::getPolicyFor(get_class($owner));

        if (!$policy instanceof ResourcePolicyInterface) {
            return false;
        }

        if ($resource->isMatureContent()) {
            return false;
        }

        return $policy->update($user, $owner);
    }

    public function saveItem(User $user, Content $resource = null): bool
    {
        if (!$resource instanceof HasSavedItem) {
            return false;
        }

        if (!$resource instanceof Photo) {
            return false;
        }

        if (!$user->hasPermissionTo('saved.create')) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($resource->force_save_item ?? null) {
            return true;
        }

        if ($resource->isStrictMatureContent()) {
            return false;
        }

        return $user->hasPermissionTo('photo.save');
    }

    public function setProfileCover(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Photo) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        $owner = $resource->owner;

        $isResourceUser = $user->entityId() == $resource->userId();
        if ($user->entityId() != $owner?->userId()) {
            return $isResourceUser;
        }

        if (!$isResourceUser) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.set_profile_cover')) {
            return false;
        }

        if ($resource->isMatureContent()) {
            return false;
        }

        return true;
    }

    public function setParentAvatar(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        $owner = $resource->owner;
        if (!$owner instanceof HasPrivacyMember) {
            return false;
        }

        if (!$owner instanceof HasAvatarMorph) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (!$user->hasPermissionTo('photo.set_profile_avatar')) {
            return false;
        }

        $policy = PolicyGate::getPolicyFor(get_class($owner));

        if (!$policy instanceof ResourcePolicyInterface) {
            return false;
        }

        return $policy->update($user, $owner);
    }

    public function setParentCover(User $user, ?Resource $resource = null): bool
    {
        if ($resource instanceof Content) {
            if (!$resource->isApproved()) {
                return false;
            }

            $owner = $resource->owner;
            if (!$owner instanceof HasPrivacyMember) {
                return false;
            }

            if (!$owner instanceof HasCoverMorph) {
                return false;
            }

            if ($user->hasSuperAdminRole()) {
                return true;
            }

            if (!$user->hasPermissionTo('photo.set_profile_cover')) {
                return false;
            }

            $policy = PolicyGate::getPolicyFor(get_class($owner));

            if (!$policy instanceof ResourcePolicyInterface) {
                return false;
            }

            return $policy->update($user, $owner);
        }

        return true;
    }

    public function removeProfileCoverOrAvatar(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Resource) {
            return false;
        }
        $owner = UserEntity::getById($resource->ownerId())->detail;

        if ($user->hasPermissionTo("{$owner->entityType()}.moderate")) {
            return true;
        }

        if ($user->entityId() != $owner->userId()) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        return true;
    }

    public function download(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof Photo) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if ($resource->isStrictMatureContent()) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.download')) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$this->view($user, $resource)) {
            return false;
        }

        return true;
    }

    public function viewMature(User $user, ?Resource $resource = null): bool
    {
        if (!$resource instanceof Photo) {
            return false;
        }

        if ($user->hasPermissionTo('photo.moderate')) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if (!$resource->isMatureContent()) {
            return true;
        }

        $age      = UserFacade::getUserAge($user->profile?->birthday);
        $ageLimit = $user->getPermissionValue('photo.mature_photo_age_limit');

        return $age >= $ageLimit;
    }

    public function updateMature(User $user, ?Resource $resource = null): bool
    {
        if (!$this->update($user, $resource)) {
            return false;
        }

        if ($resource->is_cover_photo) {
            return false;
        }

        if ($resource->is_profile_photo) {
            return false;
        }

        return true;
    }

    public function tagFriend(User $user, ?User $friend = null, ?Resource $resource = null): bool
    {
        if ($friend instanceof User) {
            if (!$this->viewOwner($friend, $user)) {
                return false;
            }
        }

        if ($resource instanceof Resource && !$resource->isApproved()) {
            return false;
        }

        if ($resource instanceof Photo) {
            if ($resource->isMatureContent()) {
                return false;
            }

            if ($resource->owner && false === app('events')->dispatch('core.can_tag_friend', [$resource->owner, $friend], true)) {
                return false;
            }
        }

        if ($user->hasPermissionTo('photo.tag_friend_any')) {
            return true;
        }

        if (!$user->hasPermissionTo('photo.tag_friend')) {
            return false;
        }

        if ($resource != null && $resource->userId() != $user->entityId()) {
            return false;
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if (!UserPrivacy::hasAccess($user, $owner, 'profile.view_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'photo.display_on_profile')) {
            return false;
        }

        if (!UserPrivacy::hasAccess($user, $owner, 'photo.view_browse_photos')) {
            return false;
        }

        return true;
    }

    public function updateAlbum(User $context, ?Resource $content): bool
    {
        if (!$content instanceof Photo) {
            return false;
        }

        if (!$this->update($context, $content)) {
            return false;
        }

        $photoGroup = $content->group;
        // Case: this photo should be an avatar/cover image
        if (!$photoGroup instanceof PhotoGroup) {
            return false;
        }

        $album = $content->album;

        if (!$album instanceof Album) {
            return true;
        }

        return $album->is_normal;
    }

    public function uploadToAlbum(User $context, ?User $owner, ?int $albumId = null): bool
    {
        if (null === $owner) {
            return false;
        }

        if (!$context->hasPermissionTo('photo.create')) {
            return false;
        }

        if (!PrivacyPolicy::checkPermissionOwner($context, $owner)) {
            return false;
        }

        if ($owner instanceof HasPrivacyMember) {
            return UserPrivacy::hasAccess($context, $owner, 'photo.share_photos');
        }

        if ($albumId == null) {
            return true;
        }
        $album = app('events')->dispatch('photo.album.get_by_id', [$albumId], true);

        if ($album?->is_timeline) {
            return true;
        }

        if ($owner->entityId() == $context->entityId()) {
            return true;
        }

        return false;
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('photo.auto_approved');
        }

        if (!$owner->hasPendingMode()) {
            return true;
        }

        if ($owner->isPendingMode()) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        return true;
    }

    public function approveByOwner(User $context, User $owner): bool
    {
        if ($context->hasPermissionTo('photo.moderate')) {
            return true;
        }

        if ($owner instanceof HasPrivacyMember) {
            return $this->checkModeratorSetting($context, $owner, 'approve_or_deny_post');
        }

        if ($context->entityId() == $owner->entityId()) {
            return true;
        }

        return $context->hasPermissionTo('photo.approve');
    }
}
