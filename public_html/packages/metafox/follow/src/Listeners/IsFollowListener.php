<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class IsFollowListener.
 * @ignore
 */
class IsFollowListener
{
    /**
     * @param  User $user
     * @param  User $owner
     * @return bool
     */
    public function handle(User $user, User $owner): bool
    {
        return resolve(FollowRepositoryInterface::class)
            ->isFollow($user->entityId(), $owner->entityId());
    }
}
