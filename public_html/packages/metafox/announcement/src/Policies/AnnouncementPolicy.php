<?php

namespace MetaFox\Announcement\Policies;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Policies\Traits\ExtraPolicyTrait;
use MetaFox\Announcement\Repositories\AnnouncementCloseRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class AnnouncementPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use ExtraPolicyTrait;

    protected string $type = 'announcement';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('announcement.view');
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        return $user->hasPermissionTo('announcement.view');
    }

    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function create(User $user, ?User $owner = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return $user->hasPermissionTo('admincp.has_admin_access');
    }

    public function markAsRead(User $user, Announcement $resource): bool
    {
        return $user->hasPermissionTo('announcement.view');
    }

    public function close(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('announcement.close')) {
            return false;
        }

        if (!$resource instanceof Announcement) {
            return false;
        }

        /** @var AnnouncementCloseRepositoryInterface $closeRepository */
        $closeRepository = resolve(AnnouncementCloseRepositoryInterface::class);

        return !$closeRepository->getCloseAnnouncements($user, $resource->entityId())->exists();
    }
}
