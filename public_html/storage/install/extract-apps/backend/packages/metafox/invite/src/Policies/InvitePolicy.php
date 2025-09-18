<?php

namespace MetaFox\Invite\Policies;

use MetaFox\Invite\Models\Invite;
use MetaFox\Platform\Contracts\ActionOnResourcePolicyInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class InvitePolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class InvitePolicy implements ActionOnResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Invite::ENTITY_TYPE;

    public function getEntityType(): string
    {
        return Invite::ENTITY_TYPE;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if (!$owner instanceof User) {
            return false;
        }

        return $user->entityId() == $owner->entityId();
    }

    public function view(User $user, ?Entity $resource): bool
    {
        if (!$resource instanceof Invite) {
            return false;
        }

        return $resource->isUser($user);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?Content $resource = null): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (UserFacade::isBan($user->entityId())) {
            return false;
        }

        if ($user->isDeleted()) {
            return false;
        }

        return $user->hasPermissionTo('invite.create');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Invite) {
            return false;
        }

        return $resource->isUser($user);
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Invite) {
            return false;
        }

        return $resource->isUser($user);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return false;
    }
}
