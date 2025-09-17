<?php

namespace MetaFox\Saved\Policies;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;

/**
 * Class SavedListPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = SavedList::class;

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource = null): bool
    {
        if (!$resource instanceof SavedList) {
            return false;
        }

        if ($this->viewMember($user, $resource)) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    /**
     * @inerhitDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user, ?User $owner = null): bool
    {
        if (!$user->hasPermissionTo('saved_list.create')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($this->deleteOwn($user, $resource) == false) {
            return false;
        }

        return $user->hasPermissionTo('saved_list.update');
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($this->deleteOwn($user, $resource) == false) {
            return false;
        }

        return $user->hasPermissionTo('saved_list.delete');
    }

    /**
     * @inerhitDoc
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof SavedList) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function viewMember(User $user, ?Entity $resource = null): bool
    {
        if ($user->entityId() != $resource->userId()) {
            /** @var SavedListMemberRepositoryInterface $memberRepository */
            $memberRepository = resolve(SavedListMemberRepositoryInterface::class);

            if (!$memberRepository->isSavedListMember($user, $resource->entityId())) {
                return false;
            }
        }

        return true;
    }

    public function removeMember(User $user, ?Content $collection, ?int $memberId): bool
    {
        if ($memberId == $collection->userId()) {
            return false;
        }

        if ($user->hasSuperAdminRole() || $user->hasAdminRole()) {
            return true;
        }

        return $user->entityId() == $collection->userId();
    }

    public function leaveCollection(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof SavedList) {
            return false;
        }

        if ($resource->userId() == $user->entityId()) {
            return false;
        }

        return true;
    }

    public function addFriend(User $user, ?Entity $resource = null): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if (!$resource instanceof SavedList) {
            return false;
        }

        if (!$user->hasPermissionTo('saved_list.update')) {
            return false;
        }

        return $resource->userId() == $user->entityId();
    }
}
