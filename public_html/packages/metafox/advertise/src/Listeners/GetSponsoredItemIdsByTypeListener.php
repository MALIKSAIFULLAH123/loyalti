<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class GetSponsoredItemIdsByTypeListener
{
    public function handle(User $context, string $itemType, ?int $limit = null, ?array $loadedItemIds = null, bool $shuffle = false): array
    {
        if (!$context->hasPermissionTo('advertise_sponsor.view')) {
            return [];
        }

        return resolve(SponsorRepositoryInterface::class)->getSponsoredItemIdsByType($context, $itemType, $limit, $loadedItemIds, $shuffle);
    }
}
