<?php

namespace MetaFox\Music\Policies;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Music\Models\Playlist;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class PlaylistPolicy.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use CheckModeratorSettingTrait;

    public function create(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_playlist.create')) {
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
            }
        }

        return true;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_playlist.view')) {
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
        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_playlist.view')) {
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

        if (!UserPrivacy::hasAccess($user, $owner, 'music.view_browse_musics')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        if (!$user->hasPermissionTo('music_playlist.update')) {
            return false;
        }

        if (!$resource instanceof Playlist) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasPermissionTo('music_playlist.moderate')) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('music_playlist.delete')) {
            return false;
        }

        if (!$resource instanceof Playlist) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }
}
