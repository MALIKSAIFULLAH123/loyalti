<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class GetSponsorFeedPriceListener
{
    public function handle(User $context, Content $resource, ?string $currencyId = null): ?float
    {
        if (!$resource instanceof ActivityFeedSource) {
            return null;
        }

        return resolve(FeedRepositoryInterface::class)->getSponsorPriceForPayment($context, $currencyId);
    }
}
