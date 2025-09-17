<?php

namespace MetaFox\User\Policies;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class UserShortcutPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserShortcutPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = 'shortcut';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('shortcut.view')) {
            return false;
        }

        return true;
    }

    public function moderate(User $user): bool
    {
        if (!$user->hasPermissionTo('shortcut.moderate')) {
            return false;
        }

        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return true;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return true;
    }
}
