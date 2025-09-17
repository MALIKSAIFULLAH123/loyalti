<?php

namespace MetaFox\Photo\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Support\Facades\Album as FacadesAlbum;
use MetaFox\Platform\Contracts\Content as Resource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ActionPolicyInterface;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

class AlbumPolicy implements ResourcePolicyInterface, ActionPolicyInterface
{
    use CheckModeratorSettingTrait;
    use HasPolicyTrait;

    protected string $type = Album::class;

    public function getEntityType(): string
    {
        return Album::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('photo_album.view')) {
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
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return false;
        }

        /**
         * The 'moderator' permission of the item should be checked before checking the privacy setting of the item on the owner (User/Page/Group).
         */
        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

        // check user role permission
        if (!$user->hasPermissionTo('photo_album.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
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

        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        if (UserPrivacy::hasAccess($user, $owner, 'photo.view_browse_photos') == false) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('photo_album.create')) {
            return false;
        }

        if ($owner) {
            // Check can view on owner.
            if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
                return false;
            }

            if (!UserPrivacy::hasAccess($user, $owner, 'photo_album.share_albums')) {
                return false;
            }
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Resource $resource = null): bool
    {
        if (!$user->hasPermissionTo('photo_album.update')) {
            return false;
        }

        if (!$resource instanceof Resource) {
            return true;
        }

        if ($resource->userId() != $user->entityId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Album) {
            return false;
        }

        if (FacadesAlbum::isDefaultAlbum($resource->album_type)) {
            return false;
        }

        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('photo_album.delete')) {
            return false;
        }

        if (!$resource instanceof Resource) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        //Other User, Page, Group, Event,...
        $owner = $resource->owner;
        if (!$owner instanceof HasPrivacyMember) {
            //Page, Event,...
            return $user->entityId() == $resource->userId();
        }

        //Group,...
        if (!$resource->isApproved()) {
            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $this->checkModeratorSetting($user, $owner, 'remove_post_and_comment_on_post');
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        if ($user->hasPermissionTo('photo_album.moderate')) {
            return true;
        }

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

    public function uploadMedias(User $user, ?Resource $resource): bool
    {
        if (!$resource instanceof Album) {
            return false;
        }

        if ($resource->album_type !== Album::NORMAL_ALBUM) {
            return false;
        }

        if (!$user->hasPermissionTo('photo.create')) {
            return false;
        }

        $owner = $resource->owner;

        if (null === $owner) {
            return false;
        }

        if (!$owner instanceof HasPrivacyMember) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }

            return $this->update($user, $resource);
        }

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$user, $owner, Photo::ENTITY_TYPE], true)) {
            return false;
        }

        return true;
    }
}
