<?php
namespace MetaFox\Like\Listeners;

use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;

class ItemReactedAggregationListener
{
    public function handle(User $context, HasTotalLike $content, int $limit = 3): array
    {
        if (!policy_check(LikePolicy::class, 'viewAny', $context)) {
            return [];
        }

        return resolve(LikeRepositoryInterface::class)->getItemReactionAggregation($context, $content, $limit);
    }
}
