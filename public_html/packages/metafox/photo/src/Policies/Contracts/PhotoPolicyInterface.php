<?php

namespace MetaFox\Photo\Policies\Contracts;

use MetaFox\Platform\Contracts\Content as Resource;
use MetaFox\Platform\Contracts\User;

interface PhotoPolicyInterface
{
    /**
     * @param User          $user
     * @param resource|null $resource
     *
     * @return bool
     */
    public function download(User $user, ?Resource $resource = null): bool;

    /**
     * @param User          $user
     * @param resource|null $resource
     *
     * @return bool
     */
    public function setProfileAvatar(User $user, ?Resource $resource = null): bool;

    /**
     * @param User          $user
     * @param resource|null $resource
     *
     * @return bool
     */
    public function setProfileCover(User $user, ?Resource $resource = null): bool;

    /**
     * @param User          $user
     * @param resource|null $resource
     *
     * @return bool
     */
    public function setParentCover(User $user, ?Resource $resource = null): bool;

    /**
     * @param User     $user
     * @param User     $friend
     * @param resource $resource
     *
     * @return bool
     */
    public function tagFriend(User $user, ?User $friend = null, ?Resource $resource = null): bool;
}
