<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Support\Facades\ActivitySubscription;
use MetaFox\Activity\Support\Support;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class UserRegistrationListener
{
    public function __construct(protected UserRepositoryInterface $userRepository) {}

    public function handle(?User $user, array $attributes): void
    {
        if (!$user) {
            return;
        }

        $superAdmins = $this->userRepository->getAllSuperAdmin();

        if ($superAdmins->isEmpty()) {
            return;
        }

        foreach ($superAdmins as $superAdmin) {
            if (!$superAdmin instanceof User) {
                continue;
            }

            ActivitySubscription::addSubscription(
                $user->entityId(),
                $superAdmin->entityId(),
                true,
                Support::ACTIVITY_SUBSCRIPTION_VIEW_SUPER_ADMIN_FEED
            );
        }

    }
}
