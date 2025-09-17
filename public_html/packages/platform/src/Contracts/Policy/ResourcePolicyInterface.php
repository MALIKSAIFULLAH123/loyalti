<?php

namespace MetaFox\Platform\Contracts\Policy;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * Interface ResourcePolicyInterface
 */
interface ResourcePolicyInterface
{
    public function viewAny(User $user, ?User $owner = null): bool;

    public function view(User $user, Entity $resource): bool;

    public function viewOwner(User $user, ?User $owner = null): bool;

    public function create(User $user, ?User $owner = null): bool;

    public function update(User $user, ?Entity $resource = null): bool;

    public function delete(User $user, ?Entity $resource = null): bool;

    public function deleteOwn(User $user, ?Entity $resource = null): bool;

    public function viewOnProfilePage(User $user, User $owner): bool;
}
