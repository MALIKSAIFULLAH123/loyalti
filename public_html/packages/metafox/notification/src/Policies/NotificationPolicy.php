<?php

namespace MetaFox\Notification\Policies;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class NotificationPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = 'notification';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('notification.view');
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        return false;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('notification.moderate');
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('notification.deleteOwn');
    }
}
