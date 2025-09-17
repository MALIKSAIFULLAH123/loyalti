<?php

namespace MetaFox\Platform\Contracts;

use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;

/**
 * Interface ActionOnResourcePolicyInterface.
 */
interface ActionOnResourcePolicyInterface extends ResourcePolicyInterface
{
    public function create(User $user, ?Content $owner = null): bool;

    public function update(User $user, ?Entity $resource = null): bool;

    public function delete(User $user, ?Entity $resource = null): bool;

    public function deleteOwn(User $user, ?Entity $resource = null): bool;
}
