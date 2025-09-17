<?php

namespace MetaFox\Music\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Music\Models\Song;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class SongPolicy.
 * @SuppressWarnings(PHPMD)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SongPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('music_song.create')) {
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

                if (!PrivacyPolicy::checkCreateOnOwner($user, $owner)) {
                    return false;
                }

                if (!UserPrivacy::hasAccess($user, $owner, 'music.share_musics')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('music_song.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_song.view')) {
            return false;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function download(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Song) {
            return false;
        }

        if (!$resource->isApproved()) {
            return false;
        }

        if (!$this->view($user, $resource)) {
            return false;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        if (!$user->hasPermissionTo('music_song.download')) {
            return false;
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
        if ($user->hasPermissionTo('music_song.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_song.view')) {
            return false;
        }

        if (!$this->viewOwner($user, $owner)) {
            return false;
        }

        // Check can view on resource.
        if (PrivacyPolicy::checkPermission($user, $resource) == false) {
            return false;
        }

        if (!$resource->isPublished()) {
            if ($resource->userId() != $user->entityId()) {
                return false;
            }
        }

        // Check setting view on resource.
        if (!$resource->isApproved()) {
            if ($resource->userId() == $user->entityId()) {
                return true;
            }

            if ($user->hasPermissionTo('music_song.approve')) {
                return true;
            }

            if ($owner instanceof HasPrivacyMember) {
                return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
            }

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

        if (!UserPrivacy::hasAccess($user, $owner, 'music.view_browse_musics')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('music_song.moderate')) {
            return true;
        }

        return $this->updateOwn($user, $resource);
    }

    public function updateOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('music_song.update')) {
            return false;
        }

        if (!$resource instanceof Song) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('music_song.moderate')) {
            return true;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource->isApproved() && $user->hasPermissionTo('music_song.approve')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('music_song.delete')) {
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

    public function publish(User $user, ?Song $song): bool
    {
        if (!$song instanceof Song) {
            return false;
        }

        if ($song->isPublished()) {
            return false;
        }

        if ($user->hasPermissionTo('music_song.create')) {
            return true;
        }

        return false;
    }

    public function addToPlaylist(User $user, ?Song $song): bool
    {
        if (!$song instanceof Song) {
            return false;
        }

        if (!$song->isApproved()) {
            return false;
        }

        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_playlist.view')) {
            return false;
        }

        if (!$this->view($user, $song)) {
            return false;
        }

        return true;
    }

    public function removeFromPlaylist(User $user, ?Song $song, Playlist $playlist = null): bool
    {
        if (!$this->addToPlaylist($user, $song)) {
            return false;
        }

        if (null === $playlist) {
            return true;
        }

        if (policy_check(PlaylistPolicy::class, 'update', $user, $playlist)) {
            return true;
        }

        return false;
    }

    public function autoApprove(User $context, User $owner): bool
    {
        if (!$owner instanceof HasPrivacyMember) {
            return $context->hasPermissionTo('music_song.auto_approved');
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

        if (!UserPrivacy::hasAccess($user, $owner, 'music.view_browse_musics')) {
            return false;
        }

        return PolicyGate::check($owner, 'view', [$user, $owner]);
    }
}
