<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Models\Follow;
use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class TotalFollowListener.
 * @ignore
 */
class TotalFollowListener
{
    public function __construct(protected FollowRepositoryInterface $followRepository)
    {
    }

    /**
     * @param  User   $owner
     * @param  string $view
     * @return int
     */
    public function handle(User $owner, string $view = MetaFoxConstant::VIEW_FOLLOWER): int
    {
        return match ($view) {
            MetaFoxConstant::VIEW_FOLLOWER  => $this->followRepository->totalFollowers($owner),
            MetaFoxConstant::VIEW_FOLLOWING => $this->followRepository->totalFollowing($owner),
            default                         => 0,
        };
    }
}
