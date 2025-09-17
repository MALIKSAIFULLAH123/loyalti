<?php

namespace MetaFox\Hashtag\Policies;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class HashtagPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class HashtagPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = 'hashtag';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, Entity $resource): bool
    {
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }
}
