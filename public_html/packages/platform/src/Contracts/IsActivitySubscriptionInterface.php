<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Contracts;

interface IsActivitySubscriptionInterface
{
    /**
     * This method must return [$userId, $ownerId]
     * $userId: who subscribe
     * $ownerId: whom subscribe from
     * When $owner post content on profile, $userId can see $owner feed.
     *
     * @return int[]
     */
    public function toActivitySubscription(): array;
}
