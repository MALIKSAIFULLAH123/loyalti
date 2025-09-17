<?php

namespace MetaFox\User\Policies\Contracts;

use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;

interface UserPolicyInterface extends ResourcePolicyInterface
{
    public function banUser(User $user, User $owner): bool;

    public function blockUser(User $user, ?User $owner = null): bool;

    public function unBlockUser(User $user, ?User $owner = null): bool;

    public function uploadAvatar(User $user, User $owner): bool;

    public function viewLocation(User $user, User $owner): bool;
}
