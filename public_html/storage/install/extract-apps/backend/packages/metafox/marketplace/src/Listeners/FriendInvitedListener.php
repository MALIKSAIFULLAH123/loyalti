<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Policies\ListingPolicy;
use MetaFox\Marketplace\Repositories\InviteRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class FriendInvitedListener
{
    public function handle(?User $context, string $itemType, int $itemId): ?array
    {
        if ($itemType != Listing::ENTITY_TYPE) {
            return null;
        }

        $listing = resolve(ListingRepositoryInterface::class)->find($itemId);

        if (!policy_check(ListingPolicy::class, 'invite', $context, $listing)) {
            return [];
        }

        $invitedUserIds = resolve(InviteRepositoryInterface::class)->getInvitedUserIds($itemId);

        if (!is_array($invitedUserIds)) {
            $invitedUserIds = [];
        }

        $invitedUserIds[] = $listing->userId();

        return array_unique($invitedUserIds);
    }
}
