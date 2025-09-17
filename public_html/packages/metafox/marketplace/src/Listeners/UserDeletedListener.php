<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Repositories\ListingHistoryRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }
        $listingService = resolve(ListingRepositoryInterface::class);
        $listingService->deleteUserData($user);
        $listingService->deleteOwnerData($user);

        resolve(ListingHistoryRepositoryInterface::class)->deleteHistoriesByUser(
            $user->entityId(),
            $user->entityType()
        );
    }
}
