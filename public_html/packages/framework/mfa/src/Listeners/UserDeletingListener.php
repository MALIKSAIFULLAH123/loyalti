<?php

namespace MetaFox\Mfa\Listeners;

use MetaFox\Mfa\Repositories\UserAuthTokenRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\User\Models\User;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class UserDeletingListener
{
    public function __construct(
        protected UserAuthTokenRepositoryInterface $userAuthTokenRepository,
        protected UserServiceRepositoryInterface $userServiceRepository
    ) {
    }

    /**
     * @param mixed $user
     *
     * @return void
     */
    public function handle($user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->handleUserAuthTokens($user);
        $this->handleUserServices($user);
    }

    /**
     * @param User $user
     *
     * @return void
     */
    protected function handleUserAuthTokens(User $user)
    {
        $this->userAuthTokenRepository->deleteTokensByUserId($user->entityId());
    }

    /**
     * @param User $user
     *
     * @return void
     */
    protected function handleUserServices(User $user)
    {
        $this->userServiceRepository->deleteServicesByUserId($user->entityId());
    }
}
