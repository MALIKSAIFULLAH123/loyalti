<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Platform\Contracts\User;

/**
 * Class GetFollowerListener.
 *
 * @ignore
 */
class GetFollowerListener
{
    /**
     * @param User $context
     *
     * @return array
     * @deprecated
     */
    public function handle(User $context): array
    {
        $consider = ['owner_id' => $context->entityId()];

        return resolve('Activity.Subscription')->buildSubscriptions($consider)->pluck('user_id')->toArray();
    }
}
