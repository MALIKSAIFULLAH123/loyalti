<?php

namespace MetaFox\Follow\Support\Traits;

use MetaFox\Follow\Policies\FollowPolicy;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Trait IsFollowTrait.
 */
trait IsFollowTrait
{
    /**
     * @return FollowRepositoryInterface
     */
    private function repository(): FollowRepositoryInterface
    {
        return resolve(FollowRepositoryInterface::class);
    }

    /**
     * @param  User      $context
     * @param  User|null $user
     * @return bool
     */
    public function canFollow(User $context, ?User $user = null): bool
    {
        if (!app_active('metafox/follow')) {
            return false;
        }

        if (!$user instanceof User) {
            return false;
        }

        if ($this->repository()->isFollow($context->entityId(), $user->entityId())) {
            return false;
        }

        $followPolicy = resolve(FollowPolicy::class);

        return $followPolicy->addFollow($context, $user);
    }

    public function canUnfollow(User $context, ?User $user = null): bool
    {
        if (!app_active('metafox/follow')) {
            return false;
        }

        if (!$user instanceof User) {
            return false;
        }

        if ($context->entityId() == $user->entityId()) {
            return false;
        }

        return $this->repository()->isFollow($context->entityId(), $user->entityId());
    }
}
