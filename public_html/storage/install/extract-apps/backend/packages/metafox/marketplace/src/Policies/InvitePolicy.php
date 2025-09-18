<?php

namespace MetaFox\Marketplace\Policies;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class InvitePolicy
{
    public function visit(User $user, ?Listing $listing): bool
    {
        if (!policy_check(ListingPolicy::class, 'view', $user, $listing)) {
            return false;
        }

        $invite = resolve(InviteRepositoryInterface::class)->getInvite($user, $listing->entityId());

        if (null === $invite) {
            return false;
        }

        if (null !== $invite->visited_at) {
            return false;
        }

        return true;
    }
}
